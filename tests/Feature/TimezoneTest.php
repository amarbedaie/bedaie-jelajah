<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Waktu program disimpan sebagai waktu tempatan Malaysia (20:30 bermaksud
 * 8:30 malam, bukan 8:30 malam UTC). Jika jam aplikasi berjalan pada UTC,
 * setiap perbandingan terhadap now() tersasar lapan jam: program yang
 * sedang berlangsung dikira belum bermula, dan masa check-in yang direkod
 * pada sijil dan tiket salah.
 */
class TimezoneTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
    }

    public function test_jam_aplikasi_mengikut_waktu_malaysia(): void
    {
        $this->assertSame('Asia/Kuala_Lumpur', config('app.timezone'));

        $this->assertSame(
            now('Asia/Kuala_Lumpur')->format('Y-m-d H:i'),
            now()->format('Y-m-d H:i'),
            'now() mesti sepadan dengan jam dinding Malaysia.',
        );
    }

    public function test_program_yang_sedang_berlangsung_dikenali_sebagai_bermula(): void
    {
        $event = $this->makeEvent([
            'starts_at' => now()->subMinutes(30),
            'ends_at' => now()->addMinutes(90),
        ]);

        $this->assertFalse($event->hasEnded(), 'Program yang belum tamat tidak boleh dikira tamat.');
        $this->assertTrue($event->starts_at->isPast(), 'Program yang sedang berlangsung mesti dikira bermula.');
    }

    public function test_program_yang_baru_tamat_dikira_tamat(): void
    {
        $event = $this->makeEvent([
            'starts_at' => now()->subHours(3),
            'ends_at' => now()->subMinutes(10),
        ]);

        $this->assertTrue($event->hasEnded());
    }
}
