<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\NotifiesUser;
use App\Models\Speaker;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class SpeakerManager extends Component
{
    use NotifiesUser;
    use WithFileUploads;

    public ?int $editingId = null;

    public bool $showForm = false;

    public string $name = '';

    public string $title = '';

    public string $bio = '';

    public bool $is_active = true;

    public string $sort_order = '0';

    public $photo;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:150'],
            'title' => ['nullable', 'string', 'max:150'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'nama', 'title' => 'gelaran', 'bio' => 'biodata',
            'sort_order' => 'susunan', 'photo' => 'gambar',
        ];
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $speaker = Speaker::findOrFail($id);

        $this->editingId = $speaker->id;
        $this->name = $speaker->name;
        $this->title = (string) $speaker->title;
        $this->bio = (string) $speaker->bio;
        $this->is_active = (bool) $speaker->is_active;
        $this->sort_order = (string) $speaker->sort_order;
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        $payload = [
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'title' => $data['title'] ?: null,
            'bio' => $data['bio'] ?: null,
            'is_active' => $data['is_active'],
            'sort_order' => (int) ($data['sort_order'] ?: 0),
        ];

        $speaker = $this->editingId ? Speaker::findOrFail($this->editingId) : new Speaker;

        // Slug mesti unik walaupun nama sama.
        if (Speaker::where('slug', $payload['slug'])->when($speaker->exists,
            fn ($q) => $q->whereKeyNot($speaker->id))->exists()) {
            $payload['slug'] .= '-'.Str::lower(Str::random(4));
        }

        if ($this->photo) {
            if ($speaker->photo_path && Storage::disk('public')->exists($speaker->photo_path)) {
                Storage::disk('public')->delete($speaker->photo_path);
            }

            $payload['photo_path'] = $this->photo->store('penceramah', 'public');
        }

        $speaker->fill($payload)->save();

        ActivityLogger::log($this->editingId ? 'speaker.updated' : 'speaker.created',
            $speaker, "Penceramah {$speaker->name} disimpan.");

        $this->resetForm();
        $this->notify('Penceramah disimpan.', 'success');
    }

    public function toggleActive(int $id): void
    {
        $speaker = Speaker::findOrFail($id);
        $speaker->update(['is_active' => ! $speaker->is_active]);
    }

    public function delete(int $id): void
    {
        $speaker = Speaker::withCount('events')->findOrFail($id);

        // Penceramah yang terikat pada program tidak dipadam — hanya dinyahaktifkan,
        // supaya rekod program lampau kekal utuh.
        if ($speaker->events_count > 0) {
            $speaker->update(['is_active' => false]);
            $this->notify("{$speaker->name} mempunyai {$speaker->events_count} program, jadi hanya dinyahaktifkan.", 'info');

            return;
        }

        $speaker->delete();
        $this->notify('Penceramah dibuang.', 'success');
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'title', 'bio', 'sort_order', 'showForm', 'photo']);
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.speaker-manager', [
            'speakers' => Speaker::ordered()->withCount('events')->get(),
        ]);
    }
}
