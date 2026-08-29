<?php

namespace Tests\Feature;

use App\Enums\OutreachSource;
use App\Enums\OutreachStage;
use App\Livewire\Admin\OutreachBoard;
use App\Livewire\Admin\OutreachDetail;
use App\Models\EventCategory;
use App\Models\OutreachTarget;
use App\Models\Partner;
use App\Models\State;
use App\Services\OutreachService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class OutreachTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
        Queue::fake();
    }

    private function makeTarget(array $attributes = []): OutreachTarget
    {
        return app(OutreachService::class)->create(array_merge([
            'name' => 'Masjid Ujian '.\Illuminate\Support\Str::random(4),
            'type' => \App\Enums\OutreachTargetType::Masjid,
            'state_id' => State::where('code', 'SGR')->value('id'),
            'source' => OutreachSource::StafTerus,
            'priority' => \App\Enums\OutreachPriority::Sederhana,
        ], $attributes), $this->admin());
    }

    // ── Asas ─────────────────────────────────────────────────

    public function test_staf_boleh_menambah_sasaran_melalui_papan(): void
    {
        $admin = $this->admin();
        $state = State::where('code', 'KEL')->firstOrFail();

        Livewire::actingAs($admin)
            ->test(OutreachBoard::class)
            ->call('create')
            ->set('name', 'Masjid Jamek Kampung Baru')
            ->set('type', 'masjid')
            ->set('state_id', (string) $state->id)
            ->set('form_source', 'staf_terus')
            ->set('assigned_to', (string) $admin->id)
            ->call('save')
            ->assertHasNoErrors();

        $target = OutreachTarget::first();

        $this->assertNotNull($target);
        $this->assertSame('Masjid Jamek Kampung Baru', $target->name);
        $this->assertSame(OutreachStage::Sasaran, $target->stage);
        $this->assertStringStartsWith('BDJ-S', $target->reference_no);
        $this->assertSame($admin->id, $target->assigned_to);
    }

    public function test_sasaran_dengan_kontak_terus_masuk_peringkat_kontak_dijumpai(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(OutreachBoard::class)
            ->call('create')
            ->set('name', 'Surau Ujian Berkontak')
            ->set('state_id', (string) State::where('code', 'SGR')->value('id'))
            ->set('contact_name', 'Ustaz Nazir')
            ->set('contact_phone', '012-999 8877')
            ->call('save')
            ->assertHasNoErrors();

        $target = OutreachTarget::first();

        $this->assertSame(OutreachStage::KontakDijumpai, $target->stage);
        $this->assertSame('60129998877', $target->contact_phone);
        $this->assertNotNull($target->contact_found_at);
    }

    // ── Sumber & rakan ───────────────────────────────────────

    public function test_sumber_rakan_memerlukan_rakan_dipilih(): void
    {
        Livewire::actingAs($this->admin())
            ->test(OutreachBoard::class)
            ->call('create')
            ->set('name', 'Masjid Tanpa Rakan')
            ->set('state_id', (string) State::where('code', 'SGR')->value('id'))
            ->set('form_source', 'rakan')
            ->call('save')
            ->assertHasErrors(['partner_id']);

        $this->assertSame(0, OutreachTarget::count());
    }

    public function test_sumber_rujukan_memerlukan_nama_perujuk(): void
    {
        Livewire::actingAs($this->admin())
            ->test(OutreachBoard::class)
            ->call('create')
            ->set('name', 'Masjid Rujukan')
            ->set('state_id', (string) State::where('code', 'SGR')->value('id'))
            ->set('form_source', 'rujukan')
            ->call('save')
            ->assertHasErrors(['referrer_name']);
    }

    public function test_label_sumber_menunjukkan_nama_rakan(): void
    {
        $partner = Partner::create([
            'name' => 'Yayasan Ujian',
            'slug' => 'yayasan-ujian',
            'type' => 'rakan',
            'is_active' => true,
        ]);

        $target = $this->makeTarget([
            'source' => OutreachSource::Rakan,
            'partner_id' => $partner->id,
        ]);

        $this->assertSame('Rakan: Yayasan Ujian', $target->sourceLabel());
    }

    public function test_prestasi_rakan_mengira_kadar_berjaya(): void
    {
        $partner = Partner::create([
            'name' => 'Rakan Prestasi',
            'slug' => 'rakan-prestasi',
            'type' => 'penaja',
            'is_active' => true,
        ]);

        $service = app(OutreachService::class);
        $admin = $this->admin();

        // Tiga dibawa, satu berjaya.
        $a = $this->makeTarget(['source' => OutreachSource::Rakan, 'partner_id' => $partner->id]);
        $this->makeTarget(['source' => OutreachSource::Rakan, 'partner_id' => $partner->id]);
        $c = $this->makeTarget(['source' => OutreachSource::Rakan, 'partner_id' => $partner->id]);

        $service->moveStage($a, OutreachStage::Berjaya, null, $admin);
        $service->close($c, 'Tidak berminat.', $admin);

        $row = $service->partnerPerformance()->first();

        $this->assertSame($partner->id, $row->partner_id);
        $this->assertSame(3, (int) $row->jumlah);
        $this->assertSame(1, (int) $row->berjaya);
        $this->assertSame(1, (int) $row->aktif, 'Satu masih terbuka.');
    }

    // ── Peringkat ────────────────────────────────────────────

    public function test_peringkat_direkod_pada_garis_masa(): void
    {
        $admin = $this->admin();
        $target = $this->makeTarget();

        app(OutreachService::class)->moveStage(
            $target, OutreachStage::Dihubungi, 'Dihubungi selepas Jumaat.', $admin,
        );

        $this->assertSame(OutreachStage::Dihubungi, $target->fresh()->stage);
        $this->assertDatabaseHas('outreach_activities', [
            'outreach_target_id' => $target->id,
            'type' => 'peringkat',
            'from_stage' => OutreachStage::Sasaran->value,
            'to_stage' => OutreachStage::Dihubungi->value,
        ]);
    }

    public function test_butang_maju_menolak_satu_peringkat(): void
    {
        $admin = $this->admin();
        $target = $this->makeTarget();

        Livewire::actingAs($admin)
            ->test(OutreachBoard::class)
            ->call('advance', $target->id);

        $this->assertSame(OutreachStage::CariKontak, $target->fresh()->stage);
    }

    public function test_staf_boleh_membuang_sasaran_daripada_papan(): void
    {
        $admin = $this->admin();
        $target = $this->makeTarget();

        Livewire::actingAs($admin)
            ->test(OutreachDetail::class, ['target' => $target])
            ->call('delete')
            ->assertRedirect(route('admin.sasaran'));

        $this->assertSoftDeleted($target);
    }

    public function test_menutup_sasaran_memerlukan_sebab(): void
    {
        $admin = $this->admin();
        $target = $this->makeTarget();

        Livewire::actingAs($admin)
            ->test(OutreachDetail::class, ['target' => $target])
            ->set('stage', OutreachStage::TidakBerminat->value)
            ->set('stageNote', '')
            ->call('changeStage')
            ->assertHasErrors(['stageNote']);

        $this->assertSame(OutreachStage::Sasaran, $target->fresh()->stage);
    }

    public function test_sasaran_ditutup_menyimpan_sebab(): void
    {
        $admin = $this->admin();
        $target = $this->makeTarget();

        app(OutreachService::class)->close($target, 'Dewan sedang diubah suai.', $admin);

        $target->refresh();

        $this->assertSame(OutreachStage::TidakBerminat, $target->stage);
        $this->assertSame('Dewan sedang diubah suai.', $target->closed_reason);
        $this->assertFalse($target->stage->isOpen());
    }

    // ── Aktiviti & kontak ────────────────────────────────────

    public function test_staf_boleh_merekod_aktiviti(): void
    {
        $admin = $this->admin();
        $target = $this->makeTarget();

        Livewire::actingAs($admin)
            ->test(OutreachDetail::class, ['target' => $target])
            ->set('activityType', 'panggilan')
            ->set('activityBody', 'Menghubungi nazir masjid untuk memperkenalkan program.')
            ->set('activityOutcome', 'Minta hubungi semula')
            ->call('logActivity')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('outreach_activities', [
            'outreach_target_id' => $target->id,
            'type' => 'panggilan',
            'outcome' => 'Minta hubungi semula',
        ]);
    }

    public function test_merekod_kontak_menolak_peringkat_ke_hadapan(): void
    {
        $admin = $this->admin();
        $target = $this->makeTarget();

        Livewire::actingAs($admin)
            ->test(OutreachDetail::class, ['target' => $target])
            ->set('contact_name', 'Haji Sulaiman')
            ->set('contact_phone', '019-888 7766')
            ->call('saveContact')
            ->assertHasNoErrors();

        $target->refresh();

        $this->assertSame(OutreachStage::KontakDijumpai, $target->stage);
        $this->assertSame('60198887766', $target->contact_phone);
    }

    public function test_merekod_kontak_tidak_mengundurkan_sasaran_yang_maju(): void
    {
        $admin = $this->admin();
        $target = $this->makeTarget();
        app(OutreachService::class)->moveStage($target, OutreachStage::Berbincang, null, $admin);

        app(OutreachService::class)->recordContact($target->fresh(), [
            'contact_phone' => '60123334444',
        ], $admin);

        $this->assertSame(OutreachStage::Berbincang, $target->fresh()->stage,
            'Sasaran yang sudah maju tidak boleh diundurkan.');
    }

    // ── Penukaran kepada permohonan ──────────────────────────

    public function test_sasaran_setuju_boleh_ditukar_kepada_permohonan(): void
    {
        $admin = $this->admin();
        $target = $this->makeTarget([
            'contact_name' => 'Haji Rahman',
            'contact_phone' => '60123456789',
        ]);

        app(OutreachService::class)->moveStage($target, OutreachStage::Setuju, null, $admin);

        $application = app(OutreachService::class)->convertToApplication($target->fresh(), [
            'event_category_id' => EventCategory::value('id'),
            'topic' => 'Program tadabbur untuk ahli kariah masjid ini.',
            'preferred_date_1' => now()->addDays(40)->toDateString(),
        ], $admin);

        $target->refresh();

        $this->assertNotNull($application);
        $this->assertSame($application->id, $target->application_id);
        $this->assertSame(OutreachStage::Dijadualkan, $target->stage);
        $this->assertSame($target->name, $application->venue_name);
        $this->assertSame('60123456789', $application->applicant_phone);

        // Nota permohonan merekod asal-usulnya.
        $this->assertStringContainsString($target->reference_no, $application->notes);
    }

    public function test_penukaran_tidak_berulang(): void
    {
        $admin = $this->admin();
        $target = $this->makeTarget(['contact_phone' => '60123456789']);
        app(OutreachService::class)->moveStage($target, OutreachStage::Setuju, null, $admin);

        $payload = [
            'event_category_id' => EventCategory::value('id'),
            'topic' => 'Program tadabbur untuk ahli kariah masjid ini.',
            'preferred_date_1' => now()->addDays(40)->toDateString(),
        ];

        $first = app(OutreachService::class)->convertToApplication($target->fresh(), $payload, $admin);
        $second = app(OutreachService::class)->convertToApplication($target->fresh(), $payload, $admin);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, \App\Models\Application::count());
    }

    public function test_penukaran_memerlukan_nombor_kontak(): void
    {
        $admin = $this->admin();
        $target = $this->makeTarget();
        app(OutreachService::class)->moveStage($target, OutreachStage::Setuju, null, $admin);

        Livewire::actingAs($admin)
            ->test(OutreachDetail::class, ['target' => $target->fresh()])
            ->call('startConvert')
            ->set('topic', 'Program tadabbur untuk ahli kariah masjid ini.')
            ->set('preferred_date_1', now()->addDays(40)->toDateString())
            ->call('convert')
            ->assertHasErrors(['topic']);

        $this->assertNull($target->fresh()->application_id);
    }

    // ── Susulan & kebenaran ──────────────────────────────────

    public function test_sasaran_tertunggak_dikesan(): void
    {
        $target = $this->makeTarget();
        $target->update(['next_action_at' => now()->subDays(3)]);

        $this->assertTrue($target->fresh()->isOverdue());
        $this->assertSame(1, OutreachTarget::dueForAction()->count());
    }

    public function test_sasaran_ditutup_tidak_dikira_tertunggak(): void
    {
        $admin = $this->admin();
        $target = $this->makeTarget();
        $target->update(['next_action_at' => now()->subDays(3)]);
        app(OutreachService::class)->close($target, 'Tidak berminat.', $admin);

        $this->assertFalse($target->fresh()->isOverdue());
        $this->assertSame(0, OutreachTarget::dueForAction()->count());
    }

    public function test_nombor_kontak_disamarkan_untuk_senarai(): void
    {
        $target = $this->makeTarget(['contact_phone' => '60123456789']);

        $masked = $target->maskedContactPhone();

        $this->assertStringStartsWith('601', $masked);
        $this->assertStringEndsWith('789', $masked);
        $this->assertStringNotContainsString('60123456789', $masked);
    }

    public function test_penggerak_dan_peserta_tidak_boleh_mencapai_papan_sasaran(): void
    {
        $this->actingAs($this->penggerak())->get(route('admin.sasaran'))->assertForbidden();
        $this->actingAs($this->peserta())->get(route('admin.sasaran'))->assertForbidden();
    }

    public function test_tetamu_dihalakan_ke_log_masuk_untuk_papan_sasaran(): void
    {
        $this->get(route('admin.sasaran'))->assertRedirect(route('login'));
    }

    public function test_halaman_papan_dan_butiran_dibuka_untuk_admin(): void
    {
        $admin = $this->admin();
        $target = $this->makeTarget(['name' => 'Masjid Paparan Ujian']);

        $this->actingAs($admin)->get(route('admin.sasaran'))->assertOk();
        $this->actingAs($admin)
            ->get(route('admin.sasaran.show', $target))
            ->assertOk()
            ->assertSee('Masjid Paparan Ujian');
    }

    public function test_corong_mengira_setiap_peringkat(): void
    {
        $admin = $this->admin();
        $this->makeTarget();
        $b = $this->makeTarget();
        app(OutreachService::class)->moveStage($b, OutreachStage::Berbincang, null, $admin);

        $funnel = app(OutreachService::class)->funnel();

        $this->assertSame(1, $funnel[OutreachStage::Sasaran->value]);
        $this->assertSame(1, $funnel[OutreachStage::Berbincang->value]);
        $this->assertSame(0, $funnel[OutreachStage::Berjaya->value]);
    }
}
