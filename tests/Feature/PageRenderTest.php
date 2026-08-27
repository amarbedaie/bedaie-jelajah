<?php

namespace Tests\Feature;

use App\Enums\RegistrationStatus;
use App\Services\ApplicationService;
use App\Services\AttendanceService;
use App\Services\CertificateService;
use App\Services\RegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Menjamin tiada halaman utama yang pecah atau kosong tanpa state.
 */
class PageRenderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
        Notification::fake();
    }

    public function test_halaman_awam_dibuka_walaupun_data_kosong(): void
    {
        foreach ([
            'home', 'peta', 'program.index', 'jejak', 'kategori',
            'galeri', 'rakan', 'tentang', 'jemput', 'minat',
            'sijil.semak', 'privasi', 'terma', 'login', 'register',
        ] as $name) {
            $this->get(route($name))->assertOk();
        }
    }

    public function test_manifest_pwa_dihasilkan(): void
    {
        $this->get(route('pwa.manifest'))
            ->assertOk()
            ->assertJsonPath('name', 'BeDaie Jelajah')
            ->assertJsonPath('theme_color', '#D97757');
    }

    public function test_halaman_program_dan_pautan_pendek_berfungsi(): void
    {
        $event = $this->makeEvent(['title' => 'Jelajah Ujian Shah Alam']);

        $this->get($event->publicUrl())
            ->assertOk()
            ->assertSee('Jelajah Ujian Shah Alam')
            ->assertSee('Daftar Sekarang');

        $this->get('/j/'.$event->short_code)
            ->assertRedirect($event->publicUrl());

        $this->get(route('jelajah.daftar', [$event->state->slug, $event->slug]))
            ->assertOk()
            ->assertSee('Maklumat Peserta');
    }

    public function test_halaman_negeri_memaparkan_statistik(): void
    {
        $this->get(route('peta.negeri', 'selangor'))
            ->assertOk()
            ->assertSee('Jelajah Selangor');

        // Negeri tanpa program masih boleh dibuka dengan ajakan menjemput.
        $this->get(route('peta.negeri', 'perlis'))
            ->assertOk()
            ->assertSee('Jemput BeDaie');
    }

    public function test_dashboard_penggerak_dibuka_walaupun_tiada_program(): void
    {
        $this->actingAs($this->penggerak())
            ->get(route('penggerak.dashboard'))
            ->assertOk()
            ->assertSee('Belum ada program aktif');
    }

    public function test_semua_halaman_penggerak_dibuka(): void
    {
        $penggerak = $this->penggerak();
        $event = $this->makeEvent();
        $event->mobilizers()->attach($penggerak->id, ['role' => 'utama']);

        foreach ([
            'penggerak.dashboard', 'penggerak.permohonan', 'penggerak.program',
            'penggerak.peserta', 'penggerak.sijil', 'penggerak.profil', 'penggerak.notifikasi',
        ] as $name) {
            $this->actingAs($penggerak)->get(route($name))->assertOk();
        }

        $this->actingAs($penggerak)->get(route('penggerak.program.poster', $event))->assertOk();
        $this->actingAs($penggerak)->get(route('penggerak.program.qr', $event))
            ->assertOk()
            ->assertHeader('content-type', 'image/svg+xml');
    }

    public function test_semua_halaman_peserta_dibuka(): void
    {
        $peserta = $this->peserta();

        foreach ([
            'peserta.dashboard', 'peserta.program', 'peserta.sijil',
            'peserta.profil', 'peserta.notifikasi',
        ] as $name) {
            $this->actingAs($peserta)->get(route($name))->assertOk();
        }
    }

    public function test_semua_halaman_admin_dibuka(): void
    {
        $admin = $this->admin();

        foreach ([
            'admin.dashboard', 'admin.permohonan', 'admin.program', 'admin.kalendar',
            'admin.penggerak', 'admin.peserta', 'admin.kehadiran', 'admin.sijil',
            'admin.negeri', 'admin.permintaan', 'admin.penceramah', 'admin.kategori',
            'admin.galeri', 'admin.rakan', 'admin.laporan', 'admin.kandungan',
            'admin.template', 'admin.tetapan',
        ] as $name) {
            $this->actingAs($admin)->get(route($name))->assertOk();
        }
    }

    public function test_halaman_admin_dengan_data_lengkap_dibuka(): void
    {
        $admin = $this->admin();
        $penggerak = $this->penggerak();

        $application = app(ApplicationService::class)->submit($this->applicationPayload(), $penggerak);
        $event = $this->makeEvent();
        $event->mobilizers()->attach($penggerak->id, ['role' => 'utama']);

        $registration = app(RegistrationService::class)->register($event, $this->registrationPayload());
        $event = $this->movePast($event);
        app(AttendanceService::class)->checkIn($registration, $admin);
        app(CertificateService::class)->issueForEvent($event->fresh());

        $this->actingAs($admin)->get(route('admin.permohonan.show', $application))->assertOk();
        $this->actingAs($admin)->get(route('admin.program.show', $event))->assertOk();
        $this->actingAs($admin)->get(route('admin.kehadiran.show', $event))->assertOk();
        $this->actingAs($admin)->get(route('admin.laporan.program', $event))->assertOk();
        $this->actingAs($admin)->get(route('admin.penggerak.show', $penggerak))->assertOk();
    }

    public function test_tiket_dan_maklum_balas_dibuka_tanpa_log_masuk(): void
    {
        $event = $this->makeEvent();
        $registration = app(RegistrationService::class)->register($event, $this->registrationPayload([
            'name' => 'Peserta Tiket',
        ]));

        $this->get(route('tiket.show', $registration->public_token))
            ->assertOk()
            ->assertSee('Peserta Tiket')
            ->assertSee('Pendaftaran Disahkan');

        $this->get(route('tiket.cancel', $registration->public_token))
            ->assertOk()
            ->assertSee('Batalkan Pendaftaran?');

        $this->get(route('maklum-balas.show', $registration->public_token))
            ->assertOk()
            ->assertSee('Bagaimana pengalaman anda?');
    }

    public function test_fail_kalendar_ics_dihasilkan(): void
    {
        $event = $this->makeEvent();
        $registration = app(RegistrationService::class)->register($event, $this->registrationPayload());

        $response = $this->get(route('tiket.kalendar', $registration->public_token));

        $response->assertOk()->assertHeader('content-type', 'text/calendar; charset=utf-8');
        $this->assertStringContainsString('BEGIN:VCALENDAR', $response->getContent());
        $this->assertStringContainsString('SUMMARY:', $response->getContent());
        $this->assertStringContainsString('END:VCALENDAR', $response->getContent());
    }

    public function test_pembatalan_tiket_melalui_pautan_selamat(): void
    {
        $event = $this->makeEvent();
        $registration = app(RegistrationService::class)->register($event, $this->registrationPayload());

        $this->delete(route('tiket.cancel.submit', $registration->public_token), [
            'reason' => 'Ada urusan lain.',
        ])->assertRedirect(route('tiket.show', $registration->public_token));

        $this->assertSame(
            RegistrationStatus::Dibatalkan,
            $registration->fresh()->status,
        );
    }
}
