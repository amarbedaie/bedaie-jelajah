<?php

namespace App\Livewire\Concerns;

/**
 * Maklum balas selepas tindakan Livewire.
 *
 * session()->flash() tidak berfungsi di dalam komponen Livewire: mesej
 * dipaparkan oleh partial di luar akar komponen, jadi permintaan XHR
 * memakan flash itu tanpa pernah memaparkannya. Setiap "berjaya disimpan"
 * dalam sistem ini hilang begitu sahaja — pengguna menekan butang dan
 * skrin nampak seperti tidak berlaku apa-apa.
 *
 * Menghantar peristiwa pelayar sebaliknya; pendengar toast berada di
 * luar setiap akar Livewire, jadi ia sentiasa sampai.
 */
trait NotifiesUser
{
    protected function notify(string $message, string $variant = 'success'): void
    {
        $this->dispatch('notify', message: $message, variant: $variant);
    }
}
