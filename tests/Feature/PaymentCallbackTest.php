<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
use App\Enums\PricingMode;
use App\Models\Payment;
use App\Services\RegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Callback pembayaran ialah satu-satunya endpoint yang boleh menukar wang
 * menjadi tempat yang disahkan tanpa manusia menekan apa-apa, jadi setiap
 * jalan masuknya diuji.
 */
class PaymentCallbackTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();

        config()->set('jelajah.payments.gateways.bayarcash', [
            'portal_key' => 'ujian-portal',
            'secret_key' => 'rahsia-ujian',
            'api_token' => 'token-ujian',
            'sandbox' => true,
        ]);
    }

    private function payment(float $amount = 50.00): Payment
    {
        $event = $this->makeEvent([
            'pricing_mode' => PricingMode::Berbayar,
            'price' => $amount,
        ]);

        $registration = app(RegistrationService::class)->register($event, $this->registrationPayload());

        return $registration->payment;
    }

    private function signed(array $data): array
    {
        ksort($data);

        $signed = [];
        foreach ($data as $k => $v) {
            $signed[] = $k.'='.(string) $v;
        }

        $data['checksum'] = hash_hmac('sha256', implode('|', $signed), 'rahsia-ujian');

        return $data;
    }

    public function test_callback_sah_mengesahkan_pembayaran(): void
    {
        $payment = $this->payment(50.00);

        $this->post(route('bayaran.callback', ['gateway' => 'bayarcash']), $this->signed([
            'order_number' => $payment->registration->reference_no,
            'amount' => '50.00',
            'status' => '3',
        ]))->assertOk();

        $this->assertSame(PaymentStatus::Berjaya, $payment->fresh()->status);
    }

    public function test_callback_tanpa_rujukan_tidak_menyentuh_pembayaran_lain(): void
    {
        $payment = $this->payment(50.00);

        $this->post(route('bayaran.callback', ['gateway' => 'bayarcash']), $this->signed([
            'amount' => '50.00',
            'status' => '3',
        ]))->assertStatus(422);

        $this->assertNotSame(PaymentStatus::Berjaya, $payment->fresh()->status,
            'Callback tanpa order_number tidak boleh mengesahkan pembayaran rawak.');
    }

    public function test_callback_dengan_jumlah_tidak_sepadan_ditolak(): void
    {
        $payment = $this->payment(150.00);

        $this->post(route('bayaran.callback', ['gateway' => 'bayarcash']), $this->signed([
            'order_number' => $payment->registration->reference_no,
            'amount' => '1.00',
            'status' => '3',
        ]))->assertStatus(422);

        $this->assertNotSame(PaymentStatus::Berjaya, $payment->fresh()->status);
    }

    public function test_checksum_menandatangani_nama_kunci(): void
    {
        $payment = $this->payment(50.00);

        // Menukar nama kunci mengekalkan senarai nilai yang sama. Jika hanya
        // nilai ditandatangani, tandatangan ini masih lulus.
        $asal = [
            'order_number' => $payment->registration->reference_no,
            'amount' => '50.00',
            'status' => '3',
        ];

        $ditulisSemula = $asal;
        unset($ditulisSemula['order_number']);
        $ditulisSemula['aorder_number'] = $payment->registration->reference_no;

        $palsu = $ditulisSemula;
        ksort($asal);
        $palsu['checksum'] = hash_hmac('sha256', implode('|', array_map('strval', $asal)), 'rahsia-ujian');

        $this->post(route('bayaran.callback', ['gateway' => 'bayarcash']), $palsu)
            ->assertStatus(400);

        $this->assertNotSame(PaymentStatus::Berjaya, $payment->fresh()->status);
    }

    public function test_checksum_salah_ditolak(): void
    {
        $payment = $this->payment(50.00);

        $this->post(route('bayaran.callback', ['gateway' => 'bayarcash']), [
            'order_number' => $payment->registration->reference_no,
            'amount' => '50.00',
            'status' => '3',
            'checksum' => str_repeat('a', 64),
        ])->assertStatus(400);

        $this->assertNotSame(PaymentStatus::Berjaya, $payment->fresh()->status);
    }
}
