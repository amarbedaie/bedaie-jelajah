<?php

use App\Console\Commands\AdvanceEventLifecycle;
use App\Console\Commands\SendEventReminders;
use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\EventReminderDispatch;
use App\Services\ActivityLogger;
use App\Services\RegistrationService;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Tugas berjadual
|--------------------------------------------------------------------------
| Jalankan penjadual dengan satu entri cron:
|   * * * * * cd /laluan/projek && php artisan schedule:run >> /dev/null 2>&1
*/

// Peringatan 7 hari / 1 hari / 2 jam sebelum program.
Schedule::command(SendEventReminders::class)
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();

// Tandakan program berlangsung, dan tutup program yang telah tamat
// (sijil dilepaskan + permintaan maklum balas dihantar).
Schedule::command(AdvanceEventLifecycle::class)
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();

// Naikkan peserta senarai menunggu apabila ada tempat kosong.
Schedule::call(function () {
    $promoted = 0;

    // upcoming() menyusun mengikut starts_at; digabungkan dengan chunkById
    // itu meninggalkan program yang ID-nya lebih kecil daripada kelompok
    // pertama. Susun mengikut kunci sahaja di sini.
    Event::query()
        ->whereIn('status', [EventStatus::Diterbitkan, EventStatus::Berlangsung])
        ->where('starts_at', '>=', now())
        ->where('allow_waiting_list', true)
        ->chunkById(50, function ($events) use (&$promoted) {
            foreach ($events as $event) {
                $promoted += app(RegistrationService::class)->promoteFromWaitlist($event);
            }
        });

    if ($promoted > 0) {
        ActivityLogger::log(
            'waitlist.promoted',
            null,
            "{$promoted} peserta dinaikkan daripada senarai menunggu.",
        );
    }
})->name('jelajah:naikkan-senarai-menunggu')->everyThirtyMinutes()->withoutOverlapping();

// Buang rekod peringatan lama supaya jadual kekal ringan.
Schedule::call(fn () => EventReminderDispatch::where('dispatched_at', '<', now()->subMonths(6))->delete())
    ->name('jelajah:bersih-rekod-peringatan')
    ->weekly();
