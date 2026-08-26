<?php

namespace App\Livewire\Admin;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Services\ActivityLogger;
use App\Services\RegistrationService;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Pengesahan bayaran manual (pindahan bank). Mengesahkan bayaran
 * turut mengesahkan pendaftaran peserta secara automatik.
 */
class PaymentReview extends Component
{
    use WithPagination;

    public string $status = 'belum_bayar';

    public string $search = '';

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function confirm(int $id, RegistrationService $registrations): void
    {
        $payment = Payment::with('registration')->findOrFail($id);

        if ($payment->status === PaymentStatus::Berjaya) {
            session()->flash('info', 'Bayaran ini telah pun disahkan.');

            return;
        }

        $payment->update([
            'status' => PaymentStatus::Berjaya,
            'paid_at' => now(),
            'verified_by' => auth()->id(),
        ]);

        if ($payment->registration) {
            $registrations->confirm($payment->registration);
        }

        ActivityLogger::log('payment.confirmed', $payment,
            "Bayaran {$payment->registration?->reference_no} disahkan secara manual.");

        session()->flash('success', 'Bayaran disahkan dan pendaftaran peserta telah diaktifkan.');
    }

    public function markFailed(int $id): void
    {
        $payment = Payment::findOrFail($id);
        $payment->update(['status' => PaymentStatus::Gagal]);

        ActivityLogger::log('payment.failed', $payment, 'Bayaran ditanda gagal oleh admin.');
        session()->flash('warning', 'Bayaran ditanda sebagai gagal.');
    }

    public function markRefunded(int $id): void
    {
        $payment = Payment::findOrFail($id);
        $payment->update(['status' => PaymentStatus::Dipulangkan]);

        ActivityLogger::log('payment.refunded', $payment, 'Bayaran ditanda dipulangkan.');
        session()->flash('info', 'Bayaran ditanda sebagai dipulangkan.');
    }

    public function exempt(int $id, RegistrationService $registrations): void
    {
        $payment = Payment::with('registration')->findOrFail($id);
        $payment->update(['status' => PaymentStatus::Dikecualikan, 'amount' => 0]);

        if ($payment->registration) {
            $registrations->confirm($payment->registration);
        }

        ActivityLogger::log('payment.exempted', $payment, 'Peserta dikecualikan daripada bayaran.');
        session()->flash('success', 'Peserta dikecualikan dan pendaftaran diaktifkan.');
    }

    public function render()
    {
        $payments = Payment::with(['registration', 'event'])
            ->where('amount', '>', 0)
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->when(mb_strlen($this->search) >= 3, function ($q) {
                $term = '%'.$this->search.'%';
                $q->whereHas('registration', fn ($r) => $r->where('name', 'like', $term)
                    ->orWhere('reference_no', 'like', $term)
                    ->orWhere('phone', 'like', $term));
            })
            ->latest()
            ->paginate(20);

        return view('livewire.admin.payment-review', [
            'payments' => $payments,
            'statuses' => PaymentStatus::cases(),
            'pendingTotal' => Payment::where('status', PaymentStatus::BelumBayar)->where('amount', '>', 0)->count(),
        ]);
    }
}
