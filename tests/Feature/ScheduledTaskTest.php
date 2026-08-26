<?php

namespace Tests\Feature;

use App\Enums\EventStatus;
use App\Jobs\SendNotificationJob;
use App\Models\Certificate;
use App\Models\EventReminderDispatch;
use App\Services\AttendanceService;
use App\Services\RegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ScheduledTaskTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
    }

    public function test_peringatan_dihantar_untuk_program_dalam_tetingkap(): void
    {
        Queue::fake();

        // Program 24 jam dari sekarang — sepadan dengan peringatan 1 hari.
        $event = $this->makeEvent(['starts_at' => now()->addHours(24)->addMinutes(20)]);
        app(RegistrationService::class)->register($event, $this->registrationPayload());
        app(RegistrationService::class)->register($event->fresh(), $this->registrationPayload());

        $this->artisan('jelajah:hantar-peringatan')->assertSuccessful();

        $this->assertSame(2, EventReminderDispatch::where('reminder_key', 'reminder_1_day')->count());
        Queue::assertPushed(SendNotificationJob::class);
    }

    public function test_peringatan_yang_sama_tidak_dihantar_dua_kali(): void
    {
        Queue::fake();

        $event = $this->makeEvent(['starts_at' => now()->addHours(24)->addMinutes(20)]);
        app(RegistrationService::class)->register($event, $this->registrationPayload());

        $this->artisan('jelajah:hantar-peringatan')->assertSuccessful();
        $this->artisan('jelajah:hantar-peringatan')->assertSuccessful();

        $this->assertSame(1, EventReminderDispatch::count());
    }

    public function test_program_di_luar_tetingkap_tidak_diperingatkan(): void
    {
        Queue::fake();

        // 60 hari dari sekarang — jauh daripada mana-mana tetingkap peringatan.
        $event = $this->makeEvent(['starts_at' => now()->addDays(60)]);
        app(RegistrationService::class)->register($event, $this->registrationPayload());

        $this->artisan('jelajah:hantar-peringatan')->assertSuccessful();

        $this->assertSame(0, EventReminderDispatch::count());
    }

    public function test_program_tamat_ditutup_secara_automatik_dan_sijil_dilepaskan(): void
    {
        Queue::fake();

        $admin = $this->admin();
        $event = $this->makeEvent();
        $registration = app(RegistrationService::class)->register($event, $this->registrationPayload());

        $event = $this->movePast($event);
        app(AttendanceService::class)->checkIn($registration, $admin);

        $this->artisan('jelajah:kemas-kini-program')->assertSuccessful();

        $event->refresh();

        $this->assertSame(EventStatus::Selesai, $event->status);
        $this->assertSame(1, Certificate::where('event_id', $event->id)->count());
        $this->assertNotNull($event->completed_at);
    }

    public function test_program_akan_datang_tidak_ditutup(): void
    {
        Queue::fake();

        $event = $this->makeEvent(['starts_at' => now()->addDays(7)]);

        $this->artisan('jelajah:kemas-kini-program')->assertSuccessful();

        $this->assertSame(EventStatus::Diterbitkan, $event->fresh()->status);
    }

    public function test_larian_kering_tidak_mengubah_data(): void
    {
        $event = $this->makeEvent(['starts_at' => now()->addHours(24)->addMinutes(20)]);
        app(RegistrationService::class)->register($event, $this->registrationPayload());

        // Fake selepas pendaftaran supaya hanya kesan larian kering diukur.
        Queue::fake();

        $this->artisan('jelajah:hantar-peringatan --dry-run')->assertSuccessful();

        $this->assertSame(0, EventReminderDispatch::count());
        Queue::assertNotPushed(SendNotificationJob::class);
    }
}
