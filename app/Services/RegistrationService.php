<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Enums\PricingMode;
use App\Enums\RegistrationStatus;
use App\Models\Event;
use App\Models\QrToken;
use App\Models\Registration;
use App\Models\User;
use App\Services\Notifications\NotificationRecipient;
use App\Services\Notifications\NotificationService;
use App\Services\Payments\PaymentManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegistrationService
{
    public function __construct(
        private ReferenceGenerator $references,
        private NotificationService $notifications,
        private PaymentManager $payments,
    ) {}

    /**
     * Mendaftarkan peserta. Kapasiti, senarai menunggu, kod jemputan dan
     * pembayaran diuruskan di sini supaya semua saluran (awam, admin
     * walk-in) berkongsi logik yang sama.
     */
    public function register(Event $event, array $data, ?User $user = null, string $source = 'online'): Registration
    {
        return DB::transaction(function () use ($event, $data, $user, $source) {
            // Kunci baris program supaya kiraan kapasiti tepat walaupun
            // beberapa peserta mendaftar serentak.
            $event = Event::whereKey($event->id)->lockForUpdate()->firstOrFail();

            $this->guardRegistrationOpen($event, $source);
            $this->guardInviteCode($event, $data, $source);
            $this->guardDuplicate($event, $data);

            $guests = collect($data['guests'] ?? [])
                ->filter(fn ($g) => filled($g['name'] ?? null))
                ->take($event->allow_guest_registration ? $event->max_guests_per_registration : 0)
                ->values();

            $seatsNeeded = 1 + $guests->count();
            $status = $this->resolveStatus($event, $seatsNeeded, $source);

            $registration = Registration::create([
                'reference_no' => $this->references->registration(),
                'event_id' => $event->id,
                'user_id' => $user?->id,
                'ticket_id' => $data['ticket_id'] ?? null,
                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'gender' => $data['gender'] ?? null,
                'state_id' => $data['state_id'] ?? null,
                'district_id' => $data['district_id'] ?? null,
                'guests_count' => $guests->count(),
                'status' => $status,
                'source' => $source,
                'invite_code' => $data['invite_code'] ?? null,
                'notes' => $data['notes'] ?? null,
                'privacy_consent_at' => now(),
                'registered_at' => now(),
                'confirmed_at' => $status === RegistrationStatus::Disahkan ? now() : null,
                'ip_address' => request()->ip(),
            ]);

            foreach ($guests as $guest) {
                $registration->guests()->create([
                    'name' => $guest['name'],
                    'gender' => $guest['gender'] ?? null,
                    'age_group' => $guest['age_group'] ?? null,
                ]);
            }

            // QR kehadiran unik — token, bukan ID database.
            QrToken::create([
                'tokenable_type' => Registration::class,
                'tokenable_id' => $registration->id,
                'purpose' => 'checkin',
            ]);

            $this->createPaymentIfNeeded($event, $registration);
            $this->refreshCounts($event);

            ActivityLogger::log(
                'registration.created',
                $registration,
                "{$registration->name} mendaftar untuk {$event->title} ({$status->label()}).",
                ['source' => $source, 'seats' => $seatsNeeded],
            );

            $this->notifyParticipant($registration->fresh(['event.venue', 'event.speaker']));

            return $registration->fresh(['guests', 'payment', 'event']);
        });
    }

    /** Mengesahkan pendaftaran yang menunggu (contoh: selepas pembayaran diterima). */
    public function confirm(Registration $registration): Registration
    {
        if ($registration->status === RegistrationStatus::Disahkan) {
            return $registration;
        }

        $registration->update([
            'status' => RegistrationStatus::Disahkan,
            'confirmed_at' => now(),
        ]);

        $this->refreshCounts($registration->event);

        ActivityLogger::log('registration.confirmed', $registration, "Pendaftaran {$registration->reference_no} disahkan.");

        $this->notifyParticipant($registration->fresh(['event.venue', 'event.speaker']));

        return $registration;
    }

    /** Membatalkan pendaftaran dan menaikkan peserta pertama dalam senarai menunggu. */
    public function cancel(Registration $registration, ?string $reason = null): Registration
    {
        DB::transaction(function () use ($registration, $reason) {
            $registration->update([
                'status' => RegistrationStatus::Dibatalkan,
                'cancelled_at' => now(),
                'cancel_reason' => $reason,
            ]);

            $registration->activeQrToken()?->revoke();
            $this->promoteFromWaitlist($registration->event);
            $this->refreshCounts($registration->event);

            ActivityLogger::log('registration.cancelled', $registration, "Pendaftaran {$registration->reference_no} dibatalkan.");
        });

        return $registration->fresh();
    }

    /** Menaikkan peserta daripada senarai menunggu apabila tempat kosong. */
    public function promoteFromWaitlist(Event $event): int
    {
        if ($event->capacity <= 0) {
            return 0;
        }

        $promoted = 0;

        $waiting = $event->registrations()
            ->waitlisted()
            ->orderBy('registered_at')
            ->get();

        foreach ($waiting as $registration) {
            $left = $event->fresh()->seatsLeft();

            if ($left === null || $left < $registration->seats()) {
                break;
            }

            $registration->update([
                'status' => RegistrationStatus::Disahkan,
                'confirmed_at' => now(),
            ]);

            $promoted++;

            ActivityLogger::log(
                'registration.promoted',
                $registration,
                "{$registration->name} dinaikkan daripada senarai menunggu.",
            );

            $this->notifications->queue('naik_dari_senarai_menunggu', NotificationRecipient::fromRegistration($registration), [
                'participant_name' => $registration->name,
                'event_name' => $event->title,
                'event_date' => $event->dateLabel(),
                'event_time' => $event->timeLabel(),
                'venue' => $event->venue?->name ?? $event->locationLabel(),
                'qr_link' => $registration->ticketUrl(),
                'registration_link' => $event->shortUrl(),
            ], $registration, ['url' => $registration->ticketUrl()]);
        }

        return $promoted;
    }

    /** Menyegarkan kiraan cache pada program. */
    public function refreshCounts(Event $event): void
    {
        $event->forceFill([
            'registered_count' => $event->registrations()->active()->count(),
            'waitlist_count' => $event->registrations()->waitlisted()->count(),
            'attended_count' => $event->attendanceRecords()->count(),
        ])->save();
    }

    // ── Pengesahan ───────────────────────────────────────────────────

    private function guardRegistrationOpen(Event $event, string $source): void
    {
        // Admin boleh mendaftarkan walk-in walaupun pendaftaran awam ditutup.
        if ($source === 'walk_in' || $source === 'admin') {
            return;
        }

        if (! $event->registrationOpen()) {
            throw ValidationException::withMessages([
                'event' => $event->registrationClosedReason() ?? 'Pendaftaran tidak dibuka.',
            ]);
        }
    }

    private function guardInviteCode(Event $event, array $data, string $source): void
    {
        if ($source !== 'online' || ! $event->pricing_mode->requiresInviteCode()) {
            return;
        }

        $given = trim((string) ($data['invite_code'] ?? ''));

        if (! $event->invite_code || ! hash_equals(strtoupper($event->invite_code), strtoupper($given))) {
            throw ValidationException::withMessages([
                'invite_code' => 'Kod jemputan tidak sah. Sila semak semula kod yang diberikan penganjur.',
            ]);
        }
    }

    private function guardDuplicate(Event $event, array $data): void
    {
        $query = $event->registrations()->where(function ($q) use ($data) {
            $q->where('phone', $data['phone']);

            if (! empty($data['email'])) {
                $q->orWhere('email', $data['email']);
            }
        });

        $existing = $query->first();

        if (! $existing) {
            return;
        }

        if ($existing->status === RegistrationStatus::Dibatalkan) {
            // Padam kekal supaya unique index (event_id, phone) tidak berlanggar.
            $existing->forceDelete();

            return;
        }

        throw ValidationException::withMessages([
            'phone' => "Nombor atau emel ini telah didaftarkan untuk program ini (rujukan {$existing->reference_no}).",
        ]);
    }

    private function resolveStatus(Event $event, int $seatsNeeded, string $source): RegistrationStatus
    {
        if ($source === 'walk_in') {
            return RegistrationStatus::Disahkan;
        }

        $left = $event->seatsLeft();

        if ($left !== null && $left < $seatsNeeded) {
            if (! $event->allow_waiting_list) {
                throw ValidationException::withMessages([
                    'event' => 'Maaf, tempat untuk program ini telah penuh.',
                ]);
            }

            return RegistrationStatus::SenaraiMenunggu;
        }

        if ($event->requires_approval || $event->pricing_mode->requiresPayment()) {
            return RegistrationStatus::MenungguPengesahan;
        }

        return RegistrationStatus::Disahkan;
    }

    private function createPaymentIfNeeded(Event $event, Registration $registration): void
    {
        if (! $event->pricing_mode->requiresPayment()) {
            if ($event->pricing_mode === PricingMode::Ditaja) {
                $this->payments->createFor($registration, 0)->update(['status' => PaymentStatus::Ditaja]);
            }

            return;
        }

        $unitPrice = $registration->ticket?->price ?? $event->price;
        $amount = (float) $unitPrice * $registration->seats();

        $this->payments->createFor($registration, $amount);
    }

    private function notifyParticipant(Registration $registration): void
    {
        $event = $registration->event;

        $templateKey = match ($registration->status) {
            RegistrationStatus::SenaraiMenunggu => 'pendaftaran_senarai_menunggu',
            RegistrationStatus::MenungguPengesahan => 'pendaftaran_menunggu_pengesahan',
            default => 'pendaftaran_berjaya',
        };

        $this->notifications->queue($templateKey, NotificationRecipient::fromRegistration($registration), [
            'participant_name' => $registration->name,
            'event_name' => $event->title,
            'event_date' => $event->dateLabel(),
            'event_time' => $event->timeLabel(),
            'venue' => $event->venue?->name ?? $event->locationLabel(),
            'speaker' => $event->speaker?->name ?? 'Pasukan BeDaie',
            'reference_no' => $registration->reference_no,
            'registration_link' => $event->shortUrl(),
            'qr_link' => $registration->ticketUrl(),
        ], $registration, ['url' => $registration->ticketUrl(), 'action_label' => 'Lihat Tiket & QR']);
    }
}
