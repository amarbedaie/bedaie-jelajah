<?php

namespace App\Livewire\Admin;

use App\Enums\PaymentStatus;
use App\Livewire\Concerns\NotifiesUser;
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
    use NotifiesUser;
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
            $this->notify('Bayaran ini telah pun disahkan.', 'info');

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

        $this->notify('Bayaran disahkan dan pendaftaran peserta telah diaktifkan.', 'success');
    }

    public function markFailed(int $id): void
    {
        $payment = Payment::findOrFail($id);
        $payment->update(['status' => PaymentStatus::Gagal]);

        ActivityLogger::log('payment.failed', $payment, 'Bayaran ditanda gagal oleh admin.');
        $this->notify('Bayaran ditanda sebagai gagal.', 'warning');
    }

    public function markRefunded(int $id): void
    {
        $payment = Payment::findOrFail($id);
        $payment->update(['status' => PaymentStatus::Dipulangkan]);

        ActivityLogger::log('payment.refunded', $payment, 'Bayaran ditanda dipulangkan.');
        $this->notify('Bayaran ditanda sebagai dipulangkan.', 'info');
    }

    public function exempt(int $id, RegistrationService $registrations): void
    {
        $payment = Payment::with('registration')->findOrFail($id);
        $payment->update(['status' => PaymentStatus::Dikecualikan, 'amount' => 0]);

        if ($payment->registration) {
            $registrations->confirm($payment->registration);
        }

        ActivityLogger::log('payment.exempted', $payment, 'Peserta dikecualikan daripada bayaran.');
        $this->notify('Peserta dikecualikan dan pendaftaran diaktifkan.', 'success');
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
