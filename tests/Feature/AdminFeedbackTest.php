<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Livewire\Admin\ApplicationWorkflow;
use App\Livewire\Admin\PartnerManager;
use App\Livewire\CheckIn\Scanner;
use App\Services\ApplicationService;
use App\Services\RegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Staf melaporkan sistem "tak selesa". Puncanya bukan reka bentuk:
 * setiap mesej "berjaya" dalam ruang admin tidak pernah dipaparkan,
 * kerana session()->flash() dimakan oleh permintaan XHR Livewire
 * sebelum partial di luar akar komponen sempat memaparkannya.
 */
class AdminFeedbackTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
    }

    public function test_tindakan_livewire_menghantar_maklum_balas_yang_boleh_dilihat(): void
    {
        Livewire::actingAs($this->admin())
            ->test(PartnerManager::class)
            ->set('name', 'Masjid Rakan Ujian')
            ->set('type', 'masjid')
            ->call('save')
            ->assertDispatched('notify');
    }

    public function test_tiada_lagi_flash_sesi_dalam_komponen_livewire(): void
    {
        $found = [];

        foreach (glob(app_path('Livewire/*/*.php')) as $file) {
            // Buang komen dahulu — docblock yang menerangkan pepijat ini
            // bukan penggunaannya.
            $code = preg_replace('#/\*.*?\*/|//[^\n]*#s', '', file_get_contents($file));

            // flash sah apabila komponen mengubah hala selepasnya —
            // ketika itu halaman dimuat penuh dan partial flash berfungsi.
            if (str_contains($code, 'session()->flash(') && ! str_contains($code, '$this->redirect(')) {
                $found[] = basename($file);
            }
        }

        $this->assertSame([], $found,
            'Komponen ini masih guna session()->flash(), yang tidak pernah dipaparkan: '
            .implode(', ', $found));
    }

    /** Kod manual ialah sandaran apabila kamera gagal di pintu masjid. */
    public function test_kod_qr_manual_benar_benar_merekod_kehadiran(): void
    {
        $event = $this->makeEvent();
        $registration = app(RegistrationService::class)->register($event, $this->registrationPayload());

        Livewire::actingAs($this->admin())
            ->test(Scanner::class, ['event' => $event])
            ->set('manualCode', $registration->qrTokens()->first()->token)
            ->call('scanManual')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('attendance_records', [
            'registration_id' => $registration->id,
        ]);
    }

    public function test_kod_manual_kosong_memberitahu_pengguna(): void
    {
        $event = $this->makeEvent();

        Livewire::actingAs($this->admin())
            ->test(Scanner::class, ['event' => $event])
            ->set('manualCode', '')
            ->call('scanManual')
            ->assertDispatched('notify');
    }

    /**
     * Selepas menukar status, halaman mesti dimuat semula ke halaman
     * permohonan — bukan ke /livewire/update, dan bukan kekal memaparkan
     * status lama.
     */
    public function test_tukar_status_mengubah_hala_ke_halaman_permohonan(): void
    {
        $admin = $this->admin();
        $application = app(ApplicationService::class)->submit($this->applicationPayload());

        Livewire::actingAs($admin)
            ->test(ApplicationWorkflow::class, ['application' => $application])
            ->set('status', ApplicationStatus::DalamSemakan->value)
            ->call('save')
            ->assertRedirect(route('admin.permohonan.show', $application));

        $this->assertSame(
            ApplicationStatus::DalamSemakan,
            $application->fresh()->status,
        );
    }
}
