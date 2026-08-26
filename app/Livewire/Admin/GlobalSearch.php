<?php

namespace App\Livewire\Admin;

use App\Models\Application;
use App\Models\Certificate;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Livewire\Component;

/** Carian merentas permohonan, program, peserta, penggerak dan sijil. */
class GlobalSearch extends Component
{
    public string $q = '';

    public bool $open = false;

    public function updatedQ(): void
    {
        $this->open = mb_strlen(trim($this->q)) >= 2;
    }

    public function close(): void
    {
        $this->reset('q', 'open');
    }

    public function render()
    {
        $term = trim($this->q);
        $groups = [];

        if (mb_strlen($term) >= 2) {
            $like = '%'.$term.'%';
            $digits = preg_replace('/\D/', '', $term);

            $groups['Permohonan'] = Application::query()
                ->where(fn ($q) => $q->where('reference_no', 'like', $like)
                    ->orWhere('applicant_name', 'like', $like)
                    ->orWhere('venue_name', 'like', $like))
                ->limit(4)->get()
                ->map(fn ($a) => [
                    'title' => $a->venue_name,
                    'meta' => $a->reference_no.' · '.$a->applicant_name,
                    'url' => route('admin.permohonan.show', $a),
                    'icon' => 'clipboard',
                ]);

            $groups['Program'] = Event::query()
                ->where(fn ($q) => $q->where('title', 'like', $like)
                    ->orWhere('short_code', 'like', $like))
                ->with('state')
                ->limit(4)->get()
                ->map(fn ($e) => [
                    'title' => $e->title,
                    'meta' => $e->short_code.' · '.$e->dateLabel(),
                    'url' => route('admin.program.show', $e),
                    'icon' => 'calendar',
                ]);

            $groups['Peserta'] = Registration::query()
                ->where(function ($q) use ($like, $digits) {
                    $q->where('name', 'like', $like)->orWhere('reference_no', 'like', $like);
                    if ($digits !== '') {
                        $q->orWhere('phone', 'like', "%{$digits}%");
                    }
                })
                ->with('event')
                ->limit(4)->get()
                ->map(fn ($r) => [
                    'title' => $r->name,
                    'meta' => $r->reference_no.' · '.($r->event?->title ?? '—'),
                    'url' => route('admin.peserta', ['q' => $r->reference_no]),
                    'icon' => 'users',
                ]);

            $groups['Penggerak'] = User::query()
                ->where('role', 'penggerak')
                ->where(function ($q) use ($like, $digits) {
                    $q->where('name', 'like', $like)->orWhere('email', 'like', $like);
                    if ($digits !== '') {
                        $q->orWhere('phone', 'like', "%{$digits}%");
                    }
                })
                ->limit(4)->get()
                ->map(fn ($u) => [
                    'title' => $u->name,
                    'meta' => $u->email,
                    'url' => route('admin.penggerak.show', $u),
                    'icon' => 'map',
                ]);

            $groups['Sijil'] = Certificate::query()
                ->where(fn ($q) => $q->where('certificate_number', 'like', $like)
                    ->orWhere('recipient_name', 'like', $like))
                ->limit(4)->get()
                ->map(fn ($c) => [
                    'title' => $c->recipient_name,
                    'meta' => $c->certificate_number,
                    'url' => $c->verificationUrl(),
                    'icon' => 'certificate',
                ]);

            $groups = array_filter($groups, fn ($rows) => $rows->isNotEmpty());
        }

        return view('livewire.admin.global-search', ['groups' => $groups]);
    }
}
