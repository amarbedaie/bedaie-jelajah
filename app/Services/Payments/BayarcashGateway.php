<?php

namespace App\Services\Payments;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * FPX / DuitNow melalui BayarCash.
 *
 * Memerlukan BAYARCASH_API_TOKEN, BAYARCASH_PORTAL_KEY dan BAYARCASH_SECRET_KEY.
 * Tanpa kredensial ini, isConfigured() memulangkan false dan sistem
 * kekal menggunakan gateway manual.
 */
class BayarcashGateway implements PaymentGateway
{
    public function key(): string
    {
        return 'bayarcash';
    }

    public function label(): string
    {
        return config('jelajah.payments.gateways.bayarcash.label', 'FPX / DuitNow');
    }

    public function isConfigured(): bool
    {
        $config = config('jelajah.payments.gateways.bayarcash');

        return ! empty($config['api_token']) && ! empty($config['portal_key']) && ! empty($config['secret_key']);
    }

    public function initiate(Payment $payment): PaymentIntent
    {
        if (! $this->isConfigured()) {
            throw new \RuntimeException('BayarCash belum dikonfigurasi. Sila tetapkan BAYARCASH_* dalam .env.');
        }

        $config = config('jelajah.payments.gateways.bayarcash');
        $registration = $payment->registration;

        $payload = [
            'portal_key' => $config['portal_key'],
            'order_number' => $registration->reference_no,
            'amount' => number_format((float) $payment->amount, 2, '.', ''),
            'payer_name' => $registration->name,
            'payer_email' => $registration->email,
            'payer_telephone_number' => $registration->phone,
            'return_url' => route('bayaran.callback', ['gateway' => $this->key()]),
        ];

        $payload['checksum'] = $this->checksum($payload, $config['secret_key']);

        $endpoint = $config['sandbox']
            ? 'https://console.bayarcash-sandbox.com/api/v3/payment-intents'
            : 'https://console.bayar.cash/api/v3/payment-intents';

        $response = Http::withToken($config['api_token'])
            ->acceptJson()
            ->timeout(30)
            ->post($endpoint, $payload);

        if (! $response->successful()) {
            throw new \RuntimeException('BayarCash gagal mencipta payment intent: '.$response->body());
        }

        $payment->update([
            'status' => PaymentStatus::MenungguPengesahan,
            'gateway_reference' => $response->json('id'),
            'gateway_payload' => $response->json(),
        ]);

        return new PaymentIntent(
            redirectUrl: $response->json('url'),
            reference: $response->json('id'),
        );
    }

    public function handleCallback(Request $request): PaymentCallbackResult
    {
        $config = config('jelajah.payments.gateways.bayarcash');
        $data = $request->all();

        $received = $data['checksum'] ?? null;
        unset($data['checksum']);

        if (! $received || ! hash_equals($this->checksum($data, $config['secret_key']), $received)) {
            return new PaymentCallbackResult(
                verified: false,
                message: 'Checksum tidak sah.',
            );
        }

        // status 3 = Berjaya mengikut BayarCash
        $status = (string) ($data['status'] ?? '');

        return new PaymentCallbackResult(
            verified: true,
            reference: $data['order_number'] ?? null,
            status: $status === '3' ? PaymentStatus::Berjaya : PaymentStatus::Gagal,
            payload: $data,
        );
    }

    private function checksum(array $payload, string $secret): string
    {
        ksort($payload);

        return hash_hmac('sha256', implode('|', array_map('strval', $payload)), $secret);
    }
}
