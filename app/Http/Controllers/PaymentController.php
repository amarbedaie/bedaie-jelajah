<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Services\ActivityLogger;
use App\Services\Payments\PaymentManager;
use App\Services\RegistrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentManager $payments,
        private RegistrationService $registrations,
    ) {}

    /** Arahan pembayaran untuk satu pendaftaran. */
    public function show(Payment $payment)
    {
        $payment->load(['registration.event.venue', 'registration.event.state']);

        $gateway = $this->payments->gateway($payment->gateway);

        return view('public.payment', [
            'payment' => $payment,
            'registration' => $payment->registration,
            'event' => $payment->registration->event,
            'gateway' => $gateway,
            'intent' => $payment->status === PaymentStatus::BelumBayar
                ? $gateway->initiate($payment)
                : null,
        ]);
    }

    /**
     * Webhook/return-url gateway. Tandatangan disahkan di dalam gateway;
     * kami tidak pernah mempercayai status yang dihantar mentah-mentah.
     */
    public function callback(Request $request, string $gateway)
    {
        $driver = $this->payments->gateway($gateway);
        $result = $driver->handleCallback($request);

        if (! $result->verified) {
            Log::warning('Callback pembayaran gagal disahkan.', [
                'gateway' => $gateway,
                'message' => $result->message,
            ]);

            return response()->json(['ok' => false], 400);
        }

        $payment = Payment::where('gateway_reference', $result->reference)
            ->orWhere('public_id', $result->reference)
            ->first();

        if (! $payment) {
            Log::warning('Callback pembayaran untuk rujukan tidak dikenali.', [
                'gateway' => $gateway,
                'reference' => $result->reference,
            ]);

            return response()->json(['ok' => false], 404);
        }

        // Idempotent — callback yang sama boleh tiba lebih daripada sekali.
        if ($payment->status === PaymentStatus::Berjaya) {
            return response()->json(['ok' => true, 'note' => 'sudah diproses']);
        }

        $payment->update([
            'status' => $result->status,
            'gateway_payload' => $result->payload,
            'paid_at' => $result->status === PaymentStatus::Berjaya ? now() : null,
        ]);

        if ($result->status === PaymentStatus::Berjaya) {
            $this->registrations->confirm($payment->registration);
        }

        ActivityLogger::log('payment.callback', $payment,
            "Callback {$gateway}: {$result->status->value}.");

        return $request->isMethod('get')
            ? redirect()->route('tiket.show', $payment->registration->public_token)
                ->with($result->status === PaymentStatus::Berjaya ? 'success' : 'warning',
                    $result->status === PaymentStatus::Berjaya
                        ? 'Pembayaran berjaya. Tiket dan QR kehadiran anda sudah sedia.'
                        : 'Pembayaran tidak berjaya. Sila cuba sekali lagi.')
            : response()->json(['ok' => true]);
    }
}
