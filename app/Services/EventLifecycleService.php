<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\User;
use App\Services\Notifications\NotificationRecipient;
use App\Services\Notifications\NotificationService;
use Illuminate\Support\Facades\DB;

/**
 * Menguruskan peralihan kitaran hayat program — termasuk semua
 * automasi "selepas program selesai".
 */
class EventLifecycleService
{
    public function __construct(
        private RegistrationService $registrations,
        private CertificateService $certificates,
        private NotificationService $notifications,
    ) {}

    public function publish(Event $event): Event
    {
        $event->update([
            'status' => EventStatus::Diterbitkan,
            'published_at' => $event->published_at ?? now(),
        ]);

        ImpactStatsService::flush();
        ActivityLogger::log('event.published', $event, "{$event->title} diterbitkan.");

        return $event;
    }

    public function markOngoing(Event $event): Event
    {
        $event->update(['status' => EventStatus::Berlangsung]);
        ImpactStatsService::flush();

        return $event;
    }

    /**
     * Menutup program dan menjalankan automasi pasca-program:
     * tutup pendaftaran → arkib → maklum balas → sijil → laporan → statistik.
     */
    public function complete(Event $event, ?User $actor = null, bool $issueCertificates = true): array
    {
        return DB::transaction(function () use ($event, $actor, $issueCertificates) {
            $event->update([
                'status' => EventStatus::Selesai,
                'completed_at' => now(),
                'registration_closes_at' => min($event->registration_closes_at ?? now(), now()),
            ]);

            $this->registrations->refreshCounts($event);

            // Permohonan asal ditandakan selesai
            if ($event->application && $event->application->status !== ApplicationStatus::Selesai) {
                $event->application->update([
                    'status' => ApplicationStatus::Selesai,
                    'status_changed_at' => now(),
                ]);
            }

            $feedbackRequests = $this->requestFeedback($event);
            $certificatesIssued = $issueCertificates ? $this->certificates->issueForEvent($event) : 0;
            $mobilizerCertificates = $this->issueMobilizerCertificates($event);

            ImpactStatsService::flush();

            ActivityLogger::log(
                'event.completed',
                $event,
                "{$event->title} ditandakan selesai.",
                [
                    'certificates' => $certificatesIssued,
                    'feedback_requests' => $feedbackRequests,
                ],
            );

            return [
                'feedback_requests' => $feedbackRequests,
                'certificates' => $certificatesIssued,
                'mobilizer_certificates' => $mobilizerCertificates,
            ];
        });
    }

    public function postpone(Event $event, ?string $reason = null): Event
    {
        $event->update(['status' => EventStatus::Ditangguhkan]);
        $this->notifyChange($event, 'program_ditangguhkan', $reason);
        ImpactStatsService::flush();

        return $event;
    }

    public function cancel(Event $event, ?string $reason = null): Event
    {
        $event->update(['status' => EventStatus::Dibatalkan]);
        $this->notifyChange($event, 'program_dibatalkan', $reason);
        ImpactStatsService::flush();

        return $event;
    }

    /** Menghantar permintaan maklum balas kepada peserta yang hadir. */
    public function requestFeedback(Event $event): int
    {
        $sent = 0;

        $event->registrations()
            ->attended()
            ->whereDoesntHave('feedback')
            ->with('user')
            ->chunkById(100, function ($registrations) use ($event, &$sent) {
                foreach ($registrations as $registration) {
                    $this->notifications->queue(
                        'maklum_balas_program',
                        NotificationRecipient::fromRegistration($registration),
                        [
                            'participant_name' => $registration->name,
                            'event_name' => $event->title,
                            'registration_link' => route('maklum-balas.show', $registration->public_token),
                        ],
                        $registration,
                        ['url' => route('maklum-balas.show', $registration->public_token), 'action_label' => 'Beri Maklum Balas'],
                    );
                    $sent++;
                }
            });

        return $sent;
    }

    private function issueMobilizerCertificates(Event $event): int
    {
        $count = 0;

        foreach ($event->mobilizers as $mobilizer) {
            $this->certificates->issueMobilizerCertificate($event, $mobilizer);
            $count++;
        }

        if ($event->organizer_name) {
            $this->certificates->issuePartnerCertificate($event, $event->organizer_name);
            $count++;
        }

        return $count;
    }

    private function notifyChange(Event $event, string $templateKey, ?string $reason): void
    {
        $event->registrations()->active()->with('user')->chunkById(100, function ($registrations) use ($event, $templateKey, $reason) {
            foreach ($registrations as $registration) {
                $this->notifications->queue($templateKey, NotificationRecipient::fromRegistration($registration), [
                    'participant_name' => $registration->name,
                    'event_name' => $event->title,
                    'event_date' => $event->dateLabel(),
                    'venue' => $event->venue?->name ?? $event->locationLabel(),
                    'status_note' => $reason ?: 'Sila hubungi penganjur untuk maklumat lanjut.',
                ], $registration);
            }
        });
    }
}
