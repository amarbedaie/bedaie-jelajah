<?php

namespace Tests\Feature;

use App\Livewire\Public\FeedbackForm;
use App\Livewire\Public\InterestForm;
use App\Models\AreaInterestRequest;
use App\Models\State;
use App\Services\AttendanceService;
use App\Services\CertificateService;
use App\Services\EventLifecycleService;
use App\Services\ImpactStatsService;
use App\Services\RegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class ImpactAndPassportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
        Notification::fake();
    }

    public function test_statistik_peta_dikemas_kini_selepas_program_selesai(): void
    {
        $stats = app(ImpactStatsService::class);

        $before = $stats->headline();
        $this->assertSame(0, $before['program']);

        $event = $this->makeEvent();
        $admin = $this->admin();
        $registration = app(RegistrationService::class)->register($event, $this->registrationPayload());

        $event = $this->movePast($event);
        app(AttendanceService::class)->checkIn($registration, $admin);
        app(EventLifecycleService::class)->complete($event->fresh(), $admin);

        ImpactStatsService::flush();
        $after = $stats->headline();

        $this->assertSame(1, $after['program']);
        $this->assertSame(1, $after['peserta']);
        $this->assertGreaterThanOrEqual(1, $after['negeri']);

        // Peta menandakan negeri berkenaan sebagai telah dijelajahi.
        $selangor = $stats->stateMap()->firstWhere('code', 'SGR');
        $this->assertSame('dijelajahi', $selangor['status']);
        $this->assertSame(1, $selangor['completed']);
    }

    public function test_peta_menandakan_negeri_dengan_program_akan_datang(): void
    {
        $this->makeEvent();
        ImpactStatsService::flush();

        $selangor = app(ImpactStatsService::class)->stateMap()->firstWhere('code', 'SGR');

        $this->assertSame('akan_datang', $selangor['status']);
        $this->assertSame(1, $selangor['upcoming']);
    }

    public function test_permintaan_kawasan_direkod_dan_digabungkan(): void
    {
        $state = State::where('code', 'KDH')->firstOrFail();

        Livewire::test(InterestForm::class)
            ->set('name', 'Rokiah binti Daud')
            ->set('phone', '012-555 6677')
            ->set('state_id', (string) $state->id)
            ->set('postcode', '05100')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true);

        $this->assertDatabaseHas('area_interest_requests', [
            'phone' => '60125556677',
            'state_id' => $state->id,
            'postcode' => '05100',
        ]);

        // Hantar semula nombor yang sama — tidak mencipta rekod berganda.
        Livewire::test(InterestForm::class)
            ->set('name', 'Rokiah binti Daud')
            ->set('phone', '60125556677')
            ->set('state_id', (string) $state->id)
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertSame(1, AreaInterestRequest::count());
    }

    public function test_permintaan_individu_tidak_didedahkan_kepada_umum(): void
    {
        $state = State::where('code', 'KDH')->firstOrFail();

        AreaInterestRequest::create([
            'name' => 'Nama Sangat Peribadi',
            'phone' => '60129998888',
            'state_id' => $state->id,
        ]);

        $this->get(route('peta.negeri', $state->slug))
            ->assertOk()
            ->assertDontSee('Nama Sangat Peribadi')
            ->assertDontSee('60129998888');
    }

    public function test_admin_melihat_kawasan_permintaan_tertinggi(): void
    {
        $state = State::where('code', 'NSN')->firstOrFail();

        foreach (range(1, 3) as $i) {
            AreaInterestRequest::create([
                'name' => "Peminat {$i}",
                'phone' => '6012000'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'state_id' => $state->id,
            ]);
        }

        $areas = app(ImpactStatsService::class)->topDemandAreas();

        $this->assertSame(3, (int) $areas->first()->total);
        $this->assertSame(3, (int) $areas->first()->individuals);
    }

    public function test_pasport_ilmu_memaparkan_rekod_peserta(): void
    {
        $peserta = $this->peserta();
        $admin = $this->admin();
        $event = $this->makeEvent(['learning_hours' => 2.5, 'title' => 'Program Pasport Ujian']);

        $registration = app(RegistrationService::class)->register(
            $event,
            $this->registrationPayload(['name' => $peserta->name]),
            $peserta,
        );

        $event = $this->movePast($event);
        app(AttendanceService::class)->checkIn($registration, $admin);
        app(CertificateService::class)->issueForEvent($event->fresh());

        $this->actingAs($peserta)
            ->get(route('peserta.dashboard'))
            ->assertOk()
            ->assertSee('Pasport Ilmu BeDaie')
            ->assertSee('Program Pasport Ujian')
            ->assertSee('2.5');

        $this->actingAs($peserta)
            ->get(route('peserta.sijil'))
            ->assertOk()
            ->assertSee($registration->fresh()->certificate->certificate_number);
    }

    public function test_maklum_balas_disimpan_dan_boleh_melepaskan_sijil(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent();
        $registration = app(RegistrationService::class)->register($event, $this->registrationPayload());

        $event = $this->movePast($event);
        app(AttendanceService::class)->checkIn($registration, $admin);

        Livewire::test(FeedbackForm::class, ['registration' => $registration->fresh()])
            ->call('setRating', 5)
            ->set('most_beneficial', 'Penerangan yang sangat mudah difahami.')
            ->set('next_topic', 'Fiqh muamalat')
            ->set('wants_advanced', true)
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true);

        $this->assertDatabaseHas('feedback', [
            'registration_id' => $registration->id,
            'rating' => 5,
            'wants_advanced' => 1,
        ]);
    }

    public function test_maklum_balas_memerlukan_penilaian(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent();
        $registration = app(RegistrationService::class)->register($event, $this->registrationPayload());

        $event = $this->movePast($event);
        app(AttendanceService::class)->checkIn($registration, $admin);

        Livewire::test(FeedbackForm::class, ['registration' => $registration->fresh()])
            ->call('submit')
            ->assertHasErrors(['rating']);
    }

    public function test_laporan_program_mengira_kadar_kehadiran(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent(['capacity' => 10]);
        $service = app(RegistrationService::class);

        $a = $service->register($event, $this->registrationPayload());
        $b = $service->register($event->fresh(), $this->registrationPayload());
        $service->register($event->fresh(), $this->registrationPayload());

        $event = $this->movePast($event);
        app(AttendanceService::class)->checkIn($a, $admin);
        app(AttendanceService::class)->checkIn($b, $admin);

        $report = app(ImpactStatsService::class)->eventReport($event->fresh());

        $this->assertSame(3, $report['registered']);
        $this->assertSame(2, $report['attended']);
        $this->assertEqualsWithDelta(66.7, $report['attendance_rate'], 0.1);
    }
}
