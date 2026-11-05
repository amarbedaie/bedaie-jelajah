<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\CertificateType;
use App\Livewire\Admin\BlueprintManager;
use App\Livewire\Admin\CertificateTemplateManager;
use App\Livewire\Admin\EventChecklist;
use App\Models\Blueprint;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\EventChecklistItem;
use App\Services\CertificateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class CertificateDesignAndBlueprintTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
    }

    public function test_status_ringkas_tetapi_status_lama_masih_dipaparkan(): void
    {
        $choices = ApplicationStatus::selectable();

        $this->assertNotContains(ApplicationStatus::Draf, $choices);
        $this->assertNotContains(ApplicationStatus::DalamSemakan, $choices);
        $this->assertContains(ApplicationStatus::ProgramDisahkan, $choices);

        // Rekod berstatus lama tetap ada dalam senarai supaya tidak hilang.
        $this->assertSame(ApplicationStatus::PerluMaklumat,
            ApplicationStatus::selectable(ApplicationStatus::PerluMaklumat)[0]);
        $this->assertCount(11, ApplicationStatus::cases());
    }

    public function test_pratonton_sijil_a4_setiap_gaya_dan_orientasi_satu_muka_surat(): void
    {
        $template = CertificateTemplate::where('type', CertificateType::PenghargaanRakan->value)->first();
        $admin = $this->admin();

        foreach (['klasik', 'moden', 'islamik', 'minimal'] as $design) {
            foreach (['landscape', 'portrait'] as $orientation) {
                $response = $this->actingAs($admin)->get(route('admin.sijil.pratonton',
                    ['template' => $template, 'design' => $design, 'orientation' => $orientation]));

                $response->assertOk();
                $pdf = $response->getContent() ?: $response->streamedContent();
                $this->assertStringStartsWith('%PDF', $pdf);
                $this->assertSame(1, preg_match_all('/\/Type\s*\/Page[^s]/', $pdf), "$design/$orientation");
            }
        }

        $this->actingAs($admin)->get(route('admin.sijil.templat'))->assertOk()->assertSee('Templat &amp; Reka Bentuk Sijil', false);
    }

    public function test_gaya_program_mengatasi_kategori_dan_templat(): void
    {
        $event = $this->makeEvent();
        $template = CertificateTemplate::resolveFor(CertificateType::Penyertaan);

        $this->assertSame(['klasik', 'landscape'],
            [CertificateTemplate::appearance($template, $event)[0]->value, CertificateTemplate::appearance($template, $event)[1]]);

        $event->category->update(['certificate_design' => 'islamik', 'certificate_orientation' => 'portrait']);
        $event->refresh();
        $this->assertSame('islamik', CertificateTemplate::appearance($template, $event)[0]->value);
        $this->assertSame('portrait', CertificateTemplate::appearance($template, $event)[1]);

        $event->update(['certificate_design' => 'moden']);
        $this->assertSame('moden', CertificateTemplate::appearance($template, $event->fresh())[0]->value);
    }

    public function test_pdf_dijana_semula_apabila_reka_bentuk_berubah(): void
    {
        Storage::fake('public');
        $event = $this->makeEvent();
        $service = app(CertificateService::class);
        $certificate = $service->issuePartnerCertificate($event, 'Masjid Ujian');

        $first = $service->pdfPath($certificate);
        $this->assertSame($first, $service->pdfPath($certificate->fresh()));

        $event->update(['certificate_design' => 'minimal']);
        $second = $service->pdfPath(Certificate::find($certificate->id));

        $this->assertNotSame($first, $second);
        Storage::disk('public')->assertExists($second);
        Storage::disk('public')->assertMissing($first);
    }

    public function test_admin_boleh_ubah_templat_sijil(): void
    {
        $template = CertificateTemplate::first();

        Livewire::actingAs($this->admin())->test(CertificateTemplateManager::class)
            ->call('edit', $template->id)
            ->set('design', 'islamik')
            ->set('orientation', 'portrait')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('islamik', $template->fresh()->design);
        $this->assertSame('portrait', $template->fresh()->orientation);
    }

    public function test_tiga_blueprint_permulaan_wujud(): void
    {
        $this->assertEqualsCanonicalizing(['masjid', 'sekolah', 'institusi'],
            Blueprint::pluck('venue_type')->all());

        foreach (Blueprint::with('items')->get() as $bp) {
            foreach (['sebelum', 'semasa', 'selepas'] as $phase) {
                $this->assertTrue($bp->items->where('phase', $phase)->isNotEmpty(), "{$bp->slug}/{$phase}");
            }
        }

        $this->actingAs($this->admin())->get(route('admin.blueprint'))->assertOk()->assertSee('Blueprint Masjid');
    }

    public function test_blueprint_dijana_ke_program_dan_boleh_ditanda(): void
    {
        $event = $this->makeEvent();
        $admin = $this->admin();
        $blueprint = Blueprint::where('slug', 'masjid')->first();

        $component = Livewire::actingAs($admin)->test(EventChecklist::class, ['event' => $event])
            ->set('blueprintId', (string) $blueprint->id)
            ->call('applyBlueprint');

        $this->assertSame($blueprint->items()->count(), $event->checklistItems()->count());
        $this->assertSame($blueprint->id, $event->fresh()->blueprint_id);

        $item = $event->checklistItems()->first();
        $component->call('toggle', $item->id);
        $this->assertNotNull($item->fresh()->completed_at);
        $this->assertSame($admin->id, $item->fresh()->completed_by);

        // Guna semula tidak menduplikasi; item baharu blueprint ditambah.
        $blueprint->items()->create(['phase' => 'selepas', 'title' => 'Item baharu ujian', 'sort_order' => 99]);
        $component->call('applyBlueprint');
        $this->assertSame($blueprint->items()->count(), $event->checklistItems()->count());
        $this->assertNotNull($item->fresh()->completed_at);

        // Item blueprint tidak boleh dibuang dari program.
        $component->call('removeItem', $item->id);
        $this->assertNotNull(EventChecklistItem::find($item->id));

        $this->actingAs($admin)->get(route('admin.program.show', $event))->assertOk()->assertSee('Senarai Semak SOP');
    }

    public function test_admin_boleh_urus_item_blueprint(): void
    {
        $blueprint = Blueprint::where('slug', 'sekolah')->first();
        $count = $blueprint->items()->count();

        Livewire::actingAs($this->admin())->test(BlueprintManager::class)
            ->call('select', $blueprint->id)
            ->set('itemPhase', 'semasa')
            ->set('itemTitle', 'Bacaan doa pembukaan')
            ->set('itemOffset', '0')
            ->call('addItem')
            ->assertHasNoErrors()
            ->call('duplicate');

        $this->assertSame($count + 1, $blueprint->items()->count());
        $this->assertSame(4, Blueprint::count());
    }
}
