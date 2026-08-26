<?php

namespace App\Livewire\Admin;

use App\Models\Certificate;
use App\Services\CertificateService;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Jana semula (pembetulan nama) atau tarik balik satu sijil.
 * Sijil lama sentiasa dikekalkan dan ditandakan "Digantikan" —
 * jejak audit tidak pernah dipadam.
 */
class CertificateActions extends Component
{
    #[Locked]
    public int $certificateId;

    public string $mode = '';

    public string $correctedName = '';

    public string $revokeReason = '';

    public function mount(Certificate $certificate): void
    {
        $this->certificateId = $certificate->id;
    }

    public function getCertificateProperty(): Certificate
    {
        return Certificate::findOrFail($this->certificateId);
    }

    public function startRegenerate(): void
    {
        $this->mode = 'regenerate';
        $this->correctedName = $this->certificate->recipient_name;
        $this->resetValidation();
    }

    public function startRevoke(): void
    {
        $this->mode = 'revoke';
        $this->revokeReason = '';
        $this->resetValidation();
    }

    public function cancel(): void
    {
        $this->reset('mode', 'correctedName', 'revokeReason');
        $this->resetValidation();
    }

    public function regenerate(CertificateService $certificates): void
    {
        $this->validate([
            'correctedName' => ['required', 'string', 'min:3', 'max:150'],
        ], [], ['correctedName' => 'nama yang dibetulkan']);

        $replacement = $certificates->regenerate(
            $this->certificate,
            $this->correctedName,
            auth()->user(),
        );

        $this->cancel();
        $this->dispatch('sijil-dikemaskini');

        session()->flash('success',
            "Sijil dijana semula: {$replacement->certificate_number}. Sijil lama ditandakan digantikan.");
    }

    public function revoke(CertificateService $certificates): void
    {
        $this->validate([
            'revokeReason' => ['required', 'string', 'min:5', 'max:255'],
        ], [], ['revokeReason' => 'sebab pembatalan']);

        $certificates->revoke($this->certificate, $this->revokeReason, auth()->user());

        $this->cancel();
        $this->dispatch('sijil-dikemaskini');

        session()->flash('warning', 'Sijil telah ditarik balik dan tidak lagi boleh dimuat turun.');
    }

    public function render()
    {
        return view('livewire.admin.certificate-actions', [
            'certificate' => $this->certificate,
        ]);
    }
}
