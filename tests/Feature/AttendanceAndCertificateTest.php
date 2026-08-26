<?php

namespace Tests\Feature;

use App\Enums\AttendanceMethod;
use App\Enums\CertificateStatus;
use App\Enums\CertificateType;
use App\Enums\EventStatus;
use App\Livewire\CheckIn\Scanner;
use App\Models\Certificate;
use App\Models\QrToken;
use App\Models\Registration;
use App\Services\AttendanceService;
use App\Services\CertificateService;
use App\Services\CheckInResult;
use App\Services\EventLifecycleService;
use App\Services\RegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class AttendanceAndCertificateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
        Notification::fake();
    }

    private function tokenFor(Registration $registration): string
    {
        return QrToken::where('tokenable_type', Registration::class)
            ->where('tokenable_id', $registration->id)
            ->where('purpose', 'checkin')
            ->value('token');
    }

    public function test_check_in_qr_berjaya_merekod_kehadiran(): void
    {
        $event = $this->makeEvent();
        $admin = $this->admin();
        $registration = app(RegistrationService::class)->register($event, $this->registrationPayload());

        $attendance = app(AttendanceService::class);
        $lookup = $attendance->resolveToken($this->tokenFor($registration), $event);

        $this->assertSame(CheckInResult::OK, $lookup->outcome);

        $result = $attendance->checkIn($lookup->registration, $admin, AttendanceMethod::Qr);

        $this->assertSame(CheckInResult::CHECKED_IN, $result->outcome);
        $this->assertTrue($registration->fresh()->hasAttended());
        $this->assertDatabaseHas('attendance_records', [
            'registration_id' => $registration->id,
            'method' => AttendanceMethod::Qr->value,
        ]);
    }

    public function test_check_in_berganda_ditolak(): void
    {
        $event = $this->makeEvent();
        $admin = $this->admin();
        $registration = app(RegistrationService::class)->register($event, $this->registrationPayload());

        $attendance = app(AttendanceService::class);
        $attendance->checkIn($registration, $admin);
        $second = $attendance->checkIn($registration->fresh(), $admin);

        $this->assertSame(CheckInResult::DUPLICATE, $second->outcome);
        $this->assertSame(1, $event->fresh()->attendanceRecords()->count());
    }

    public function test_qr_program_lain_ditolak(): void
    {
        $eventA = $this->makeEvent();
        $eventB = $this->makeEvent();
        $registration = app(RegistrationService::class)->register($eventA, $this->registrationPayload());

        $result = app(AttendanceService::class)->resolveToken($this->tokenFor($registration), $eventB);

        $this->assertSame(CheckInResult::FAIL, $result->outcome);
        $this->assertStringContainsString('program lain', $result->message);
    }

    public function test_token_tidak_dikenali_ditolak(): void
    {
        $event = $this->makeEvent();

        $result = app(AttendanceService::class)->resolveToken('TOKENPALSU123', $event);

        $this->assertSame(CheckInResult::FAIL, $result->outcome);
    }

    public function test_pengimbas_boleh_mendaftar_walk_in_dan_terus_check_in(): void
    {
        $event = $this->makeEvent(['starts_at' => now()->addHour()]);
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(Scanner::class, ['event' => $event])
            ->set('showWalkIn', true)
            ->set('walkInName', 'Peserta Walk In')
            ->set('walkInPhone', '0191234567')
            ->call('registerWalkIn')
            ->assertHasNoErrors();

        $registration = Registration::where('event_id', $event->id)->where('source', 'walk_in')->first();

        $this->assertNotNull($registration);
        $this->assertTrue($registration->hasAttended());
        $this->assertDatabaseHas('attendance_records', [
            'registration_id' => $registration->id,
            'method' => AttendanceMethod::WalkIn->value,
        ]);
    }

    public function test_carian_manual_menemui_peserta(): void
    {
        $event = $this->makeEvent();
        $admin = $this->admin();
        app(RegistrationService::class)->register($event, $this->registrationPayload(['name' => 'Zulkifli bin Hassan']));

        Livewire::actingAs($admin)
            ->test(Scanner::class, ['event' => $event])
            ->set('search', 'Zulkifli')
            ->assertSee('Zulkifli bin Hassan');
    }

    public function test_penggerak_program_lain_tidak_boleh_membuka_pengimbas(): void
    {
        $event = $this->makeEvent();
        $penggerak = $this->penggerak();

        $this->actingAs($penggerak)
            ->get(route('checkin.scanner', $event))
            ->assertForbidden();
    }

    public function test_peserta_hadir_menerima_sijil_dan_yang_tidak_hadir_tidak(): void
    {
        $event = $this->makeEvent();
        $admin = $this->admin();
        $service = app(RegistrationService::class);

        $hadir = $service->register($event, $this->registrationPayload(['name' => 'Peserta Hadir']));
        $tidakHadir = $service->register($event->fresh(), $this->registrationPayload(['name' => 'Peserta Tidak Hadir']));

        $event = $this->movePast($event);
        app(AttendanceService::class)->checkIn($hadir, $admin);

        $issued = app(CertificateService::class)->issueForEvent($event->fresh());

        $this->assertSame(1, $issued);
        $this->assertNotNull($hadir->fresh()->certificate);
        $this->assertNull($tidakHadir->fresh()->certificate);
    }

    public function test_nombor_sijil_mengikut_format_rasmi(): void
    {
        $event = $this->makeEvent();
        $admin = $this->admin();

        $registration = app(RegistrationService::class)->register($event, $this->registrationPayload());
        $event = $this->movePast($event);
        app(AttendanceService::class)->checkIn($registration, $admin);
        app(CertificateService::class)->issueForEvent($event->fresh());

        $certificate = $registration->fresh()->certificate;

        $this->assertMatchesRegularExpression(
            '/^BDJ-\d{4}-[A-Z]{2,4}-\d{6}$/',
            $certificate->certificate_number,
        );
    }

    public function test_pengesahan_sijil_awam_berfungsi(): void
    {
        $event = $this->makeEvent();
        $admin = $this->admin();

        $registration = app(RegistrationService::class)->register($event, $this->registrationPayload());
        $event = $this->movePast($event);
        app(AttendanceService::class)->checkIn($registration, $admin);
        app(CertificateService::class)->issueForEvent($event->fresh());

        $certificate = $registration->fresh()->certificate;

        $this->get(route('sijil.semak.show', $certificate->certificate_number))
            ->assertOk()
            ->assertSee('Sijil Ini Sah')
            ->assertSee($certificate->recipient_name);

        $this->get(route('sijil.semak.show', 'BDJ-TIADA-DALAM-REKOD'))
            ->assertOk()
            ->assertSee('Sijil Tidak Ditemui');
    }

    public function test_sijil_dijana_semula_menandakan_yang_lama_sebagai_digantikan(): void
    {
        $event = $this->makeEvent();
        $admin = $this->admin();

        $registration = app(RegistrationService::class)->register($event, $this->registrationPayload());
        $event = $this->movePast($event);
        app(AttendanceService::class)->checkIn($registration, $admin);
        app(CertificateService::class)->issueForEvent($event->fresh());

        $original = $registration->fresh()->certificate;
        $replacement = app(CertificateService::class)
            ->regenerate($original, 'Nama Dibetulkan bin Abdullah', $admin);

        $original->refresh();

        $this->assertSame('Nama Dibetulkan bin Abdullah', $replacement->recipient_name);
        $this->assertNotSame($original->id, $replacement->id);
        $this->assertSame(CertificateStatus::Digantikan, $original->status);
        $this->assertSame($replacement->id, $original->superseded_by_id);
    }

    public function test_sijil_dibatalkan_tidak_boleh_dimuat_turun(): void
    {
        $event = $this->makeEvent();
        $admin = $this->admin();

        $registration = app(RegistrationService::class)->register($event, $this->registrationPayload());
        $event = $this->movePast($event);
        app(AttendanceService::class)->checkIn($registration, $admin);
        app(CertificateService::class)->issueForEvent($event->fresh());

        $certificate = $registration->fresh()->certificate;
        app(CertificateService::class)->revoke($certificate, 'Nama tidak sah.', $admin);

        $this->get(route('sijil.muat-turun', $certificate->fresh()->public_id))
            ->assertStatus(410);
    }

    public function test_menutup_program_melepaskan_sijil_dan_mengemas_kini_statistik(): void
    {
        $event = $this->makeEvent();
        $admin = $this->admin();
        $service = app(RegistrationService::class);

        $a = $service->register($event, $this->registrationPayload());
        $b = $service->register($event->fresh(), $this->registrationPayload());

        $event = $this->movePast($event);
        app(AttendanceService::class)->checkIn($a, $admin);
        app(AttendanceService::class)->checkIn($b, $admin);

        app(EventLifecycleService::class)->complete($event->fresh(), $admin);

        $event->refresh();

        $this->assertSame(EventStatus::Selesai, $event->status);
        $this->assertSame(2, $event->attended_count);
        $this->assertSame(2, Certificate::where('event_id', $event->id)
            ->where('type', CertificateType::Penyertaan)->count());
    }
}
