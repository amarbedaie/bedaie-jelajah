<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\ApplicationStatusHistory;
use App\Models\LoginLink;
use App\Models\MobilizerProfile;
use App\Models\User;
use App\Services\Notifications\NotificationRecipient;
use App\Services\Notifications\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApplicationService
{
    public function __construct(
        private ReferenceGenerator $references,
        private NotificationService $notifications,
        private EventSpaceGenerator $eventSpace,
    ) {}

    /**
     * Menghantar permohonan baharu. Akaun Penggerak dicipta atau
     * dipautkan secara automatik supaya pemohon boleh menjejak status.
     */
    public function submit(array $data, ?User $user = null): Application
    {
        return DB::transaction(function () use ($data, $user) {
            if ($user) {
                // Pemohon yang sudah log masuk telah membuktikan pemilikan
                // akaunnya, jadi naikkan pangkat terus — tanpa ini mereka
                // tersekat 403 pada halaman permohonan mereka sendiri.
                if ($user->role === UserRole::Peserta) {
                    $user->update(['role' => UserRole::Penggerak]);
                }

                $this->ensureProfile($user, (object) $data);
            } else {
                $user = $this->resolveMobilizer($data);
            }

            $application = Application::create([
                ...$data,
                'reference_no' => $this->references->application(),
                'user_id' => $user?->id,
                'status' => ApplicationStatus::Diterima,
                'status_changed_at' => now(),
                'submitted_at' => now(),
                'privacy_consent_at' => now(),
            ]);

            $this->recordHistory(
                $application,
                null,
                ApplicationStatus::Diterima,
                ApplicationStatus::Diterima->description(),
                'Permohonan dihantar melalui borang awam.',
                $user,
            );

            ActivityLogger::log('application.submitted', $application, "Permohonan {$application->reference_no} diterima.");

            $this->notifyApplicant($application, 'permohonan_diterima');

            // Akaun Penggerak dicipta tanpa kata laluan yang diketahui pemohon,
            // jadi kita hantar pautan log masuk terus supaya mereka benar-benar
            // dapat menjejak status permohonan seperti yang dijanjikan.
            if ($user) {
                $this->sendLoginLink($user);
            }

            return $application;
        });
    }

    /**
     * Menukar status permohonan. Apabila status menjadi "Program Disahkan",
     * EventSpace dijana secara automatik.
     */
    public function changeStatus(
        Application $application,
        ApplicationStatus $status,
        ?string $publicNote = null,
        ?string $internalNote = null,
        ?User $actor = null,
        array $eventOverrides = [],
    ): Application {
        return DB::transaction(function () use ($application, $status, $publicNote, $internalNote, $actor, $eventOverrides) {
            $from = $application->status;

            if ($from === $status && $status !== ApplicationStatus::ProgramDisahkan) {
                return $application;
            }

            $application->update([
                'status' => $status,
                'status_changed_at' => now(),
            ]);

            $this->recordHistory($application, $from, $status, $publicNote, $internalNote, $actor);

            ActivityLogger::log(
                'application.status_changed',
                $application,
                "Status {$application->reference_no}: {$from->label()} → {$status->label()}",
                ['from' => $from->value, 'to' => $status->value],
            );

            // ── Automasi: cipta EventSpace apabila program disahkan ──
            if ($status === ApplicationStatus::ProgramDisahkan && ! $application->isConverted()) {
                $event = $this->eventSpace->createFromApplication($application, $eventOverrides);
                $application->refresh();

                $this->notifyApplicant($application, 'program_disahkan', [
                    'event_name' => $event->title,
                    'event_date' => $event->dateLabel(),
                    'event_time' => $event->timeLabel(),
                    'venue' => $event->venue?->name ?? $event->locationLabel(),
                    'registration_link' => $event->shortUrl(),
                ], ['url' => route('penggerak.program.show', $event)]);

                return $application;
            }

            $this->notifyApplicant($application, 'status_permohonan_berubah', [
                'status' => $status->label(),
                'status_note' => $publicNote ?: $status->description(),
            ]);

            return $application;
        });
    }

    public function assignAdmin(Application $application, ?User $admin): void
    {
        $application->update(['assigned_admin_id' => $admin?->id]);

        ActivityLogger::log(
            'application.assigned',
            $application,
            $admin ? "Ditugaskan kepada {$admin->name}." : 'Tugasan dibuang.',
        );
    }

    /** Memautkan permohonan kepada akaun Penggerak sedia ada. */
    public function linkMobilizer(Application $application, User $user): void
    {
        $application->update(['user_id' => $user->id]);

        if ($user->role === UserRole::Peserta) {
            $user->update(['role' => UserRole::Penggerak]);
        }

        $this->ensureProfile($user, $application);

        ActivityLogger::log('application.linked', $application, "Dipautkan kepada Penggerak {$user->name}.");
    }

    private function recordHistory(
        Application $application,
        ?ApplicationStatus $from,
        ApplicationStatus $to,
        ?string $publicNote,
        ?string $internalNote,
        ?User $actor,
    ): void {
        ApplicationStatusHistory::create([
            'application_id' => $application->id,
            'from_status' => $from?->value,
            'to_status' => $to->value,
            'user_id' => $actor?->id,
            'public_note' => $publicNote,
            'internal_note' => $internalNote,
        ]);
    }

    /**
     * Mencari atau mencipta akaun Penggerak berdasarkan emel/telefon pemohon.
     * Akaun dicipta tanpa kata laluan tetap — pemohon menetapkannya melalui
     * pautan "set kata laluan" yang dihantar bersama pengesahan.
     */
    private function resolveMobilizer(array $data): ?User
    {
        $email = $data['applicant_email'] ?? null;
        $phone = $data['applicant_phone'] ?? null;

        $user = null;

        if ($email) {
            $user = User::withTrashed()->where('email', $email)->first();
        }

        if (! $user && $phone) {
            $user = User::withTrashed()->where('phone', $phone)->first();
        }

        if ($user) {
            // Borang ini tidak disahkan — sesiapa boleh menaip e-mel atau
            // nombor orang lain. Jadi ia tidak boleh menghidupkan semula
            // akaun yang sengaja dipadam admin, dan tidak boleh menaikkan
            // pangkat sesiapa. Kenaikan pangkat berlaku apabila pemohon
            // membuktikan pemilikan dengan menggunakan pautan log masuk.
            if ($user->trashed()) {
                return null;
            }
        } else {
            $user = User::create([
                'name' => $data['applicant_name'],
                'email' => $email ?: $this->placeholderEmail($phone),
                'phone' => $phone,
                'password' => Str::random(40),
                'role' => UserRole::Penggerak,
                'state_id' => $data['state_id'] ?? null,
                'district_id' => $data['district_id'] ?? null,
            ]);
        }

        $this->ensureProfile($user, (object) $data);

        return $user;
    }

    private function ensureProfile(User $user, object $source): void
    {
        MobilizerProfile::updateOrCreate(
            ['user_id' => $user->id],
            array_filter([
                'background' => $source->background ?? null,
                'background_other' => $source->background_other ?? null,
                'whatsapp' => $source->applicant_phone ?? $user->phone,
                'state_id' => $source->state_id ?? $user->state_id,
                'district_id' => $source->district_id ?? $user->district_id,
            ], fn ($v) => $v !== null && $v !== ''),
        );
    }

    /** Emel pengganti apabila pemohon hanya memberi nombor WhatsApp. */
    /** Pautan log masuk sekali-guna melalui WhatsApp. */
    private function sendLoginLink(User $user): void
    {
        $link = LoginLink::issueFor($user, 'whatsapp', request()?->ip());

        $this->notifications->queue(
            'pautan_log_masuk',
            NotificationRecipient::fromUser($user),
            [
                'participant_name' => $user->name,
                'registration_link' => $link->url(),
                'status' => '30 minit',
            ],
            $user,
            ['url' => $link->url(), 'action_label' => 'Log Masuk'],
        );
    }

    /**
     * Menaikkan pemohon menjadi Penggerak setelah pemilikan akaun terbukti.
     *
     * Dipanggil apabila pautan log masuk digunakan — hanya pemilik nombor
     * WhatsApp atau e-mel itu boleh sampai ke sini.
     */
    public static function promoteIfApplicant(User $user): void
    {
        if ($user->role !== UserRole::Peserta) {
            return;
        }

        if (! Application::where('user_id', $user->id)->exists()) {
            return;
        }

        $user->update(['role' => UserRole::Penggerak]);

        ActivityLogger::log('application.mobilizer_promoted', $user,
            "{$user->name} dinaikkan menjadi Penggerak selepas mengesahkan pemilikan akaun.");
    }

    private function placeholderEmail(?string $phone): string
    {
        $digits = preg_replace('/\D/', '', (string) $phone) ?: Str::random(10);

        return "penggerak.{$digits}".User::PLACEHOLDER_EMAIL_DOMAIN;
    }

    private function notifyApplicant(Application $application, string $templateKey, array $extra = [], array $context = []): void
    {
        $recipient = new NotificationRecipient(
            name: $application->applicant_name,
            email: $this->realEmail($application->applicant_email),
            phone: $application->applicant_phone,
            user: $application->user,
        );

        $this->notifications->queue($templateKey, $recipient, [
            'mobilizer_name' => $application->applicant_name,
            'participant_name' => $application->applicant_name,
            'reference_no' => $application->reference_no,
            'venue' => $application->venue_name,
            'status' => $application->status->label(),
            'status_note' => $application->status->description(),
            ...$extra,
        ], $application, $context ?: ['url' => route('penggerak.permohonan.show', $application->public_id)]);
    }

    private function realEmail(?string $email): ?string
    {
        return $email && ! str_ends_with($email, User::PLACEHOLDER_EMAIL_DOMAIN) ? $email : null;
    }
}
