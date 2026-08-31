<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\NotifiesUser;
use App\Models\Partner;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class PartnerManager extends Component
{
    use NotifiesUser;
    use WithFileUploads;

    public ?int $editingId = null;

    public bool $showForm = false;

    public string $name = '';

    public string $type = 'rakan';

    public string $tier = '';

    public string $website_url = '';

    public string $description = '';

    public bool $is_active = true;

    public bool $is_featured = false;

    public string $sort_order = '0';

    public $logo;

    public const TYPES = [
        'rakan' => 'Rakan Kerjasama',
        'penaja' => 'Penaja',
        'masjid' => 'Rakan Masjid & Surau',
        'media' => 'Rakan Media',
    ];

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:150'],
            'type' => ['required', Rule::in(array_keys(self::TYPES))],
            'tier' => ['nullable', 'string', 'max:40'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'nama', 'type' => 'jenis', 'tier' => 'tahap',
            'website_url' => 'pautan laman', 'description' => 'penerangan',
            'sort_order' => 'susunan', 'logo' => 'logo',
        ];
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $partner = Partner::findOrFail($id);

        $this->editingId = $partner->id;
        $this->name = $partner->name;
        $this->type = $partner->type ?: 'rakan';
        $this->tier = (string) $partner->tier;
        $this->website_url = (string) $partner->website_url;
        $this->description = (string) $partner->description;
        $this->is_active = (bool) $partner->is_active;
        $this->is_featured = (bool) $partner->is_featured;
        $this->sort_order = (string) $partner->sort_order;
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        $partner = $this->editingId ? Partner::findOrFail($this->editingId) : new Partner;
        $slug = Str::slug($data['name']);

        if (Partner::where('slug', $slug)
            ->when($partner->exists, fn ($q) => $q->whereKeyNot($partner->id))->exists()) {
            $slug .= '-'.Str::lower(Str::random(4));
        }

        $payload = [
            'name' => $data['name'],
            'slug' => $partner->exists ? $partner->slug : $slug,
            'type' => $data['type'],
            'tier' => $data['tier'] ?: null,
            'website_url' => $data['website_url'] ?: null,
            'description' => $data['description'] ?: null,
            'is_active' => $data['is_active'],
            'is_featured' => $data['is_featured'],
            'sort_order' => (int) ($data['sort_order'] ?: 0),
        ];

        if ($this->logo) {
            if ($partner->logo_path && Storage::disk('public')->exists($partner->logo_path)) {
                Storage::disk('public')->delete($partner->logo_path);
            }

            $payload['logo_path'] = $this->logo->store('rakan', 'public');
        }

        $partner->fill($payload)->save();

        ActivityLogger::log($this->editingId ? 'partner.updated' : 'partner.created',
            $partner, "Rakan {$partner->name} disimpan.");

        $this->resetForm();
        $this->notify('Rakan disimpan.', 'success');
    }

    public function toggleActive(int $id): void
    {
        $partner = Partner::findOrFail($id);
        $partner->update(['is_active' => ! $partner->is_active]);
    }

    public function delete(int $id): void
    {
        $partner = Partner::findOrFail($id);

        if ($partner->logo_path && Storage::disk('public')->exists($partner->logo_path)) {
            Storage::disk('public')->delete($partner->logo_path);
        }

        $partner->delete();
        $this->notify('Rakan dibuang.', 'success');
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'tier', 'website_url', 'description',
            'sort_order', 'showForm', 'logo', 'is_featured']);
        $this->type = 'rakan';
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.partner-manager', [
            'partners' => Partner::ordered()->get()->groupBy('type'),
            'types' => self::TYPES,
        ]);
    }
}
