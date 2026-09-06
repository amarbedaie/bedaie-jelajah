<?php

namespace Tests\Feature;

use App\Services\AttendanceService;
use App\Services\RegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Senarai peserta ialah senarai pendaftaran, jadi seorang yang datang
 * lima kali muncul lima baris. Halaman ini menyatukannya menjadi seorang
 * manusia — dan kerana ia mendedahkan sejarah peribadi, capaiannya
 * terhad kepada Super Admin.
 */
class ParticipantHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
    }

    /** Seorang peserta yang menyertai tiga program, hadir dua. */
    private function peserta_berulang(): array
    {
        $admin = $this->admin();
        $payload = $this->registrationPayload(['name' => 'Rokiah binti Daud', 'phone' => '60123338888']);
        $regs = [];

        foreach (range(1, 3) as $i) {
            $event = $this->makeEvent(['title' => "Program Berulang {$i}"]);
            $regs[] = app(RegistrationService::class)->register($event, $payload);
        }

        // Hadir pada dua yang pertama.
        foreach (array_slice($regs, 0, 2) as $r) {
            $this->movePast($r->event);
            app(AttendanceService::class)->checkIn($r->fresh(), $admin);
        }

        return [$admin, $regs[0]->fresh()];
    }

    public function test_menyatukan_sejarah_seorang_peserta(): void
    {
        [$admin, $registration] = $this->peserta_berulang();

        $this->actingAs($admin)
            ->get(route('admin.peserta.show', $registration->public_id))
            ->assertOk()
            ->assertSee('Rokiah binti Daud')
            ->assertSee('Program Berulang 1')
            ->assertSee('Program Berulang 2')
            ->assertSee('Program Berulang 3')
            ->assertSee('Sejarah Penyertaan');
    }

    public function test_mengira_hadir_dan_tidak_hadir_dengan_betul(): void
    {
        [$admin, $registration] = $this->peserta_berulang();

        $response = $this->actingAs($admin)
            ->get(route('admin.peserta.show', $registration->public_id));

        $stats = $response->viewData('stats');

        $this->assertSame(3, $stats['jumlah'], 'Tiga pendaftaran dengan nombor yang sama.');
        $this->assertSame(2, $stats['hadir']);
        $this->assertSame(0, $stats['terlepas'], 'Program ketiga belum tamat, jadi bukan "tidak hadir".');
    }

    public function test_orang_lain_tidak_bercampur(): void
    {
        [$admin, $registration] = $this->peserta_berulang();

        $lain = $this->makeEvent(['title' => 'Program Orang Lain']);
        app(RegistrationService::class)->register($lain, $this->registrationPayload([
            'name' => 'Zulkifli bin Hassan', 'phone' => '60129990000',
        ]));

        $this->actingAs($admin)
            ->get(route('admin.peserta.show', $registration->public_id))
            ->assertOk()
            ->assertDontSee('Program Orang Lain')
            ->assertDontSee('Zulkifli bin Hassan');
    }

    public function test_hanya_admin_boleh_melihat_sejarah_peribadi(): void
    {
        [, $registration] = $this->peserta_berulang();
        $url = route('admin.peserta.show', $registration->public_id);

        $this->actingAs($this->penggerak())->get($url)->assertForbidden();
        $this->actingAs($this->peserta())->get($url)->assertForbidden();
        $this->post(route('logout'));
        $this->get($url)->assertRedirect(route('login'));
    }

    public function test_senarai_kehadiran_menunjukkan_nama_dan_boleh_ditapis(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent();

        $hadir = app(RegistrationService::class)->register($event,
            $this->registrationPayload(['name' => 'Aminah Sudah Hadir', 'phone' => '60123331111']));
        app(RegistrationService::class)->register($event,
            $this->registrationPayload(['name' => 'Baharin Belum Hadir', 'phone' => '60123332222']));

        app(AttendanceService::class)->checkIn($hadir->fresh(), $admin);

        $this->actingAs($admin);

        // Tanpa tapisan: kedua-duanya muncul.
        $this->get(route('admin.kehadiran.show', $event))
            ->assertOk()
            ->assertSee('Aminah Sudah Hadir')
            ->assertSee('Baharin Belum Hadir');

        // Soalan sebenar staf pada malam program.
        $this->get(route('admin.kehadiran.show', ['event' => $event, 'hadir' => 'belum']))
            ->assertOk()
            ->assertSee('Baharin Belum Hadir')
            ->assertDontSee('Aminah Sudah Hadir');

        $this->get(route('admin.kehadiran.show', ['event' => $event, 'hadir' => 'sudah']))
            ->assertOk()
            ->assertSee('Aminah Sudah Hadir')
            ->assertDontSee('Baharin Belum Hadir');
    }

    public function test_carian_nama_pada_senarai_kehadiran(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent();

        app(RegistrationService::class)->register($event,
            $this->registrationPayload(['name' => 'Siti Nurhaliza', 'phone' => '60123334444']));
        app(RegistrationService::class)->register($event,
            $this->registrationPayload(['name' => 'Ahmad Zaki', 'phone' => '60123335555']));

        $this->actingAs($admin)
            ->get(route('admin.kehadiran.show', ['event' => $event, 'q' => 'Nurhaliza']))
            ->assertOk()
            ->assertSee('Siti Nurhaliza')
            ->assertDontSee('Ahmad Zaki');
    }

    /**
     * Nombor penuh tidak boleh DIBACA pada senarai, tetapi ia dibenarkan
     * di dalam pautan wa.me — menghubungi orang yang belum sampai ialah
     * keseluruhan tujuan skrin ini pada malam program.
     */
    public function test_nombor_penuh_tidak_boleh_dibaca_pada_senarai_kehadiran(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent();

        $r = app(RegistrationService::class)->register($event,
            $this->registrationPayload(['name' => 'Halim bin Osman', 'phone' => '60123336666']));

        $html = $this->actingAs($admin)
            ->get(route('admin.kehadiran.show', $event))
            ->assertOk()
            ->getContent();

        // Buang setiap atribut href, kemudian buang semua tag —
        // yang tinggal ialah apa yang mata staf benar-benar baca.
        $tanpaPautan = preg_replace('/href="[^"]*"/', '', $html);
        $teksSahaja = strip_tags($tanpaPautan);

        $this->assertStringNotContainsString('60123336666', $teksSahaja,
            'Nombor penuh tidak boleh dibaca pada senarai kehadiran.');

        $this->assertStringContainsString($r->maskedPhone(), $teksSahaja,
            'Nombor yang disamarkan sepatutnya dipaparkan.');

        $this->assertStringContainsString('wa.me/60123336666', $html,
            'Butang hubungi mesti berfungsi untuk peserta yang belum sampai.');
    }
}
