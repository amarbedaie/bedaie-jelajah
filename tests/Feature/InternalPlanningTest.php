<?php

namespace Tests\Feature;

use App\Livewire\Admin\InternalPlanning;
use App\Models\Event;
use App\Models\EventFlowStep;
use App\Services\RegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Perancangan dalaman ialah dokumen kerja pasukan BeDaie. Syarat yang
 * paling penting bukan ia berfungsi, tetapi ia TIDAK PERNAH bocor —
 * bukan kepada peserta, bukan kepada orang awam, dan bukan kepada
 * Penggerak yang menjalankan program itu sendiri.
 */
class InternalPlanningTest extends TestCase
{
    use RefreshDatabase;

    /** Rentetan yang mudah dicari dalam mana-mana respons. */
    private const RAHSIA_OBJEKTIF = 'OBJEKTIF-DALAMAN-RAHSIA-XYZ';

    private const RAHSIA_SOP = 'SOP-DALAMAN-RAHSIA-XYZ';

    private const RAHSIA_LANGKAH = 'LANGKAH-DALAMAN-RAHSIA-XYZ';

    private const RAHSIA_NOTA = 'NOTA-DALAMAN-RAHSIA-XYZ';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
    }

    private function eventWithPlanning(): Event
    {
        $event = $this->makeEvent();

        $event->update([
            'objectives' => self::RAHSIA_OBJEKTIF,
            'sop' => self::RAHSIA_SOP,
            'flow_notes' => self::RAHSIA_NOTA,
        ]);

        EventFlowStep::create([
            'event_id' => $event->id,
            'sort_order' => 0,
            'time_label' => '8:30 mlm',
            'activity' => self::RAHSIA_LANGKAH,
            'responsible' => 'Penggerak',
        ]);

        return $event->fresh();
    }

    private function assertTiadaKebocoran(string $body, string $di): void
    {
        foreach ([self::RAHSIA_OBJEKTIF, self::RAHSIA_SOP,
            self::RAHSIA_LANGKAH, self::RAHSIA_NOTA] as $rahsia) {
            $this->assertStringNotContainsString($rahsia, $body,
                "Perancangan dalaman bocor pada {$di}.");
        }
    }

    public function test_admin_boleh_menyimpan_perancangan(): void
    {
        $event = $this->makeEvent();

        Livewire::actingAs($this->admin())
            ->test(InternalPlanning::class, ['event' => $event])
            ->set('objectives', 'Memperkenalkan fiqh solat')
            ->set('sop', 'Kaunter dibuka 30 minit awal')
            ->set('flowNotes', 'Penceramah tiba awal')
            ->set('steps', [
                ['id' => null, 'time_label' => '8:00 mlm', 'activity' => 'Pendaftaran', 'responsible' => 'AJK'],
                ['id' => null, 'time_label' => '8:30 mlm', 'activity' => 'Kuliah', 'responsible' => 'Penceramah'],
            ])
            ->call('save')
            ->assertHasNoErrors();

        $event->refresh();

        $this->assertSame('Memperkenalkan fiqh solat', $event->objectives);
        $this->assertSame('Kaunter dibuka 30 minit awal', $event->sop);
        $this->assertCount(2, $event->flowSteps);
        $this->assertSame('Pendaftaran', $event->flowSteps->first()->activity);
    }

    public function test_baris_kosong_diabaikan(): void
    {
        $event = $this->makeEvent();

        Livewire::actingAs($this->admin())
            ->test(InternalPlanning::class, ['event' => $event])
            ->set('steps', [
                ['id' => null, 'time_label' => '8:00', 'activity' => 'Pendaftaran', 'responsible' => ''],
                ['id' => null, 'time_label' => '', 'activity' => '', 'responsible' => ''],
            ])
            ->call('save');

        $this->assertCount(1, $event->fresh()->flowSteps);
    }

    public function test_penggerak_tidak_boleh_membuka_komponen(): void
    {
        $event = $this->makeEvent();

        Livewire::actingAs($this->penggerak())
            ->test(InternalPlanning::class, ['event' => $event])
            ->assertForbidden();
    }

    public function test_peserta_tidak_boleh_membuka_komponen(): void
    {
        $event = $this->makeEvent();

        Livewire::actingAs($this->peserta())
            ->test(InternalPlanning::class, ['event' => $event])
            ->assertForbidden();
    }

    public function test_halaman_cetak_hanya_untuk_admin(): void
    {
        $event = $this->eventWithPlanning();
        $url = route('admin.program.perancangan', $event);

        $this->actingAs($this->admin())->get($url)->assertOk()
            ->assertSee(self::RAHSIA_OBJEKTIF);

        $this->actingAs($this->penggerak())->get($url)->assertForbidden();
        $this->actingAs($this->peserta())->get($url)->assertForbidden();
        $this->post(route('logout'));
        $this->get($url)->assertRedirect(route('login'));
    }

    /** Ujian sebenar: sapu setiap muka yang orang luar boleh capai. */
    public function test_tidak_bocor_pada_mana_mana_muka_awam(): void
    {
        $event = $this->eventWithPlanning();
        $registration = app(RegistrationService::class)->register($event, $this->registrationPayload());

        $muka = [
            'laman utama' => '/',
            'senarai program' => route('program.index'),
            'halaman program' => $event->publicUrl(),
            'peta negeri' => route('peta.negeri', $event->state->slug),
            'tiket peserta' => route('tiket.show', $registration->public_token),
            'jejak jelajah' => route('jejak'),
        ];

        foreach ($muka as $nama => $url) {
            $this->assertTiadaKebocoran($this->get($url)->getContent(), $nama);
        }
    }

    public function test_tidak_bocor_pada_muka_penggerak(): void
    {
        $event = $this->eventWithPlanning();
        $penggerak = $this->penggerak();
        $event->mobilizers()->attach($penggerak->id, ['role' => 'utama']);

        $this->actingAs($penggerak);

        foreach ([
            'dashboard penggerak' => route('penggerak.dashboard'),
            'program penggerak' => route('penggerak.program.show', $event),
        ] as $nama => $url) {
            $this->assertTiadaKebocoran($this->get($url)->getContent(), $nama);
        }
    }

    public function test_tidak_bocor_pada_poster_atau_kalendar(): void
    {
        $event = $this->eventWithPlanning();

        $this->assertTiadaKebocoran(
            $this->get(route('tiket.kalendar',
                app(RegistrationService::class)->register($event, $this->registrationPayload())->public_token
            ))->getContent(),
            'fail kalendar',
        );
    }
}
