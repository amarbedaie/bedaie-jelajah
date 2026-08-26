<?php

namespace App\Console\Commands;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Services\EventLifecycleService;
use Illuminate\Console\Command;

/**
 * Menggerakkan program mengikut masa:
 *  - Program yang bermula hari ini ditandakan "Berlangsung".
 *  - Program yang telah tamat ditutup: pendaftaran ditutup, sijil
 *    dilepaskan kepada yang hadir, dan permintaan maklum balas dihantar.
 */
class AdvanceEventLifecycle extends Command
{
    protected $signature = 'jelajah:kemas-kini-program
                            {--grace=4 : Jam selepas tamat sebelum program ditutup}
                            {--dry-run : Papar sahaja tanpa mengubah data}';

    protected $description = 'Menandakan program berlangsung dan menutup program yang telah tamat';

    public function handle(EventLifecycleService $lifecycle): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $grace = (int) $this->option('grace');

        // ── Program yang sedang berlangsung ──
        $ongoing = Event::where('status', EventStatus::Diterbitkan)
            ->where('starts_at', '<=', now())
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->get();

        foreach ($ongoing as $event) {
            $this->line("  Berlangsung: {$event->short_code} — {$event->title}");

            if (! $dryRun) {
                $lifecycle->markOngoing($event);
            }
        }

        // ── Program yang perlu ditutup ──
        $finished = Event::whereIn('status', [EventStatus::Diterbitkan, EventStatus::Berlangsung])
            ->where('starts_at', '<', now()->subHours($grace))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '<', now()->subHours($grace)))
            ->get();

        foreach ($finished as $event) {
            if ($dryRun) {
                $this->line("  Akan ditutup: {$event->short_code} — {$event->title}");

                continue;
            }

            $result = $lifecycle->complete($event, null);

            $this->line(sprintf(
                '  Ditutup: %s — %s sijil, %s permintaan maklum balas',
                $event->short_code,
                $result['certificates'] ?? 0,
                $result['feedback_requests'] ?? 0,
            ));
        }

        $this->info(sprintf(
            '%s: %d berlangsung, %d ditutup.',
            $dryRun ? 'Larian kering' : 'Selesai',
            $ongoing->count(),
            $finished->count(),
        ));

        return self::SUCCESS;
    }
}
