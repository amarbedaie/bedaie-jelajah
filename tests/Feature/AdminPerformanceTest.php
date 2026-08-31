<?php

namespace Tests\Feature;

use App\Models\Feedback;
use App\Models\NotificationTemplate;
use App\Services\AttendanceService;
use App\Services\CertificateService;
use App\Services\RegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Skrin admin dibuka berpuluh kali sehari oleh pasukan BeDaie. Satu
 * pertanyaan per baris kelihatan murah pada data demo dan menjadi
 * beban sebenar pada 25 baris kali beberapa skrin.
 *
 * Ujian ini mengunci bilangan pertanyaan supaya N+1 tidak menyelinap
 * masuk semula.
 */
class AdminPerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
    }

    /** Bina beberapa program berdaftar supaya N+1 benar-benar muncul. */
    private function seedEvents(int $count = 8): void
    {
        for ($i = 0; $i < $count; $i++) {
            $event = $this->makeEvent(['title' => "Program Ujian Prestasi {$i}"]);
            app(RegistrationService::class)->register($event, $this->registrationPayload());
            app(RegistrationService::class)->register($event, $this->registrationPayload());
        }
    }

    private function countQueries(string $url): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get($url)->assertOk();
        $n = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $n;
    }

    /**
     * Ujian N+1 yang sebenar: bilangan pertanyaan tidak boleh naik
     * apabila bilangan baris naik. Ambang mutlak menipu pada data demo
     * yang kecil; kadar pertumbuhan tidak.
     */
    private function assertQueriesDoNotGrow(string $url, callable $seed, string $what): void
    {
        $seed(3);
        $this->actingAs($this->admin());
        $small = $this->countQueries($url);

        $seed(9);
        $large = $this->countQueries($url);

        $growth = $large - $small;

        $this->assertLessThanOrEqual(2, $growth,
            "{$what}: {$small} pertanyaan pada 3 baris, {$large} pada 12 — "
            ."tumbuh {$growth}. Ini N+1.");
    }

    public function test_senarai_program_tidak_tumbuh_dengan_baris(): void
    {
        $this->assertQueriesDoNotGrow(
            route('admin.program'),
            fn ($n) => $this->seedEvents($n),
            'Senarai program',
        );
    }

    public function test_senarai_sijil_tidak_tumbuh_dengan_baris(): void
    {
        $this->assertQueriesDoNotGrow(
            route('admin.sijil'),
            fn ($n) => $this->seedCompletedEvents($n),
            'Senarai sijil',
        );
    }

    public function test_laporan_tidak_tumbuh_dengan_baris(): void
    {
        $this->assertQueriesDoNotGrow(
            route('admin.laporan'),
            fn ($n) => $this->seedCompletedEvents($n),
            'Laporan',
        );
    }

    public function test_kehadiran_tidak_tumbuh_dengan_baris(): void
    {
        $this->assertQueriesDoNotGrow(
            route('admin.kehadiran'),
            fn ($n) => $this->seedEvents($n),
            'Senarai kehadiran',
        );
    }

    /** Program selesai dengan kehadiran, maklum balas dan sijil. */
    private function seedCompletedEvents(int $count): void
    {
        $admin = $this->admin();

        for ($i = 0; $i < $count; $i++) {
            $event = $this->makeEvent(['title' => 'Program Selesai '.uniqid()]);
            $r1 = app(RegistrationService::class)->register($event, $this->registrationPayload());
            $r2 = app(RegistrationService::class)->register($event, $this->registrationPayload());

            $this->movePast($event);

            foreach ([$r1, $r2] as $reg) {
                app(AttendanceService::class)->checkIn($reg->fresh(), $admin);
            }

            Feedback::create([
                'event_id' => $event->id,
                'registration_id' => $r1->id,
                'rating' => 5,
                'is_published' => true,
            ]);

            app(CertificateService::class)->issueForEvent($event->fresh());
        }
    }

    /**
     * Halaman templat notifikasi memaparkan satu komponen Livewire
     * setiap templat. Ia pernah menembak 41 pertanyaan berulang.
     */
    public function test_halaman_templat_tidak_menembak_pertanyaan_per_templat(): void
    {
        $this->actingAs($this->admin());

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get(route('admin.template'))->assertOk();
        $n = count(DB::getQueryLog());
        DB::disableQueryLog();

        $templates = NotificationTemplate::count();

        $this->assertLessThan(10, $n,
            "Halaman templat menggunakan {$n} pertanyaan untuk {$templates} templat — "
            .'render komponen tidak boleh mengambil semula modelnya.');
    }
}
