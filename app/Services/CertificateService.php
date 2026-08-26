<?php

namespace App\Services;

use App\Enums\CertificateStatus;
use App\Enums\CertificateType;
use App\Models\Certificate;
use App\Models\CertificateStatusHistory;
use App\Models\CertificateTemplate;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use App\Services\Notifications\NotificationRecipient;
use App\Services\Notifications\NotificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CertificateService
{
    public function __construct(
        private ReferenceGenerator $references,
        private QrCodeService $qr,
        private NotificationService $notifications,
    ) {}

    /** Adakah peserta ini layak menerima sijil? */
    public function isEligible(Registration $registration): bool
    {
        $event = $registration->event;

        if (! $event->certificate_enabled) {
            return false;
        }

        if (! $registration->hasAttended()) {
            return false;
        }

        if ($event->feedback_required_for_certificate && ! $registration->feedback) {
            return false;
        }

        return true;
    }

    public function ineligibleReason(Registration $registration): ?string
    {
        $event = $registration->event;

        if (! $event->certificate_enabled) {
            return 'Program ini tidak mengeluarkan sijil.';
        }

        if (! $registration->hasAttended()) {
            return 'Sijil hanya dikeluarkan kepada peserta yang hadir dan telah mengimbas QR kehadiran.';
        }

        if ($event->feedback_required_for_certificate && ! $registration->feedback) {
            return 'Sila lengkapkan maklum balas program untuk membuka sijil anda.';
        }

        return null;
    }

    /** Mengeluarkan sijil penyertaan kepada seorang peserta. */
    public function issueForRegistration(Registration $registration, ?CertificateType $type = null): ?Certificate
    {
        if (! $this->isEligible($registration)) {
            return null;
        }

        $existing = $registration->certificate()->valid()->first();

        if ($existing) {
            return $existing;
        }

        $event = $registration->event;
        $type ??= CertificateType::Penyertaan;

        $certificate = $this->create([
            'type' => $type,
            'event' => $event,
            'registration' => $registration,
            'user' => $registration->user,
            'recipient_name' => $registration->name,
            'recipient_email' => $registration->email,
            'recipient_phone' => $registration->phone,
        ]);

        $this->notifications->queue('sijil_tersedia', NotificationRecipient::fromRegistration($registration), [
            'participant_name' => $registration->name,
            'event_name' => $event->title,
            'event_date' => $event->dateLabel(),
            'certificate_link' => $certificate->verificationUrl(),
        ], $certificate, ['url' => route('peserta.sijil'), 'action_label' => 'Lihat Sijil']);

        return $certificate;
    }

    /** Mengeluarkan sijil kepada semua peserta yang layak untuk satu program. */
    public function issueForEvent(Event $event, ?CertificateType $type = null): int
    {
        $issued = 0;

        $event->registrations()
            ->attended()
            ->with(['attendance', 'feedback', 'user', 'event'])
            ->chunkById(100, function ($registrations) use (&$issued, $type) {
                foreach ($registrations as $registration) {
                    if ($this->issueForRegistration($registration, $type)) {
                        $issued++;
                    }
                }
            });

        ActivityLogger::log('certificate.bulk_issued', $event, "{$issued} sijil dikeluarkan untuk {$event->title}.");

        return $issued;
    }

    /** Sijil penghargaan untuk Penggerak Jelajah. */
    public function issueMobilizerCertificate(Event $event, User $mobilizer): Certificate
    {
        $existing = Certificate::valid()
            ->where('event_id', $event->id)
            ->where('user_id', $mobilizer->id)
            ->where('type', CertificateType::PenghargaanPenggerak->value)
            ->first();

        if ($existing) {
            return $existing;
        }

        return $this->create([
            'type' => CertificateType::PenghargaanPenggerak,
            'event' => $event,
            'user' => $mobilizer,
            'recipient_name' => $mobilizer->name,
            'recipient_email' => $mobilizer->email,
            'recipient_phone' => $mobilizer->phone,
        ]);
    }

    /** Sijil penghargaan untuk masjid / organisasi rakan. */
    public function issuePartnerCertificate(Event $event, string $organizationName): Certificate
    {
        // Program boleh ditutup lebih daripada sekali selepas ditangguh
        // dan diterbitkan semula — jangan cetak sijil kedua untuk rakan
        // kongsi yang sama.
        $existing = Certificate::valid()
            ->where('event_id', $event->id)
            ->where('organization_name', $organizationName)
            ->where('type', CertificateType::PenghargaanRakan->value)
            ->first();

        if ($existing) {
            return $existing;
        }

        return $this->create([
            'type' => CertificateType::PenghargaanRakan,
            'event' => $event,
            'recipient_name' => $organizationName,
            'organization_name' => $organizationName,
        ]);
    }

    /**
     * Menjana semula sijil selepas nama dibetulkan.
     * Sijil lama ditandakan "digantikan" — bukan dipadam.
     */
    public function regenerate(Certificate $certificate, string $correctedName, ?User $actor = null): Certificate
    {
        return DB::transaction(function () use ($certificate, $correctedName, $actor) {
            $replacement = $this->create([
                'type' => $certificate->type,
                'event' => $certificate->event,
                'registration' => $certificate->registration,
                'user' => $certificate->user,
                'recipient_name' => $correctedName,
                'recipient_email' => $certificate->recipient_email,
                'recipient_phone' => $certificate->recipient_phone,
                'organization_name' => $certificate->organization_name,
            ]);

            $this->transition(
                $certificate,
                CertificateStatus::Digantikan,
                "Digantikan oleh {$replacement->certificate_number} (pembetulan nama).",
                $actor,
            );

            $certificate->update(['superseded_by_id' => $replacement->id]);

            return $replacement;
        });
    }

    public function revoke(Certificate $certificate, string $reason, ?User $actor = null): Certificate
    {
        $this->transition($certificate, CertificateStatus::Dibatalkan, $reason, $actor);

        $certificate->update(['revoke_reason' => $reason]);

        return $certificate->fresh();
    }

    /** Menjana PDF dan memulangkan laluan simpanan. */
    public function renderPdf(Certificate $certificate): string
    {
        $template = $certificate->template ?? CertificateTemplate::resolveFor($certificate->type);

        $pdf = Pdf::loadView('certificates.template', [
            'certificate' => $certificate,
            'template' => $template,
            'qrDataUri' => $this->qr->pngDataUri($certificate->verificationUrl(), 300),
        ])->setPaper('a4', $template?->orientation ?? 'landscape');

        $path = "sijil/{$certificate->certificate_number}.pdf";
        Storage::disk('public')->put($path, $pdf->output());

        $certificate->update(['pdf_path' => $path]);

        return $path;
    }

    public function pdfPath(Certificate $certificate): string
    {
        if ($certificate->pdf_path && Storage::disk('public')->exists($certificate->pdf_path)) {
            return $certificate->pdf_path;
        }

        return $this->renderPdf($certificate);
    }

    // ── Dalaman ──────────────────────────────────────────────────────

    private function create(array $attributes): Certificate
    {
        /** @var Event|null $event */
        $event = $attributes['event'] ?? null;
        $type = $attributes['type'];
        $template = CertificateTemplate::resolveFor($type);

        $certificate = Certificate::create([
            'certificate_number' => $this->references->certificate($event?->state?->code),
            'type' => $type,
            'event_id' => $event?->id,
            'registration_id' => $attributes['registration']?->id ?? null,
            'user_id' => $attributes['user']?->id ?? null,
            'certificate_template_id' => $template?->id,
            'recipient_name' => $attributes['recipient_name'],
            'recipient_email' => $attributes['recipient_email'] ?? null,
            'recipient_phone' => $attributes['recipient_phone'] ?? null,
            'organization_name' => $attributes['organization_name'] ?? $event?->organizer_name,
            'event_title' => $event?->title,
            'speaker_name' => $event?->speaker?->name,
            'venue_name' => $event?->venue?->name ?? $event?->locationLabel(),
            'event_date' => $event?->starts_at?->toDateString(),
            'learning_hours' => $event?->learning_hours,
            'status' => CertificateStatus::Dikeluarkan,
            'issued_at' => now(),
        ]);

        CertificateStatusHistory::create([
            'certificate_id' => $certificate->id,
            'from_status' => null,
            'to_status' => CertificateStatus::Dikeluarkan->value,
            'reason' => 'Sijil dikeluarkan.',
        ]);

        ActivityLogger::log(
            'certificate.issued',
            $certificate,
            "{$type->label()} {$certificate->certificate_number} untuk {$certificate->recipient_name}.",
        );

        return $certificate;
    }

    private function transition(Certificate $certificate, CertificateStatus $to, string $reason, ?User $actor = null): void
    {
        $from = $certificate->status;

        $certificate->update(['status' => $to]);

        CertificateStatusHistory::create([
            'certificate_id' => $certificate->id,
            'from_status' => $from->value,
            'to_status' => $to->value,
            'user_id' => $actor?->id,
            'reason' => $reason,
        ]);

        ActivityLogger::log(
            'certificate.status_changed',
            $certificate,
            "{$certificate->certificate_number}: {$from->label()} → {$to->label()}",
        );
    }
}
