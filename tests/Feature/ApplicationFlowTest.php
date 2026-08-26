<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\EventStatus;
use App\Livewire\Public\ApplyForm;
use App\Models\Application;
use App\Models\District;
use App\Models\EventCategory;
use App\Models\QrToken;
use App\Models\State;
use App\Services\ApplicationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class ApplicationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
        Notification::fake();
    }

    public function test_pengguna_boleh_menghantar_permohonan_melalui_borang(): void
    {
        $state = State::where('code', 'KEL')->firstOrFail();

        Livewire::test(ApplyForm::class)
            ->set('applicant_name', 'Ahmad bin Abdullah')
            ->set('applicant_phone', '012-345 6789')
            ->set('applicant_email', 'ahmad@ujian.test')
            ->set('state_id', (string) $state->id)
            ->set('district_id', (string) District::where('state_id', $state->id)->value('id'))
            ->set('background', 'wakil_masjid')
            ->call('next')
            ->assertHasNoErrors()
            ->assertSet('step', 2)
            ->set('venue_name', 'Masjid Al-Ujian Kubang Kerian')
            ->set('venue_address', 'Jalan Ujian 1, 16150 Kota Bharu')
            ->set('venue_consent', 'sudah_bersetuju')
            ->call('next')
            ->assertHasNoErrors()
            ->assertSet('step', 3)
            ->set('event_category_id', (string) EventCategory::value('id'))
            ->set('topic', 'Kami mahukan sesi tentang cara qadha solat untuk ahli kariah.')
            ->set('preferred_date_1', now()->addDays(45)->toDateString())
            ->set('estimated_attendees', '101_300')
            ->set('target_audience', 'umum')
            ->call('next')
            ->assertHasNoErrors()
            ->assertSet('step', 4)
            ->set('privacy', true)
            ->call('submit')
            ->assertHasNoErrors();

        $application = Application::first();

        $this->assertNotNull($application);
        $this->assertSame('Masjid Al-Ujian Kubang Kerian', $application->venue_name);
        $this->assertSame(ApplicationStatus::Diterima, $application->status);
        $this->assertStringStartsWith('BDJ-P', $application->reference_no);

        // Nombor telefon dinormalkan kepada format antarabangsa.
        $this->assertSame('60123456789', $application->applicant_phone);

        // Akaun Penggerak dicipta automatik supaya pemohon boleh menjejak status.
        $this->assertNotNull($application->user_id);
        $this->assertTrue($application->user->canAccessPenggerak());
    }

    public function test_borang_menolak_langkah_yang_tidak_lengkap(): void
    {
        Livewire::test(ApplyForm::class)
            ->call('next')
            ->assertHasErrors(['applicant_name', 'applicant_phone', 'state_id', 'background'])
            ->assertSet('step', 1);
    }

    public function test_tarikh_cadangan_mesti_selepas_hari_ini(): void
    {
        Livewire::test(ApplyForm::class)
            ->set('step', 3)
            ->set('event_category_id', (string) EventCategory::value('id'))
            ->set('topic', 'Topik yang cukup panjang untuk lulus pengesahan.')
            ->set('preferred_date_1', now()->subDay()->toDateString())
            ->set('estimated_attendees', 'bawah_50')
            ->set('target_audience', 'umum')
            ->call('next')
            ->assertHasErrors(['preferred_date_1']);
    }

    public function test_admin_boleh_menukar_status_dan_merekod_sejarah(): void
    {
        $admin = $this->admin();
        $application = app(ApplicationService::class)->submit($this->applicationPayload());

        app(ApplicationService::class)->changeStatus(
            $application,
            ApplicationStatus::DalamSemakan,
            'Kami sedang menyemak permohonan anda.',
            'Nota dalaman: lokasi sesuai.',
            $admin,
        );

        $application->refresh();

        $this->assertSame(ApplicationStatus::DalamSemakan, $application->status);
        $this->assertDatabaseHas('application_status_histories', [
            'application_id' => $application->id,
            'to_status' => ApplicationStatus::DalamSemakan->value,
            'internal_note' => 'Nota dalaman: lokasi sesuai.',
        ]);
    }

    public function test_mengesahkan_program_menjana_eventspace_lengkap(): void
    {
        $admin = $this->admin();
        $application = app(ApplicationService::class)->submit($this->applicationPayload());

        app(ApplicationService::class)->changeStatus(
            $application,
            ApplicationStatus::ProgramDisahkan,
            'Program anda telah disahkan.',
            null,
            $admin,
            ['capacity' => 250],
        );

        $application->refresh();
        $event = $application->event;

        $this->assertNotNull($event, 'EventSpace sepatutnya dijana automatik.');
        $this->assertSame(EventStatus::Diterbitkan, $event->status);
        $this->assertSame(250, $event->capacity);

        // Slug, kod pendek dan URL awam
        $this->assertNotEmpty($event->slug);
        $this->assertNotEmpty($event->short_code);
        $this->assertStringContainsString('/jelajah/', $event->publicUrl());
        $this->assertStringContainsString('/j/'.$event->short_code, $event->shortUrl());

        // QR pendaftaran awam
        $this->assertTrue(
            QrToken::where('tokenable_type', \App\Models\Event::class)
                ->where('tokenable_id', $event->id)
                ->where('purpose', 'pendaftaran')
                ->exists(),
        );

        // Penggerak dipautkan sebagai pemilik program
        $this->assertTrue($event->mobilizers()->whereKey($application->user_id)->exists());

        // Tindakan direkod dalam log aktiviti
        $this->assertDatabaseHas('activity_logs', ['action' => 'event.generated']);
    }

    public function test_eventspace_tidak_dijana_dua_kali(): void
    {
        $admin = $this->admin();
        $application = app(ApplicationService::class)->submit($this->applicationPayload());

        $service = app(ApplicationService::class);
        $service->changeStatus($application, ApplicationStatus::ProgramDisahkan, null, null, $admin);
        $firstEventId = $application->fresh()->event_id;

        $service->changeStatus($application->fresh(), ApplicationStatus::ProgramDisahkan, null, null, $admin);

        $this->assertSame($firstEventId, $application->fresh()->event_id);
        $this->assertSame(1, \App\Models\Event::count());
    }
}
