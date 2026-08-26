<?php

namespace App\Livewire\Admin;

use App\Enums\OutreachPriority;
use App\Enums\OutreachSource;
use App\Enums\OutreachStage;
use App\Enums\OutreachTargetType;
use App\Models\District;
use App\Models\OutreachTarget;
use App\Models\Partner;
use App\Models\State;
use App\Models\User;
use App\Services\OutreachService;
use App\Support\Phone;
use Livewire\Component;

/**
 * Papan sasaran jelajah. Staf melihat setiap lokasi yang sedang dikejar,
 * siapa mengejarnya, dan dari mana ia datang.
 */
class OutreachBoard extends Component
{
    // ── Penapis ──
    public string $assignee = '';

    public string $state = '';

    public string $source = '';

    public string $priority = '';

    public bool $overdueOnly = false;

    public bool $mineOnly = false;

    public string $search = '';

    public string $view = 'papan';

    // ── Borang sasaran baharu ──
    public bool $showForm = false;

    public string $name = '';

    public string $type = 'masjid';

    public string $state_id = '';

    public string $district_id = '';

    public string $address = '';

    public string $form_source = 'staf_terus';

    public string $partner_id = '';

    public string $referrer_user_id = '';

    public string $referrer_name = '';

    public string $referrer_phone = '';

    public string $assigned_to = '';

    public string $form_priority = 'sederhana';

    public string $contact_name = '';

    public string $contact_phone = '';

    public string $next_action_at = '';

    public string $notes = '';

    public function mount(): void
    {
        $this->assigned_to = (string) auth()->id();
    }

    public function updatedStateId(): void
    {
        $this->district_id = '';
    }

