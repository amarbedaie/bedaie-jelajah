<?php

namespace App\Services\Payments;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Registration;
use Illuminate\Support\Str;

class PaymentManager
{
    /** @var array<string, PaymentGateway> */
    private array $gateways = [];

    public function __construct()
    {
        foreach ([new ManualGateway, new BayarcashGateway] as $gateway) {
            $this->gateways[$gateway->key()] = $gateway;
        }
    }

    public function gateway(?string $key = null): PaymentGateway
    {
        $key ??= config('jelajah.payments.default', 'manual');
        $gateway = $this->gateways[$key] ?? null;

        // Jatuh balik ke manual apabila gateway pilihan belum dikonfigurasi.
        if (! $gateway || ! $gateway->isConfigured()) {
            return $this->gateways['manual'];
        }

        return $gateway;
    }

    /** @return array<string, PaymentGateway> */
    public function available(): array
    {
        return array_filter($this->gateways, fn (PaymentGateway $g) => $g->isConfigured());
    }

    public function createFor(Registration $registration, float $amount): Payment
    {
        return Payment::create([
            'public_id' => (string) Str::uuid(),
            'registration_id' => $registration->id,
            'event_id' => $registration->event_id,
            'gateway' => $this->gateway()->key(),
            'amount' => $amount,
            'currency' => config('jelajah.payments.currency', 'MYR'),
            'status' => $amount > 0 ? PaymentStatus::BelumBayar : PaymentStatus::Dikecualikan,
        ]);
    }
}
