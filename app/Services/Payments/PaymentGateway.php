<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Http\Request;

interface PaymentGateway
{
    public function key(): string;

    public function label(): string;

    /** Adakah gateway ini dikonfigurasi sepenuhnya dan boleh digunakan? */
    public function isConfigured(): bool;

    /**
     * Mulakan pembayaran. Pulangkan URL untuk redirect, atau null jika
     * pembayaran diselesaikan di luar talian (contoh: pindahan bank manual).
     */
    public function initiate(Payment $payment): PaymentIntent;

    /** Mengesahkan callback/webhook daripada gateway. */
    public function handleCallback(Request $request): PaymentCallbackResult;
}
