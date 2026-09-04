<?php

namespace Tests\Feature;

use App\Enums\CertificateStatus;
use App\Enums\EventStatus;
use App\Enums\PaymentStatus;
use App\Enums\PricingMode;
use App\Enums\RegistrationStatus;
use App\Jobs\SendNotificationJob;
use App\Livewire\Admin\CategoryManager;
use App\Livewire\Admin\CertificateActions;
use App\Livewire\Admin\EventEditor;
use App\Livewire\Admin\GalleryManager;
use App\Livewire\Admin\PartnerManager;
use App\Livewire\Admin\PaymentReview;
use App\Livewire\Admin\SettingsEditor;
use App\Livewire\Admin\SpeakerManager;
use App\Livewire\Admin\TemplateEditor;
use App\Livewire\Admin\TestimonialManager;
use App\Models\EventGallery;
use App\Models\NotificationTemplate;
use App\Models\Setting;
use App\Models\Speaker;
use App\Models\Testimonial;
use App\Services\AttendanceService;
use App\Services\CertificateService;
use App\Services\RegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
        Queue::fake();
    }

    // ── Program ──────────────────────────────────────────────

    public function test_admin_boleh_menyunting_program(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent(['title' => 'Tajuk Asal']);

        Livewire::actingAs($admin)
            ->test(EventEditor::class, ['event' => $event])
            ->set('title', 'Tajuk Yang Telah Dibetulkan')
            ->set('capacity', '250')
            ->set('venue_name', 'Masjid Baharu Ujian')
            ->call('save')
            ->assertHasNoErrors();

        $event->refresh();

        $this->assertSame('Tajuk Yang Telah Dibetulkan', $event->title);
        $this->assertSame(250, $event->capacity);
        $this->assertSame('Masjid Baharu Ujian', $event->venue->name);
    }

    public function test_admin_boleh_kosongkan_jam_pembelajaran_semasa_sunting_program_diterbitkan(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent(['status' => EventStatus::Diterbitkan, 'learning_hours' => 3]);

        Livewire::actingAs($admin)
            ->test(EventEditor::class, ['event' => $event])
            ->set('learning_hours', '')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('0.00', $event->fresh()->learning_hours);
    }

    public function test_kapasiti_tidak_boleh_kurang_daripada_tempat_diambil(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent(['capacity' => 10]);

        foreach (range(1, 3) as $i) {
            app(RegistrationService::class)->register($event->fresh(), $this->registrationPayload());
        }

        Livewire::actingAs($admin)
            ->test(EventEditor::class, ['event' => $event->fresh()])
            ->set('capacity', '2')
            ->call('save')
            ->assertHasErrors(['capacity']);

        $this->assertSame(10, $event->fresh()->capacity);
    }

    public function test_peserta_dimaklumkan_apabila_tarikh_berubah(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent();
        app(RegistrationService::class)->register($event, $this->registrationPayload());

        Queue::fake();

        Livewire::actingAs($admin)
            ->test(EventEditor::class, ['event' => $event->fresh()])
            ->set('starts_at', now()->addDays(30)->setTime(21, 0)->format('Y-m-d\TH:i'))
            ->call('save')
            ->assertHasNoErrors();

        Queue::assertPushed(SendNotificationJob::class);
    }

    public function test_admin_boleh_menutup_program_dan_melepaskan_sijil(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent();
        $registration = app(RegistrationService::class)->register($event, $this->registrationPayload());

        $event = $this->movePast($event);
        app(AttendanceService::class)->checkIn($registration, $admin);

        Livewire::actingAs($admin)
            ->test(EventEditor::class, ['event' => $event])
            ->call('complete');

        $event->refresh();

        $this->assertSame(EventStatus::Selesai, $event->status);
        $this->assertNotNull($registration->fresh()->certificate);
    }

    // ── Galeri ───────────────────────────────────────────────

    public function test_admin_boleh_memuat_naik_gambar_galeri(): void
    {
        Storage::fake('public');

        $admin = $this->admin();
        $event = $this->makeEvent();

        Livewire::actingAs($admin)
            ->test(GalleryManager::class)
            ->set('eventId', (string) $event->id)
            ->set('caption', 'Suasana program')
            ->set('photos', [UploadedFile::fake()->image('program.jpg', 800, 600)])
            ->call('upload')
            ->assertHasNoErrors();

        $photo = EventGallery::first();

        $this->assertNotNull($photo);
        $this->assertTrue($photo->is_approved, 'Muat naik admin terus diluluskan.');
        $this->assertSame('Suasana program', $photo->caption);
        Storage::disk('public')->assertExists($photo->image_path);
    }

    public function test_admin_boleh_meluluskan_dan_membuang_gambar(): void
    {
        Storage::fake('public');

        $admin = $this->admin();
        $event = $this->makeEvent();

        $photo = EventGallery::create([
            'event_id' => $event->id,
            'image_path' => UploadedFile::fake()->image('a.jpg')->store('galeri', 'public'),
            'is_approved' => false,
        ]);

        Livewire::actingAs($admin)->test(GalleryManager::class)->call('approve', $photo->id);
        $this->assertTrue($photo->fresh()->is_approved);

        Livewire::actingAs($admin)->test(GalleryManager::class)->call('unapprove', $photo->id);
        $this->assertFalse($photo->fresh()->is_approved);

        $path = $photo->image_path;
        Livewire::actingAs($admin)->test(GalleryManager::class)->call('delete', $photo->id);

        $this->assertNull(EventGallery::find($photo->id));
        Storage::disk('public')->assertMissing($path);
    }

    // ── Katalog ──────────────────────────────────────────────

    public function test_admin_boleh_menambah_penceramah(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(SpeakerManager::class)
            ->call('create')
            ->set('name', 'Ustaz Ujian Baharu')
            ->set('title', 'Pendakwah BeDaie')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('speakers', [
            'name' => 'Ustaz Ujian Baharu',
            'slug' => 'ustaz-ujian-baharu',
        ]);
    }

    public function test_penceramah_dengan_program_hanya_dinyahaktifkan(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent();
        $speaker = Speaker::find($event->speaker_id);

        Livewire::actingAs($admin)->test(SpeakerManager::class)->call('delete', $speaker->id);

        $this->assertNotNull(Speaker::find($speaker->id), 'Rekod program lampau mesti kekal utuh.');
        $this->assertFalse($speaker->fresh()->is_active);
    }

    public function test_admin_boleh_menambah_kategori(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(CategoryManager::class)
            ->call('create')
            ->set('name', 'Jelajah Ujian')
            ->set('icon', 'book')
            ->set('description', 'Kategori untuk ujian.')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('event_categories', ['name' => 'Jelajah Ujian', 'is_active' => 1]);
    }

    public function test_kategori_menolak_ikon_yang_tidak_wujud(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CategoryManager::class)
            ->call('create')
            ->set('name', 'Kategori Ikon Salah')
            ->set('icon', 'ikon-tidak-wujud')
            ->call('save')
            ->assertHasErrors(['icon']);
    }

    public function test_admin_boleh_menambah_rakan(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(PartnerManager::class)
            ->call('create')
            ->set('name', 'Yayasan Ujian')
            ->set('type', 'penaja')
            ->set('website_url', 'https://contoh.test')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('partners', ['name' => 'Yayasan Ujian', 'type' => 'penaja']);
    }

    public function test_admin_boleh_menguruskan_testimoni(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(TestimonialManager::class)
            ->call('create')
            ->set('name', 'Hajah Ujian')
            ->set('role_label', 'Ahli Kariah')
            ->set('quote', 'Program ini sangat bermanfaat untuk komuniti kami.')
            ->set('rating', '5')
            ->call('save')
            ->assertHasNoErrors();

        $testimonial = Testimonial::first();
        $this->assertTrue($testimonial->is_approved);

        Livewire::actingAs($admin)
            ->test(TestimonialManager::class)
            ->call('toggleApproved', $testimonial->id);

        $this->assertFalse($testimonial->fresh()->is_approved);
    }

    // ── Bayaran ──────────────────────────────────────────────

    public function test_admin_boleh_mengesahkan_bayaran_manual(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent(['pricing_mode' => PricingMode::Berbayar, 'price' => 50]);

        $registration = app(RegistrationService::class)->register($event, $this->registrationPayload());
        $payment = $registration->payment;

        $this->assertSame(RegistrationStatus::MenungguPengesahan, $registration->status);

        Livewire::actingAs($admin)
            ->test(PaymentReview::class)
            ->call('confirm', $payment->id);

        $this->assertSame(PaymentStatus::Berjaya, $payment->fresh()->status);
        $this->assertSame(RegistrationStatus::Disahkan, $registration->fresh()->status);
        $this->assertSame($admin->id, $payment->fresh()->verified_by);
    }

    public function test_pengesahan_bayaran_adalah_idempotent(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent(['pricing_mode' => PricingMode::Berbayar, 'price' => 50]);
        $payment = app(RegistrationService::class)
            ->register($event, $this->registrationPayload())->payment;

        $component = Livewire::actingAs($admin)->test(PaymentReview::class);
        $component->call('confirm', $payment->id);
        $paidAt = $payment->fresh()->paid_at;

        $component->call('confirm', $payment->id);

        $this->assertEquals($paidAt, $payment->fresh()->paid_at);
    }

    public function test_admin_boleh_mengecualikan_peserta_daripada_bayaran(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent(['pricing_mode' => PricingMode::Berbayar, 'price' => 50]);
        $registration = app(RegistrationService::class)->register($event, $this->registrationPayload());

        Livewire::actingAs($admin)
            ->test(PaymentReview::class)
            ->call('exempt', $registration->payment->id);

        $this->assertSame(PaymentStatus::Dikecualikan, $registration->payment->fresh()->status);
        $this->assertSame(RegistrationStatus::Disahkan, $registration->fresh()->status);
    }

    // ── Sijil ────────────────────────────────────────────────

    public function test_admin_boleh_membetulkan_nama_pada_sijil(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent();
        $registration = app(RegistrationService::class)->register($event, $this->registrationPayload());

        $event = $this->movePast($event);
        app(AttendanceService::class)->checkIn($registration, $admin);
        app(CertificateService::class)->issueForEvent($event->fresh());

        $original = $registration->fresh()->certificate;

        Livewire::actingAs($admin)
            ->test(CertificateActions::class, ['certificate' => $original])
            ->call('startRegenerate')
            ->set('correctedName', 'Nama Yang Betul bin Abdullah')
            ->call('regenerate')
            ->assertHasNoErrors();

        $this->assertSame(CertificateStatus::Digantikan, $original->fresh()->status);
        $this->assertSame('Nama Yang Betul bin Abdullah',
            $registration->fresh()->certificate->recipient_name);
    }

    public function test_admin_boleh_menarik_balik_sijil(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent();
        $registration = app(RegistrationService::class)->register($event, $this->registrationPayload());

        $event = $this->movePast($event);
        app(AttendanceService::class)->checkIn($registration, $admin);
        app(CertificateService::class)->issueForEvent($event->fresh());

        $certificate = $registration->fresh()->certificate;

        Livewire::actingAs($admin)
            ->test(CertificateActions::class, ['certificate' => $certificate])
            ->call('startRevoke')
            ->set('revokeReason', 'Nama tidak sepadan dengan kad pengenalan.')
            ->call('revoke')
            ->assertHasNoErrors();

        $this->assertSame(CertificateStatus::Dibatalkan, $certificate->fresh()->status);
        $this->get(route('sijil.muat-turun', $certificate->fresh()->public_id))->assertStatus(410);
    }

    public function test_sebab_pembatalan_sijil_diwajibkan(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent();
        $registration = app(RegistrationService::class)->register($event, $this->registrationPayload());

        $event = $this->movePast($event);
        app(AttendanceService::class)->checkIn($registration, $admin);
        app(CertificateService::class)->issueForEvent($event->fresh());

        Livewire::actingAs($admin)
            ->test(CertificateActions::class, ['certificate' => $registration->fresh()->certificate])
            ->call('startRevoke')
            ->call('revoke')
            ->assertHasErrors(['revokeReason']);
    }

    // ── Tetapan & template ───────────────────────────────────

    public function test_admin_boleh_menyunting_tetapan(): void
    {
        $admin = $this->admin();
        Setting::put('ujian.tajuk', 'Nilai Asal', 'kandungan');
        $setting = Setting::where('key', 'ujian.tajuk')->firstOrFail();

        // Kunci bertitik di-bind mengikut ID supaya Livewire tidak
        // menganggapnya sebagai laluan array bersarang.
        Livewire::actingAs($admin)
            ->test(SettingsEditor::class, ['group' => 'kandungan'])
            ->set("values.{$setting->id}", 'Nilai Baharu')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Nilai Baharu', Setting::get('ujian.tajuk'));
    }

    public function test_admin_boleh_menyunting_template_notifikasi(): void
    {
        $admin = $this->admin();
        $template = NotificationTemplate::first();

        Livewire::actingAs($admin)
            ->test(TemplateEditor::class, ['template' => $template])
            ->call('toggle')
            ->set('body', 'Kandungan template yang telah dikemas kini oleh admin.')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Kandungan template yang telah dikemas kini oleh admin.',
            $template->fresh()->body);
    }

    public function test_template_kosong_ditolak(): void
    {
        Livewire::actingAs($this->admin())
            ->test(TemplateEditor::class, ['template' => NotificationTemplate::first()])
            ->call('toggle')
            ->set('body', '')
            ->call('save')
            ->assertHasErrors(['body']);
    }

    // ── Kebenaran ────────────────────────────────────────────

    public function test_penggerak_tidak_boleh_mencapai_halaman_pengurusan(): void
    {
        $penggerak = $this->penggerak();

        foreach (['admin.galeri', 'admin.rakan', 'admin.penceramah', 'admin.kategori',
            'admin.tetapan', 'admin.template', 'admin.pembayaran'] as $route) {
            $this->actingAs($penggerak)->get(route($route))->assertForbidden();
        }
    }

    public function test_semua_halaman_pengurusan_dibuka_untuk_admin(): void
    {
        $admin = $this->admin();

        foreach (['admin.galeri', 'admin.rakan', 'admin.penceramah', 'admin.kategori',
            'admin.tetapan', 'admin.kandungan', 'admin.template', 'admin.pembayaran'] as $route) {
            $this->actingAs($admin)->get(route($route))->assertOk();
        }
    }

    /**
     * Komponen sijil menyalin data paparan sekali semasa mount supaya
     * senarai 25 baris tidak menembak 25 pertanyaan. Ujian ini memastikan
     * salinan itu disegarkan selepas tindakan — jika tidak, baris kekal
     * memaparkan keadaan lama sehingga halaman dimuat semula.
     */
    public function test_paparan_sijil_disegarkan_selepas_ditarik_balik(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent();
        $registration = app(RegistrationService::class)->register($event, $this->registrationPayload());

        $this->movePast($event);
        app(AttendanceService::class)->checkIn($registration->fresh(), $admin);

        $certificate = app(CertificateService::class)->issueForRegistration($registration->fresh());
        $this->assertNotNull($certificate);

        $component = Livewire::actingAs($admin)
            ->test(CertificateActions::class, ['certificate' => $certificate])
            ->assertSet('isValid', true);

        $component->call('startRevoke')
            ->set('revokeReason', 'Nama peserta tidak sepadan dengan rekod.')
            ->call('revoke')
            ->assertHasNoErrors();

        $component->assertSet('isValid', false)
            ->assertSet('downloadUrl', '');

        $this->assertSame(CertificateStatus::Dibatalkan, $certificate->fresh()->status);
    }
}
