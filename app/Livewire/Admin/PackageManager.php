<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\NotifiesUser;
use App\Models\Event;
use App\Models\Package;
use App\Services\ActivityLogger;
use Livewire\Component;

/**
 * Katalog pakej jualan — dicipta sekali, digunakan untuk semua program.
 *
 * Dokumen dalaman pasukan BeDaie; peserta tidak pernah melihatnya.
 */
class PackageManager extends Component
{
    use NotifiesUser;

    public ?int $editingId = null;

    public bool $showForm = false;

    public string $name = '';

    public string $tagline = '';

    public string $price = '0';

    public string $items = '';

    public string $pitch = '';

    public bool $is_active = true;

    public string $sort_order = '0';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:140'],
            'tagline' => ['nullable', 'string', 'max:200'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999'],
            'items' => ['nullable', 'string', 'max:2000'],
            'pitch' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'nama pakej',
            'price' => 'harga',
            'items' => 'kandungan',
            'pitch' => 'ayat jualan',
        ];
    }

    public function create(): void
    {
        $this->authorize('manage', Event::class);

        $this->reset(['editingId', 'name', 'tagline', 'items', 'pitch']);
        $this->price = '0';
        $this->sort_order = '0';
        $this->is_active = true;
        $this->showForm = true;
        $this->resetValidation();
    }

    public function edit(int $id): void
    {
        $this->authorize('manage', Event::class);

        $p = Package::findOrFail($id);

        $this->editingId = $p->id;
        $this->name = $p->name;
        $this->tagline = (string) $p->tagline;
        $this->price = (string) $p->price;
        $this->items = (string) $p->items;
        $this->pitch = (string) $p->pitch;
        $this->is_active = $p->is_active;
        $this->sort_order = (string) $p->sort_order;
        $this->showForm = true;
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->authorize('manage', Event::class);

        $data = $this->validate();

        $package = Package::updateOrCreate(
            ['id' => $this->editingId],
            [
                'name' => $data['name'],
                'tagline' => $data['tagline'] ?: null,
                'price' => $data['price'],
                'items' => $data['items'] ?: null,
                'pitch' => $data['pitch'] ?: null,
                'is_active' => $this->is_active,
                'sort_order' => (int) ($data['sort_order'] ?: 0),
            ],
        );

        ActivityLogger::log('package.saved', $package, "Pakej {$package->name} disimpan.");

        $this->showForm = false;
        $this->notify('Pakej disimpan.');
    }

    public function toggleActive(int $id): void
    {
        $this->authorize('manage', Event::class);

        $p = Package::findOrFail($id);
        $p->update(['is_active' => ! $p->is_active]);

        $this->notify($p->is_active ? 'Pakej diaktifkan.' : 'Pakej disembunyikan.');
    }

    public function cancel(): void
    {
        $this->showForm = false;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.package-manager', [
            'packages' => Package::ordered()->withCount('events')->get(),
        ]);
    }
}
