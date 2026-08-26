<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Services\ApplicationService;
use App\Services\PosterGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PosterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
        Queue::fake();

        if (! app(PosterGenerator::class)->available()) {
            $this->markTestSkipped('Imagick tidak tersedia pada persekitaran ini.');
        }
    }

    public function test_poster_dan_hero_dijana_untuk_program(): void
    {
        Storage::fake('public');

        $event = $this->makeEvent(['title' => 'Jelajah Ilmu: Masjid Ujian Poster']);

        $path = app(PosterGenerator::class)->generate($event);

        $this->assertNotNull($path);

        $event->refresh();

        Storage::disk('public')->assertExists($event->poster_path);
        Storage::disk('public')->assertExists($event->hero_image_path);
        $this->assertNotSame($event->poster_path, $event->hero_image_path);
    }

    public function test_poster_mengikut_nisbah_yang_betul(): void
    {
        Storage::fake('public');

        $event = $this->makeEvent();
        app(PosterGenerator::class)->generate($event);
        $event->refresh();

        $poster = new \Imagick;
        $poster->readImageBlob(Storage::disk('public')->get($event->poster_path));

        $hero = new \Imagick;
        $hero->readImageBlob(Storage::disk('public')->get($event->hero_image_path));

        // Poster 4:5 untuk WhatsApp/cetakan; hero 16:9 untuk kad program.
        $this->assertSame(1080, $poster->getImageWidth());
        $this->assertSame(1350, $poster->getImageHeight());
        $this->assertSame(1080, $hero->getImageWidth());
        $this->assertSame(608, $hero->getImageHeight());

        $poster->clear();
        $hero->clear();
    }

    public function test_tajuk_panjang_tidak_memecahkan_poster(): void
    {
        Storage::fake('public');

        $event = $this->makeEvent([
            'title' => 'Jelajah Ilmu Bersiri Sempena Maulidur Rasul Peringkat Negeri Bersama Ahli Kariah Seluruh Daerah',
            'theme' => 'Menghidupkan sunnah dalam kehidupan seharian keluarga muslim moden yang sibuk',
        ]);

        $this->assertNotNull(app(PosterGenerator::class)->generate($event));

        Storage::disk('public')->assertExists($event->fresh()->poster_path);
    }

    public function test_poster_dijana_automatik_apabila_program_disahkan(): void
    {
        Storage::fake('public');

        $admin = $this->admin();
        $application = app(ApplicationService::class)->submit($this->applicationPayload());

        app(ApplicationService::class)->changeStatus(
            $application, ApplicationStatus::ProgramDisahkan, null, null, $admin,
        );

        $event = $application->fresh()->event;

        $this->assertNotNull($event->poster_path, 'EventSpace mesti menjana poster rasmi.');
        Storage::disk('public')->assertExists($event->poster_path);
    }

    public function test_menjana_semula_menggantikan_poster_sedia_ada(): void
    {
        Storage::fake('public');

        $event = $this->makeEvent(['title' => 'Tajuk Asal Poster']);
        app(PosterGenerator::class)->generate($event);

        $first = Storage::disk('public')->get($event->fresh()->poster_path);

        $event->update(['title' => 'Tajuk Yang Telah Dibetulkan Sepenuhnya']);
        app(PosterGenerator::class)->generate($event->fresh());

        $second = Storage::disk('public')->get($event->fresh()->poster_path);

        $this->assertNotSame($first, $second, 'Poster mesti mencerminkan tajuk terkini.');
    }
}
