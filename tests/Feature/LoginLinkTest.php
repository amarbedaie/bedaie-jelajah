<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Models\LoginLink;
use App\Models\NotificationLog;
use App\Models\User;
use App\Services\ApplicationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Akaun Penggerak dicipta automatik daripada permohonan tanpa kata laluan
 * yang diketahui sesiapa. Pautan log masuk WhatsApp ialah jalan masuk
 * mereka — ujian ini menjaga jalan itu.
 */
class LoginLinkTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
        RateLimiter::clear('pautan-masuk:127.0.0.1');
    }

    public function test_pautan_dihantar_melalui_nombor_whatsapp(): void
    {
        $user = $this->penggerak(['phone' => '60123334444']);

        $this->post(route('masuk.pautan.hantar'), ['contact' => '012-333 4444'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('login_links', ['user_id' => $user->id, 'used_at' => null]);
        $this->assertTrue(
            NotificationLog::where('template_key', 'pautan_log_masuk')->exists(),
            'Notifikasi pautan log masuk mesti direkod.',
        );
    }

    public function test_nombor_tidak_dikenali_memberi_jawapan_yang_sama(): void
    {
        // Tidak boleh membocorkan nombor mana yang berdaftar.
        $this->post(route('masuk.pautan.hantar'), ['contact' => '019-999 8888'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(0, LoginLink::count());
    }

    public function test_pautan_melog_masuk_pengguna(): void
    {
        $user = $this->penggerak();
        $link = LoginLink::issueFor($user);

        $this->get($link->url())->assertRedirect(route($user->homeRoute()));

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($link->fresh()->used_at);
    }

    public function test_pautan_hanya_boleh_diguna_sekali(): void
    {
        $user = $this->penggerak();
        $link = LoginLink::issueFor($user);

        $this->get($link->url());
        $this->post(route('logout'));

        $this->get($link->url())->assertRedirect(route('masuk.pautan'));
        $this->assertGuest();
    }

    public function test_pautan_tamat_tempoh_ditolak(): void
    {
        $user = $this->penggerak();
        $link = LoginLink::issueFor($user);

        Carbon::setTestNow(now()->addMinutes(31));

        $this->get($link->url())->assertRedirect(route('masuk.pautan'));
        $this->assertGuest();

        Carbon::setTestNow();
    }

    public function test_permintaan_baharu_membatalkan_pautan_lama(): void
    {
        $user = $this->penggerak();
        $lama = LoginLink::issueFor($user);
        LoginLink::issueFor($user);

        $this->get($lama->url())->assertRedirect(route('masuk.pautan'));
        $this->assertGuest();
    }

    public function test_permintaan_dihadkan_kadarnya(): void
    {
        $this->penggerak(['phone' => '60123334444']);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('masuk.pautan.hantar'), ['contact' => '0123334444']);
        }

        $this->post(route('masuk.pautan.hantar'), ['contact' => '0123334444'])
            ->assertSessionHasErrors('contact');
    }

    public function test_penggerak_baharu_menerima_pautan_apabila_permohonan_diterima(): void
    {
        $admin = $this->admin();
        $application = app(ApplicationService::class)->submit($this->applicationPayload());

        app(ApplicationService::class)->changeStatus(
            $application, ApplicationStatus::Diterima, null, null, $admin,
        );

        $user = $application->fresh()->user;

        $this->assertNotNull($user, 'Akaun Penggerak mesti dicipta.');
        $this->assertDatabaseHas('login_links', ['user_id' => $user->id, 'used_at' => null]);
    }

    public function test_akaun_tanpa_kata_laluan_boleh_menetapkannya_tanpa_kata_laluan_semasa(): void
    {
        $user = $this->penggerak();
        $user->forceFill(['password_set_at' => null])->save();

        $this->actingAs($user)
            ->put(route('kata-laluan.update'), [
                'password' => 'rahsia-baharu-123',
                'password_confirmation' => 'rahsia-baharu-123',
            ])
            ->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('rahsia-baharu-123', $user->password));
        $this->assertNotNull($user->password_set_at);
    }

    public function test_akaun_berkata_laluan_mesti_sahkan_kata_laluan_semasa(): void
    {
        $user = $this->penggerak();
        $user->forceFill(['password_set_at' => now()])->save();

        $this->actingAs($user)
            ->put(route('kata-laluan.update'), [
                'current_password' => 'salah-sama-sekali',
                'password' => 'rahsia-baharu-123',
                'password_confirmation' => 'rahsia-baharu-123',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_admin_boleh_menghantar_pautan_log_masuk(): void
    {
        $admin = $this->admin();
        $penggerak = $this->penggerak();

        $this->actingAs($admin)
            ->post(route('admin.penggerak.pautan-masuk', $penggerak))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('login_links', ['user_id' => $penggerak->id]);
    }

    public function test_penggerak_tidak_boleh_menghantar_pautan_untuk_orang_lain(): void
    {
        $penggerak = $this->penggerak();
        $lain = $this->penggerak();

        $this->actingAs($penggerak)
            ->post(route('admin.penggerak.pautan-masuk', $lain))
            ->assertForbidden();
    }

    public function test_halaman_pautan_dan_profil_dipaparkan(): void
    {
        $this->get(route('masuk.pautan'))->assertOk()->assertSee('WhatsApp');

        $penggerak = $this->penggerak();
        $penggerak->forceFill(['password_set_at' => null])->save();

        $this->actingAs($penggerak)->get(route('penggerak.profil'))
            ->assertOk()->assertSee('Tetapkan Kata Laluan');

        $peserta = $this->peserta();
        $peserta->forceFill(['password_set_at' => now()])->save();

        $this->actingAs($peserta)->get(route('peserta.profil'))
            ->assertOk()->assertSee('Tukar Kata Laluan');
    }

    public function test_penggerak_tanpa_emel_sebenar_boleh_mengisinya(): void
    {
        $user = $this->penggerak();
        $user->forceFill(['email' => 'penggerak.60123334444'.User::PLACEHOLDER_EMAIL_DOMAIN])->save();

        $this->actingAs($user)->put(route('penggerak.profil.update'), [
            'name' => $user->name,
            'phone' => $user->phone,
            'state_id' => $user->state_id,
            'email' => 'penggerak.betul@contoh.com',
        ])->assertSessionHas('success');

        $this->assertSame('penggerak.betul@contoh.com', $user->fresh()->email);
    }

    public function test_emel_sebenar_tidak_boleh_ditukar_melalui_profil(): void
    {
        $user = $this->penggerak();
        $asal = $user->email;

        $this->actingAs($user)->put(route('penggerak.profil.update'), [
            'name' => $user->name,
            'phone' => $user->phone,
            'state_id' => $user->state_id,
            'email' => 'cuba.rampas@contoh.com',
        ]);

        $this->assertSame($asal, $user->fresh()->email);
    }

    public function test_pautan_menggantikan_sesi_pengguna_lain(): void
    {
        // Telefon dikongsi antara ahli jawatankuasa adalah perkara biasa.
        $lama = $this->penggerak();
        $baharu = $this->penggerak();

        $this->actingAs($lama);
        $link = LoginLink::issueFor($baharu);

        $this->get($link->url())->assertRedirect(route($baharu->homeRoute()));
        $this->assertAuthenticatedAs($baharu);
    }

    public function test_akaun_dicipta_automatik_bermula_tanpa_kata_laluan_ditetapkan(): void
    {
        $admin = $this->admin();
        $application = app(ApplicationService::class)->submit($this->applicationPayload());

        app(ApplicationService::class)->changeStatus(
            $application, ApplicationStatus::Diterima, null, null, $admin,
        );

        $this->assertNull($application->fresh()->user->password_set_at);
    }

    public function test_pendaftaran_awam_menandakan_kata_laluan_telah_ditetapkan(): void
    {
        $this->post(route('register'), [
            'name' => 'Peserta Ujian Daftar',
            'email' => 'daftar@contoh.com',
            'phone' => '0198887766',
            'password' => 'rahsia-baharu-123',
            'password_confirmation' => 'rahsia-baharu-123',
            'privacy' => '1',
        ]);

        $user = User::where('email', 'daftar@contoh.com')->first();

        $this->assertNotNull($user);
        $this->assertNotNull($user->password_set_at,
            'Pengguna yang memilih kata laluannya sendiri mesti ditandakan.');
    }
}
