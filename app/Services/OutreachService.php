<?php

namespace App\Services;

use App\Enums\ApplicantBackground;
use App\Enums\AttendeeEstimate;
use App\Enums\OutreachActivityType;
use App\Enums\OutreachSource;
use App\Enums\OutreachStage;
use App\Enums\TargetAudience;
use App\Enums\VenueConsent;
use App\Models\Application;
use App\Models\OutreachActivity;
use App\Models\OutreachTarget;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Aliran keluar: pasukan BeDaie memilih lokasi sasaran dan mengejarnya
 * sehingga pihak lokasi bersetuju. Selepas itu sasaran ditukar kepada
 * permohonan rasmi supaya ia mengalir melalui saluran sedia ada —
 * satu jalan sahaja menuju program, tiada laluan selari.
 */
class OutreachService
{
    public function __construct(
        private ReferenceGenerator $references,
        private ApplicationService $applications,
    ) {}

    public function create(array $data, ?User $actor = null): OutreachTarget
    {
        return DB::transaction(function () use ($data, $actor) {
            $target = OutreachTarget::create([
                ...$data,
                'reference_no' => $this->references->outreachTarget(),
                'stage' => $data['stage'] ?? OutreachStage::Sasaran,
                'stage_changed_at' => now(),
                'created_by' => $actor?->id,
            ]);

            $this->log($target, OutreachActivityType::Nota,
                'Sasaran ditambah ke senarai.', null, $actor);

            ActivityLogger::log('outreach.created', $target,
                "Sasaran baharu: {$target->name} ({$target->locationLabel()}).");

            return $target;
        });
    }

    /** Menukar peringkat dan merekod jejaknya pada garis masa sasaran. */
    public function moveStage(
        OutreachTarget $target,
        OutreachStage $stage,
        ?string $note = null,
        ?User $actor = null,
    ): OutreachTarget {
        return DB::transaction(function () use ($target, $stage, $note, $actor) {
            $from = $target->stage;

            if ($from === $stage) {
                return $target;
            }

            $target->update([
                'stage' => $stage,
                'stage_changed_at' => now(),
                'won_at' => $stage->isWon() ? now() : $target->won_at,
                // Peringkat terbuka semula membatalkan sebab penutupan lama.
                'closed_reason' => $stage->isOpen() ? null : $target->closed_reason,
            ]);

            OutreachActivity::create([
                'outreach_target_id' => $target->id,
                'user_id' => $actor?->id,
                'type' => OutreachActivityType::Peringkat,
                'body' => $note,
                'from_stage' => $from->value,
                'to_stage' => $stage->value,
                'occurred_at' => now(),
            ]);

            ActivityLogger::log('outreach.stage_changed', $target,
                "{$target->name}: {$from->label()} → {$stage->label()}",
                ['from' => $from->value, 'to' => $stage->value]);

            return $target->fresh();
        });
    }

    public function close(OutreachTarget $target, string $reason, ?User $actor = null): OutreachTarget
    {
        $target->update(['closed_reason' => $reason]);

        return $this->moveStage($target, OutreachStage::TidakBerminat, $reason, $actor);
    }

    public function log(
        OutreachTarget $target,
        OutreachActivityType $type,
        ?string $body = null,
        ?string $outcome = null,
        ?User $actor = null,
    ): OutreachActivity {
        return OutreachActivity::create([
            'outreach_target_id' => $target->id,
            'user_id' => $actor?->id,
            'type' => $type,
            'body' => $body,
            'outcome' => $outcome,
            'occurred_at' => now(),
        ]);
    }

    /** Merekod kontak yang baru dijumpai dan menolak peringkat ke hadapan. */
    public function recordContact(OutreachTarget $target, array $contact, ?User $actor = null): OutreachTarget
    {
        $target->update([
            'contact_name' => $contact['contact_name'] ?? $target->contact_name,
            'contact_role' => $contact['contact_role'] ?? $target->contact_role,
            'contact_phone' => $contact['contact_phone'] ?? $target->contact_phone,
            'contact_email' => $contact['contact_email'] ?? $target->contact_email,
            'contact_note' => $contact['contact_note'] ?? $target->contact_note,
            'contact_found_at' => $target->contact_found_at ?? now(),
        ]);

        $this->log($target, OutreachActivityType::Nota, 'Kontak lokasi direkodkan.', null, $actor);

        // Hanya tolak ke hadapan; jangan undurkan sasaran yang sudah maju.
        if (in_array($target->stage, [OutreachStage::Sasaran, OutreachStage::CariKontak], true)) {
            return $this->moveStage($target, OutreachStage::KontakDijumpai, null, $actor);
        }

        return $target->fresh();
    }

