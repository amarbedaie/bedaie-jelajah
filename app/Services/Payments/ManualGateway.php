<?php

namespace App\Services\Payments;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Http\Request;

/**
 * Pindahan bank manual — peserta memuat naik resit, admin mengesahkan.
 * Tiada kredensial pihak ketiga diperlukan, jadi ia sentiasa tersedia.
 */
class ManualGateway implements PaymentGateway
{
    public function key(): string
    {
        return 'manual';
    }

    public function label(): string
    {
        return config('jelajah.payments.gateways.manual.label', 'Pindahan Bank / Manual');
    }

    public function isConfigured(): bool
    {
        return true;
    }

    public function initiate(Payment $payment): PaymentIntent
    {
        $bank = config('jelajah.payments.gateways.manual');

        $payment->update(['status' => PaymentStatus::MenungguPengesahan]);

        return new PaymentIntent(
            redirectUrl: null,
            instructions: [
                'Bank' => $bank['bank_name'] ?? '—',
                'No. Akaun' => $bank['account_no'] ?? '—',
                'Nama Akaun' => $bank['account_name'] ?? '—',
                'Jumlah' => $payment->amountLabel(),
                'Rujukan' => $payment->registration->reference_no,
            ],
            reference: $payment->registration->reference_no,
        );
    }

    public function handleCallback(Request $request): PaymentCallbackResult
    {
        return new PaymentCallbackResult(
            verified: false,
            message: 'Gateway manual tidak menerima callback automatik. Pengesahan dibuat oleh admin.',
        );
    }
}