    public function updated($property): void
    {
        // Penapis mengubah kandungan papan — tiada halaman untuk direset.
        if (in_array($property, ['assignee', 'state', 'source', 'priority', 'search'], true)) {
            $this->dispatch('papan-dikemaskini');
        }
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset([
            'name', 'address', 'partner_id', 'referrer_user_id', 'referrer_name',
            'referrer_phone', 'contact_name', 'contact_phone', 'next_action_at',
            'notes', 'district_id', 'state_id', 'showForm',
        ]);
        $this->type = 'masjid';
        $this->form_source = 'staf_terus';
        $this->form_priority = 'sederhana';
        $this->assigned_to = (string) auth()->id();
        $this->resetValidation();
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:180'],
            'type' => ['required', 'string'],
            'state_id' => ['required', 'exists:states,id'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'address' => ['nullable', 'string', 'max:255'],
            'form_source' => ['required', 'string'],
            'partner_id' => [
                $this->form_source === OutreachSource::Rakan->value ? 'required' : 'nullable',
                'exists:partners,id',
            ],
            'referrer_user_id' => [
                $this->form_source === OutreachSource::Penggerak->value ? 'required' : 'nullable',
                'exists:users,id',
            ],
            'referrer_name' => [
                $this->form_source === OutreachSource::Rujukan->value ? 'required' : 'nullable',
                'string', 'max:150',
            ],
            'referrer_phone' => ['nullable', 'string', 'max:20'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'form_priority' => ['required', 'string'],
            'contact_name' => ['nullable', 'string', 'max:150'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'next_action_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'nama lokasi', 'type' => 'jenis lokasi', 'state_id' => 'negeri',
            'district_id' => 'daerah', 'address' => 'alamat', 'form_source' => 'sumber sasaran',
            'partner_id' => 'rakan', 'referrer_user_id' => 'penggerak',
            'referrer_name' => 'nama perujuk', 'referrer_phone' => 'telefon perujuk',
            'assigned_to' => 'staf bertanggungjawab', 'form_priority' => 'keutamaan',
            'contact_name' => 'nama kontak', 'contact_phone' => 'telefon kontak',
            'next_action_at' => 'tarikh tindakan', 'notes' => 'nota',
        ];
    }

    public function save(OutreachService $outreach): void
    {
        $data = $this->validate();

        $outreach->create([
            'name' => $data['name'],
            'type' => OutreachTargetType::from($data['type']),
            'state_id' => (int) $data['state_id'],
            'district_id' => $data['district_id'] ? (int) $data['district_id'] : null,
            'address' => $data['address'] ?: null,
            'source' => OutreachSource::from($data['form_source']),
            'partner_id' => $data['partner_id'] ?: null,
            'referrer_user_id' => $data['referrer_user_id'] ?: null,
            'referrer_name' => $data['referrer_name'] ?: null,
            'referrer_phone' => $data['referrer_phone'] ? Phone::normalise($data['referrer_phone']) : null,
            'assigned_to' => $data['assigned_to'] ?: null,
            'priority' => OutreachPriority::from($data['form_priority']),
            'contact_name' => $data['contact_name'] ?: null,
            'contact_phone' => $data['contact_phone'] ? Phone::normalise($data['contact_phone']) : null,
            'contact_found_at' => $data['contact_phone'] ? now() : null,
            'stage' => $data['contact_phone']
                ? OutreachStage::KontakDijumpai
                : OutreachStage::Sasaran,
            'next_action_at' => $data['next_action_at'] ?: null,
            'notes' => $data['notes'] ?: null,
        ], auth()->user());

        $this->resetForm();
        session()->flash('success', 'Sasaran ditambah ke papan.');
    }

    /** Gerakkan sasaran satu langkah ke hadapan terus dari papan. */
    public function advance(int $id, OutreachService $outreach): void
    {
        $target = OutreachTarget::findOrFail($id);
        $board = OutreachStage::board();
        $index = array_search($target->stage, $board, true);

        if ($index === false || ! isset($board[$index + 1])) {
            return;
        }

        $outreach->moveStage($target, $board[$index + 1], null, auth()->user());
    }

    public function clearFilters(): void
    {
        $this->reset(['assignee', 'state', 'source', 'priority', 'overdueOnly', 'mineOnly', 'search']);
    }

    private function query()
    {
        return OutreachTarget::query()
            ->with(['state', 'district', 'assignee', 'partner', 'referrer'])
            ->when($this->mineOnly, fn ($q) => $q->where('assigned_to', auth()->id()))
            ->when($this->assignee !== '', fn ($q) => $q->where('assigned_to', $this->assignee))
            ->when($this->state !== '', fn ($q) => $q->where('state_id', $this->state))
            ->when($this->source !== '', fn ($q) => $q->where('source', $this->source))
            ->when($this->priority !== '', fn ($q) => $q->where('priority', $this->priority))
            ->when($this->overdueOnly, fn ($q) => $q->dueForAction())
            ->when(mb_strlen($this->search) >= 2, function ($q) {
                $term = '%'.$this->search.'%';
                $q->where(fn ($w) => $w->where('name', 'like', $term)
                    ->orWhere('reference_no', 'like', $term)
                    ->orWhere('contact_name', 'like', $term));
            });
    }

    public function hasFilters(): bool
    {
        return $this->mineOnly || $this->overdueOnly
            || $this->assignee !== '' || $this->state !== ''
            || $this->source !== '' || $this->priority !== '' || $this->search !== '';
    }

    public function render(OutreachService $outreach)
    {
        $targets = $this->query()
            ->orderByRaw("FIELD(priority, 'tinggi', 'sederhana', 'rendah')")
            ->orderByRaw('next_action_at IS NULL, next_action_at')
            ->get();

        return view('livewire.admin.outreach-board', [
            'stages' => OutreachStage::board(),
            'grouped' => $targets->groupBy(fn ($t) => $t->stage->value),
            'closed' => $targets->whereIn('stage', [OutreachStage::TidakBerminat, OutreachStage::Tangguh]),
            'total' => $targets->count(),
            'overdueCount' => $targets->filter->isOverdue()->count(),
            'wonCount' => $targets->where('stage', OutreachStage::Berjaya)->count(),
            'staff' => User::where('role', 'admin')->orderBy('name')->get(),
            'penggerak' => User::where('role', 'penggerak')->orderBy('name')->limit(200)->get(),
            'states' => State::orderBy('sort_order')->get(),
            'districts' => $this->state_id
                ? District::where('state_id', $this->state_id)->orderBy('name')->get()
                : collect(),
            'partners' => Partner::where('is_active', true)->orderBy('name')->get(),
            'sources' => OutreachSource::cases(),
            'types' => OutreachTargetType::cases(),
            'priorities' => OutreachPriority::cases(),
            'partnerPerformance' => $outreach->partnerPerformance(),
        ]);
    }
}