    /**
     * Menukar sasaran yang telah bersetuju kepada permohonan rasmi.
     * Selepas ini, aliran permohonan sedia ada mengambil alih sepenuhnya.
     */
    public function convertToApplication(
        OutreachTarget $target,
        array $overrides,
        ?User $actor = null,
    ): Application {
        if ($target->application_id) {
            return $target->application;
        }

        return DB::transaction(function () use ($target, $overrides, $actor) {
            $application = $this->applications->submit([
                'applicant_name' => $target->contact_name ?: $target->name,
                'applicant_phone' => $target->contact_phone ?: ($actor?->phone ?? ''),
                'applicant_email' => $target->contact_email,
                'background' => ApplicantBackground::WakilMasjid,
                'state_id' => $target->state_id,
                'district_id' => $target->district_id,
                'venue_name' => $target->name,
                'venue_address' => $target->address ?: $target->locationLabel(),
                'venue_maps_url' => $target->google_maps_url,
                'venue_consent' => VenueConsent::SudahBersetuju,
                'venue_pic_name' => $target->contact_name,
                'venue_pic_phone' => $target->contact_phone,
                'event_category_id' => $overrides['event_category_id'],
                'topic' => $overrides['topic'],
                'preferred_date_1' => $overrides['preferred_date_1'],
                'preferred_date_2' => $overrides['preferred_date_2'] ?? null,
                'estimated_attendees' => $overrides['estimated_attendees'] ?? AttendeeEstimate::F101_300,
                'target_audience' => $overrides['target_audience'] ?? TargetAudience::Umum,
                'notes' => "Dijana daripada sasaran jelajah {$target->reference_no}. Sumber: {$target->sourceLabel()}.",
                // Tanpa rujukan, biarkan permohonan menyelesaikan Penggeraknya
                // sendiri daripada kontak lokasi. Menghantar $actor di sini
                // menjadikan admin sebagai Penggerak program itu.
            ], $target->referrer);

            $target->update(['application_id' => $application->id]);

            $this->moveStage($target, OutreachStage::Dijadualkan,
                "Ditukar kepada permohonan {$application->reference_no}.", $actor);

            ActivityLogger::log('outreach.converted', $target,
                "{$target->name} ditukar kepada permohonan {$application->reference_no}.");

            return $application;
        });
    }

    /** Ringkasan corong untuk papan dan laporan. */
    public function funnel(?int $assignedTo = null): array
    {
        $rows = OutreachTarget::query()
            ->when($assignedTo, fn ($q) => $q->where('assigned_to', $assignedTo))
            ->selectRaw('stage, COUNT(*) as jumlah')
            ->groupBy('stage')
            ->pluck('jumlah', 'stage');

        return collect(OutreachStage::cases())
            ->mapWithKeys(fn ($s) => [$s->value => (int) ($rows[$s->value] ?? 0)])
            ->all();
    }

    /**
     * Prestasi setiap rakan yang membawa lokasi kepada kami —
     * berapa dibawa, berapa menjadi jelajah sebenar.
     */
    public function partnerPerformance()
    {
        return OutreachTarget::query()
            ->where('source', OutreachSource::Rakan->value)
            ->whereNotNull('partner_id')
            ->selectRaw('partner_id, COUNT(*) as jumlah, '
                .'SUM(stage = ?) as berjaya, '
                .'SUM(stage NOT IN (?, ?)) as aktif', [
                    OutreachStage::Berjaya->value,
                    OutreachStage::Berjaya->value,
                    OutreachStage::TidakBerminat->value,
                ])
            ->groupBy('partner_id')
            ->with('partner')
            ->orderByDesc('berjaya')
            ->orderByDesc('jumlah')
            ->get();
    }

    /** Prestasi setiap staf yang mengejar sasaran. */
    public function staffPerformance()
    {
        return OutreachTarget::query()
            ->whereNotNull('assigned_to')
            ->selectRaw('assigned_to, COUNT(*) as jumlah, '
                .'SUM(stage = ?) as berjaya, '
                .'SUM(stage NOT IN (?, ?)) as aktif', [
                    OutreachStage::Berjaya->value,
                    OutreachStage::Berjaya->value,
                    OutreachStage::TidakBerminat->value,
                ])
            ->groupBy('assigned_to')
            ->with('assignee')
            ->orderByDesc('berjaya')
            ->get();
    }
}
