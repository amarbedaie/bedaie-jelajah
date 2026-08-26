<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\OutreachStage;
use App\Enums\UserRole;
use App\Models\EventCategory;
use App\Models\State;
use App\Services\ApplicationService;
use App\Services\OutreachService;
use App\Services\RegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackendAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
    }

    /** Pautan dalam setiap notifikasi pemohon mesti benar-benar terbuka. */
    public function test_pautan_permohonan_dalam_notifikasi_boleh_dibuka(): void
    {
        $admin = $this->admin();
        $application = app(ApplicationService::class)->submit($this->applicationPayload());

        app(ApplicationService::class)->changeStatus(
            $application, ApplicationStatus::Diterima, null, null, $admin,
        );

        $application = $application->fresh();
        $url = route('penggerak.permohonan.show', $application->public_id);

        $this->actingAs($application->user)->get($url)->assertOk();
    }

    /** Nombor rujukan pangkalan data tidak boleh muncul pada URL. */
    public function test_permohonan_dirujuk_melalui_public_id(): void
    {
        $application = app(ApplicationService::class)->submit($this->applicationPayload());

        $this->assertSame('public_id', $application->getRouteKeyName());
        $this->assertStringNotContainsString(
            "/{$application->id}",
            route('penggerak.permohonan.show', $application),
        );
    }

    /** hasAttended() tidak boleh menembak pertanyaan baharu bila relasi sudah dimuatkan. */
    public function test_has_attended_menggunakan_relasi_yang_dimuatkan(): void
    {
        $event = $this->makeEvent();
        $registration = app(RegistrationService::class)
            ->register($event, $this->registrationPayload());

        $loaded = $registration->fresh()->load('attendance');

        \DB::enableQueryLog();
        $loaded->hasAttended();
        $queries = \DB::getQueryLog();
        \DB::disableQueryLog();

        $this->assertCount(0, $queries,
            'hasAttended() mesti membaca relasi yang telah dimuatkan, bukan menembak pertanyaan baharu.');
    }

    /** Akaun yang dinyahaktifkan mesti benar-benar terkunci. */
    public function test_akaun_dinyahaktifkan_tidak_boleh_log_masuk(): void
    {
        $user = $this->peserta();
        $user->forceFill(['is_active' => false])->save();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors();

        $this->assertGuest();
    }

    /** Borang awam tidak boleh menaikkan pangkat akaun orang lain. */
    public function test_borang_awam_tidak_menaikkan_peserta_menjadi_penggerak(): void
    {
        $mangsa = $this->peserta(['email' => 'mangsa@contoh.com', 'phone' => '60191112222']);

        app(ApplicationService::class)->submit(array_merge($this->applicationPayload(), [
            'applicant_email' => 'mangsa@contoh.com',
            'applicant_phone' => '0191112222',
        ]));

        $this->assertSame(UserRole::Peserta, $mangsa->fresh()->role,
            'Permohonan awam tidak boleh menukar peranan akaun sedia ada.');
    }

    /** Akaun yang telah dipadam admin tidak boleh dihidupkan semula dari borang awam. */
    public function test_borang_awam_tidak_menghidupkan_semula_akaun_dipadam(): void
    {
        $dipadam = $this->penggerak(['email' => 'dipadam@contoh.com', 'phone' => '60193334455']);
        $dipadam->delete();

        app(ApplicationService::class)->submit(array_merge($this->applicationPayload(), [
            'applicant_email' => 'dipadam@contoh.com',
            'applicant_phone' => '0193334455',
        ]));

        $this->assertSoftDeleted('users', ['id' => $dipadam->id]);
    }

    /** Pautan log masuk admin hanya untuk Penggerak, bukan admin lain. */
    public function test_admin_tidak_boleh_mencipta_pautan_masuk_untuk_admin_lain(): void
    {
        $admin = $this->admin();
        $adminLain = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.penggerak.pautan-masuk', $adminLain))
            ->assertNotFound();

        $this->assertDatabaseMissing('login_links', ['user_id' => $adminLain->id]);
    }

    /** Peserta yang memohon sambil log masuk mesti dapat membuka permohonannya. */
    public function test_peserta_yang_log_masuk_boleh_membuka_permohonan_sendiri(): void
    {
        $peserta = $this->peserta();

        $application = app(ApplicationService::class)
            ->submit($this->applicationPayload(), $peserta);

        $this->assertSame(UserRole::Penggerak, $peserta->fresh()->role);

        $this->actingAs($peserta->fresh())
            ->get(route('penggerak.permohonan.show', $application))
            ->assertOk();
    }

    /** Sasaran jelajah tanpa rujukan tidak boleh menjadikan admin sebagai Penggerak. */
    public function test_penukaran_sasaran_tidak_menjadikan_admin_penggerak(): void
    {
        $admin = $this->admin();

        $target = app(OutreachService::class)->create([
            'name' => 'Masjid Ujian Sasaran',
            'state_id' => State::where('code', 'SGR')->value('id'),
            'contact_name' => 'Ustaz Kontak',
            'contact_phone' => '60195556677',
            'source' => 'staf_terus',
            'stage' => OutreachStage::Setuju,
        ], $admin);

        $application = app(OutreachService::class)->convertToApplication($target, [
            'event_category_id' => EventCategory::first()->id,
            'topic' => 'Kuliah Maghrib',
            'preferred_date_1' => now()->addMonth()->toDateString(),
        ], $admin);

        $this->assertNotSame($admin->id, $application->user_id,
            'Admin yang menekan Tukar tidak boleh menjadi Penggerak program itu.');
    }
}
