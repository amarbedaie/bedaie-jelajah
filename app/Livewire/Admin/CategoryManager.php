<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\NotifiesUser;
use App\Models\EventCategory;
use App\Services\ActivityLogger;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class CategoryManager extends Component
{
    use NotifiesUser;

    public ?int $editingId = null;

    public bool $showForm = false;

    public string $name = '';

    public string $icon = 'book';

    public string $tagline = '';

    public string $description = '';

    public bool $is_active = true;

    public string $sort_order = '0';

    /** Ikon yang tersedia dalam komponen <x-ui.icon>. */
    public const ICONS = [
        'book', 'mosque', 'heart', 'users', 'user', 'home', 'star',
        'sparkle', 'globe', 'handshake', 'building', 'clipboard', 'chat',
        'calendar', 'map', 'pin', 'shield', 'certificate',
    ];

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'icon' => ['required', Rule::in(self::ICONS)],
            'tagline' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'nama kategori', 'icon' => 'ikon', 'tagline' => 'slogan',
            'description' => 'penerangan', 'sort_order' => 'susunan',
        ];
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $category = EventCategory::findOrFail($id);

        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->icon = $category->icon ?: 'book';
        $this->tagline = (string) $category->tagline;
        $this->description = (string) $category->description;
        $this->is_active = (bool) $category->is_active;
        $this->sort_order = (string) $category->sort_order;
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        $category = $this->editingId ? EventCategory::findOrFail($this->editingId) : new EventCategory;
        $slug = Str::slug($data['name']);

        if (EventCategory::where('slug', $slug)
            ->when($category->exists, fn ($q) => $q->whereKeyNot($category->id))->exists()) {
            $slug .= '-'.Str::lower(Str::random(4));
        }

        // Slug kekal selepas dicipta supaya pautan sedia ada tidak putus.
        $category->fill([
            'name' => $data['name'],
            'slug' => $category->exists ? $category->slug : $slug,
            'icon' => $data['icon'],
            'tagline' => $data['tagline'] ?: null,
            'description' => $data['description'] ?: null,
            'is_active' => $data['is_active'],
            'sort_order' => (int) ($data['sort_order'] ?: 0),
        ])->save();

        ActivityLogger::log($this->editingId ? 'category.updated' : 'category.created',
            $category, "Kategori {$category->name} disimpan.");

        $this->resetForm();
        $this->notify('Kategori disimpan.', 'success');
    }

    public function toggleActive(int $id): void
    {
        $category = EventCategory::findOrFail($id);
        $category->update(['is_active' => ! $category->is_active]);
    }

    public function delete(int $id): void
    {
        $category = EventCategory::withCount(['events', 'applications'])->findOrFail($id);

        // Kategori yang sudah digunakan hanya dinyahaktifkan supaya
        // rekod program dan permohonan lampau kekal utuh.
        if ($category->events_count > 0 || $category->applications_count > 0) {
            $category->update(['is_active' => false]);
            $this->notify("{$category->name} sedang digunakan, jadi hanya dinyahaktifkan.", 'info');

            return;
        }

        $category->delete();
        $this->notify('Kategori dibuang.', 'success');
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'tagline', 'description', 'sort_order', 'showForm']);
        $this->icon = 'book';
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.category-manager', [
            'categories' => EventCategory::ordered()
                ->withCount(['events', 'applications', 'areaInterestRequests'])
                ->get(),
            'icons' => self::ICONS,
        ]);
    }
}
