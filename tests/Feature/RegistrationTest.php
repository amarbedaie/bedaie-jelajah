<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
use App\Enums\PricingMode;
use App\Enums\RegistrationStatus;
use App\Livewire\Public\RegistrationForm;
use App\Models\QrToken;
use App\Models\Registration;
use App\Services\RegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
        Notification::fake();
    }

    public function test_peserta_boleh_mendaftar_dan_menerima_qr_unik(): void
    {
        $event = $this->makeEvent();

        $registration = app(RegistrationService::class)
            ->register($event, $this->registrationPayload());

        $this->assertSame(RegistrationStatus::Disahkan, $registration->status);
        $this->assertStringStartsWith('BDJ-R', $registration->reference_no);

        $token = QrToken::where('tokenable_type', Registration::class)
            ->where('tokenable_id', $registration->id)
            ->where('purpose', 'checkin')
            ->first();

        $this->assertNotNull($token, 'Setiap pendaftaran mesti mempunyai QR check-in.');
        $this->assertNotEmpty($token->token);

        // Token tidak boleh mendedahkan ID database.
        $this->assertStringNotContainsString((string) $registration->id, $token->token);
    }

    public function test_kapasiti_dikurangkan_selepas_pendaftaran(): void
    {
        $event = $this->makeEvent(['capacity' => 5]);

        app(RegistrationService::class)->register($event, $this->registrationPayload());
        app(RegistrationService::class)->register($event, $this->registrationPayload());

        $event->refresh();

        $this->assertSame(2, $event->seatsTaken());
        $this->assertSame(3, $event->seatsLeft());
    }

    public function test_ahli_keluarga_dikira_sebagai_tempat_tambahan(): void
    {
        $event = $this->makeEvent(['capacity' => 5]);

        $registration = app(RegistrationService::class)->register($event, $this->registrationPayload([
            'guests' => [
                ['name' => 'Isteri Ujian', 'gender' => 'perempuan', 'age_group' => 'dewasa'],
                ['name' => 'Anak Ujian', 'gender' => 'lelaki', 'age_group' => 'kanak_kanak'],
            ],
        ]));

        $this->assertSame(2, $registration->guests()->count());
        $this->assertSame(3, $registration->seats());
        $this->assertSame(3, $event->fresh()->seatsTaken());
    }

    public function test_senarai_menunggu_berfungsi_apabila_penuh(): void
    {
        $event = $this->makeEvent(['capacity' => 2]);
        $service = app(RegistrationService::class);

        $service->register($event, $this->registrationPayload());
        $service->register($event, $this->registrationPayload());
        $third = $service->register($event->fresh(), $this->registrationPayload());

        $this->assertSame(RegistrationStatus::SenaraiMenunggu, $third->status);
        $this->assertSame(2, $event->fresh()->seatsTaken(), 'Senarai menunggu tidak mengambil tempat.');
    }

    public function test_pendaftaran_berganda_ditolak(): void
    {
        $event = $this->makeEvent();
        $payload = $this->registrationPayload();

        app(RegistrationService::class)->register($event, $payload);

        $this->expectException(ValidationException::class);
        app(RegistrationService::class)->register($event->fresh(), $payload);
    }

    public function test_program_berbayar_mencipta_rekod_pembayaran(): void
    {
        $event = $this->makeEvent([
            'pricing_mode' => PricingMode::Berbayar,
            'price' => 45.00,
        ]);

        $registration = app(RegistrationService::class)->register($event, $this->registrationPayload([
            'guests' => [['name' => 'Isteri Ujian']],
        ]));

        $this->assertSame(RegistrationStatus::MenungguPengesahan, $registration->status);

        $payment = $registration->payment;
        $this->assertNotNull($payment);
        $this->assertSame(PaymentStatus::BelumBayar, $payment->status);
        $this->assertEqualsWithDelta(90.00, (float) $payment->amount, 0.001, 'Harga didarab dengan bilangan tempat.');
    }

    public function test_pembatalan_melepaskan_tempat_dan_menaikkan_senarai_menunggu(): void
    {
        $event = $this->makeEvent(['capacity' => 1]);
        $service = app(RegistrationService::class);

        $first = $service->register($event, $this->registrationPayload());
        $waiting = $service->register($event->fresh(), $this->registrationPayload());

        $this->assertSame(RegistrationStatus::SenaraiMenunggu, $waiting->status);

        $service->cancel($first, 'Ujian pembatalan.');
        $service->promoteFromWaitlist($event->fresh());

        $this->assertSame(RegistrationStatus::Dibatalkan, $first->fresh()->status);
        $this->assertSame(RegistrationStatus::Disahkan, $waiting->fresh()->status);
    }

    public function test_program_jemputan_memerlukan_kod(): void
    {
        $event = $this->makeEvent([
            'pricing_mode' => PricingMode::JemputanSahaja,
            'invite_code' => 'BEDAIE2026',
        ]);

        $this->expectException(ValidationException::class);
        app(RegistrationService::class)->register($event, $this->registrationPayload([
            'invite_code' => 'SALAH',
        ]));
    }

    public function test_borang_livewire_menghantar_pendaftaran(): void
    {
        $event = $this->makeEvent();

        Livewire::test(RegistrationForm::class, ['event' => $event])
            ->set('name', 'Siti Aminah binti Osman')
            ->set('phone', '019-876 5432')
            ->set('email', 'siti@ujian.test')
            ->set('state_id', (string) $event->state_id)
            ->set('privacy', true)
            ->call('submit')
            ->assertHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('registrations', [
            'event_id' => $event->id,
            'name' => 'Siti Aminah binti Osman',
            'phone' => '60198765432',
        ]);
    }

    public function test_borang_livewire_memerlukan_persetujuan_privasi(): void
    {
        $event = $this->makeEvent();

        Livewire::test(RegistrationForm::class, ['event' => $event])
            ->set('name', 'Peserta Tanpa Setuju')
            ->set('phone', '0198888888')
            ->set('state_id', (string) $event->state_id)
            ->set('privacy', false)
            ->call('submit')
            ->assertHasErrors(['privacy']);

        $this->assertDatabaseCount('registrations', 0);
    }

    public function test_pendaftaran_ditutup_selepas_program_tamat(): void
    {
        $event = $this->makeEvent([
            'starts_at' => now()->subDays(3)->setTime(20, 30),
            'ends_at' => now()->subDays(3)->setTime(22, 30),
            'registration_closes_at' => now()->subDays(3)->setTime(18, 0),
        ]);

        $this->assertFalse($event->registrationOpen());
        $this->assertNotNull($event->registrationClosedReason());

        $this->expectException(ValidationException::class);
        app(RegistrationService::class)->register($event, $this->registrationPayload());
    }
}
