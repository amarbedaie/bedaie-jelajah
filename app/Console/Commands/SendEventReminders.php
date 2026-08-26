<?php

namespace App\Console\Commands;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\EventReminderDispatch;
use App\Models\Registration;
use App\Services\Notifications\NotificationRecipient;
use App\Services\Notifications\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;

/**
 * Menghantar peringatan program (7 hari, 1 hari, 2 jam sebelum).
 *
 * Idempotent: setiap (pendaftaran, jenis peringatan) direkod dengan unique
 * index, jadi tugas berjadual boleh dijalankan berulang kali dengan selamat.
 */
class SendEventReminders extends Command
{
    protected $signature = 'jelajah:hantar-peringatan {--dry-run : Papar sahaja tanpa menghantar}';

    protected $description = 'Menghantar peringatan kepada peserta sebelum program bermula';

    public function handle(NotificationService $notifications): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $total = 0;

        foreach (config('jelajah.reminders', []) as $reminder) {
            $key = $reminder['key'];
            $offsetHours = (int) $reminder['offset_hours'];

            // Tetingkap 1 jam + 15 minit toleransi supaya tugas setiap jam
            // tetap menangkapnya walaupun berjalan lewat sedikit.
            $windowStart = now()->addHours($offsetHours);
            $windowEnd = $windowStart->copy()->addMinutes(75);

            $events = Event::where('status', EventStatus::Diterbitkan)
                ->whereBetween('starts_at', [$windowStart, $windowEnd])
                ->with(['venue', 'speaker'])
                ->get();

            foreach ($events as $event) {
                $sent = $this->remindFor($event, $key, $notifications, $dryRun);
                $total += $sent;

                if ($sent > 0) {
                    $this->line("  {$event->short_code} · {$key}: {$sent} peserta");
                }
            }
        }

        $this->info($dryRun
            ? "Larian kering: {$total} peringatan akan dihantar."
            : "Selesai: {$total} peringatan dibariskan.");

        return self::SUCCESS;
    }

    private function remindFor(Event $event, string $key, NotificationService $notifications, bool $dryRun): int
    {
        $sent = 0;

        Registration::where('event_id', $event->id)
            ->confirmed()
            ->chunkById(200, function ($registrations) use ($event, $key, $notifications, $dryRun, &$sent) {
                foreach ($registrations as $registration) {
                    if ($dryRun) {
                        if (! $this->alreadySent($registration->id, $key)) {
                            $sent++;
                        }

                        continue;
                    }

                    // Unique index menjadi kunci: sisipan gagal bermakna sudah dihantar.
                    try {
                        EventReminderDispatch::create([
                            'event_id' => $event->id,
                            'registration_id' => $registration->id,
                            'reminder_key' => $key,
                            'dispatched_at' => now(),
                        ]);
                    } catch (QueryException) {
                        continue;
                    }

                    $notifications->queue(
                        'peringatan_program',
                        NotificationRecipient::fromRegistration($registration),
                        [
                            'participant_name' => $registration->name,
                            'event_name' => $event->title,
                            'event_date' => $event->dateLabel(),
                            'event_time' => $event->timeLabel(),
                            'venue' => $event->venue?->name ?? $event->locationLabel(),
                            'qr_link' => $registration->ticketUrl(),
                        ],
                        $registration,
                        [
                            'url' => $registration->ticketUrl(),
                            'action_label' => 'Lihat Tiket & QR',
                        ],
                    );

                    $sent++;
                }
            });

        return $sent;
    }

    private function alreadySent(int $registrationId, string $key): bool
    {
        return EventReminderDispatch::where('registration_id', $registrationId)
            ->where('reminder_key', $key)
            ->exists();
    }
}
