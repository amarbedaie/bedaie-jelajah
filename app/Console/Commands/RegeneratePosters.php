<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Services\PosterGenerator;
use Illuminate\Console\Command;

/**
 * Menjana semula setiap poster dan imej kad program.
 *
 * Poster ialah fail storan, bukan kod — jadi perubahan palet tidak sampai
 * ke pelayan melalui deploy. Perintah ini yang membawanya.
 */
class RegeneratePosters extends Command
{
    protected $signature = 'jelajah:jana-poster {--force : Jana semula walaupun poster sudah wujud}';

    protected $description = 'Menjana semula poster dan imej kad untuk semua program';

    public function handle(PosterGenerator $posters): int
    {
        if (! $posters->available()) {
            $this->error('Imagick tidak tersedia pada pelayan ini.');

            return self::FAILURE;
        }

        $events = Event::query()
            ->when(! $this->option('force'), fn ($q) => $q->whereNotNull('poster_path'))
            ->get();

        $done = 0;
        $failed = 0;

        foreach ($events as $event) {
            try {
                $posters->generate($event, $event->poster_style);
                $done++;
            } catch (\Throwable $e) {
                $failed++;
                $this->warn("Gagal {$event->id}: {$e->getMessage()}");
            }
        }

        $this->info("Selesai: {$done} dijana, {$failed} gagal.");

        return self::SUCCESS;
    }
}
