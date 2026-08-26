<?php

namespace Tests\Feature;

use App\Services\AttendanceService;
use App\Services\RegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
        Notification::fake();
    }

    public function test_tetamu_dihalakan_ke_log_masuk(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->get(route('penggerak.dashboard'))->assertRedirect(route('login'));
        $this->get(route('peserta.dashboard'))->assertRedirect(route('login'));
    }

    public function test_peserta_tidak_boleh_masuk_ruang_admin_atau_penggerak(): void
    {
        $peserta = $this->peserta();

        $this->actingAs($peserta)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($peserta)->get(route('penggerak.dashboard'))->assertForbidden();
    }

    public function test_penggerak_tidak_boleh_masuk_ruang_admin(): void
    {
        $this->actingAs($this->penggerak())
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_penggerak_hanya_melihat_program_sendiri(): void
    {
        $milik = $this->makeEvent(['title' => 'Program Milik Penggerak']);
        $orangLain = $this->makeEvent(['title' => 'Program Penggerak Lain']);

        $penggerak = $this->penggerak();
        $milik->mobilizers()->attach($penggerak->id, ['role' => 'utama']);

        $this->actingAs($penggerak)
            ->get(route('penggerak.program.show', $milik))
            ->assertOk()
            ->assertSee('Program Milik Penggerak');

        $this->actingAs($penggerak)
            ->get(route('penggerak.program.show', $orangLain))
            ->assertForbidden();
    }

    public function test_penggerak_hanya_melihat_permohonan_sendiri(): void
    {
        $penggerak = $this->penggerak();
        $lain = $this->penggerak();

        $milik = app(\App\Services\ApplicationService::class)
            ->submit($this->applicationPayload(), $penggerak);
        $bukanMilik = app(\App\Services\ApplicationService::class)
            ->submit($this->applicationPayload(['venue_name' => 'Masjid Lain']), $lain);

        $this->actingAs($penggerak)
            ->get(route('penggerak.permohonan.show', $milik))
            ->assertOk();

        $this->actingAs($penggerak)
            ->get(route('penggerak.permohonan.show', $bukanMilik))
            ->assertForbidden();
    }

    public function test_nota_dalaman_tidak_didedahkan_kepada_penggerak(): void
    {
        $penggerak = $this->penggerak();
        $admin = $this->admin();

        $application = app(\App\Services\ApplicationService::class)
            ->submit($this->applicationPayload(), $penggerak);

        app(\App\Services\ApplicationService::class)->changeStatus(
            $application,
            \App\Enums\ApplicationStatus::DalamSemakan,
            'Permohonan anda sedang disemak.',
            'RAHSIA DALAMAN: pemohon perlu dipantau.',
            $admin,
        );

        $this->actingAs($penggerak)
            ->get(route('penggerak.permohonan.show', $application))
            ->assertOk()
            ->assertSee('Permohonan anda sedang disemak.')
            ->assertDontSee('RAHSIA DALAMAN');
    }

    public function test_nombor_telefon_peserta_disamarkan_untuk_penggerak(): void
    {
        $event = $this->makeEvent();
        $penggerak = $this->penggerak();
        $event->mobilizers()->attach($penggerak->id, ['role' => 'utama']);

        $registration = app(RegistrationService::class)->register($event, $this->registrationPayload([
            'name' => 'Peserta Privasi',
            'phone' => '60123334444',
        ]));

        $this->actingAs($penggerak)
            ->get(route('penggerak.peserta', ['program' => $event->short_code]))
            ->assertOk()
            ->assertSee('Peserta Privasi')
            ->assertDontSee('60123334444');
    }

    public function test_eksport_peserta_hanya_untuk_admin(): void
    {
        $event = $this->makeEvent();
        $penggerak = $this->penggerak();
        $event->mobilizers()->attach($penggerak->id, ['role' => 'utama']);

        $this->actingAs($penggerak)
            ->get(route('admin.laporan.eksport', $event))
            ->assertForbidden();

        $this->actingAs($this->admin())
            ->get(route('admin.laporan.eksport', $event))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_tiket_hanya_boleh_dicapai_melalui_token_rahsia(): void
    {
        $event = $this->makeEvent();
        $registration = app(RegistrationService::class)->register($event, $this->registrationPayload());

        $this->get(route('tiket.show', $registration->public_token))->assertOk();
        $this->get('/tiket/'.$registration->id)->assertNotFound();
    }

    public function test_program_belum_diterbitkan_tidak_boleh_dilihat_awam(): void
    {
        $event = $this->makeEvent([
            'status' => \App\Enums\EventStatus::Draf,
            'published_at' => null,
        ]);

        $this->get(route('jelajah.show', [$event->state->slug, $event->slug]))
            ->assertNotFound();
    }

    public function test_penggerak_program_boleh_membuka_pengimbas_check_in(): void
    {
        $event = $this->makeEvent();
        $penggerak = $this->penggerak();
        $event->mobilizers()->attach($penggerak->id, ['role' => 'utama']);

        $this->actingAs($penggerak)
            ->get(route('checkin.scanner', $event))
            ->assertOk();
    }

    public function test_admin_boleh_check_in_mana_mana_program(): void
    {
        $event = $this->makeEvent();
        $registration = app(RegistrationService::class)->register($event, $this->registrationPayload());

        $result = app(AttendanceService::class)->checkIn($registration, $this->admin());

        $this->assertTrue($result->successful());
    }
}
