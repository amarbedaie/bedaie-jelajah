<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\NotifiesUser;
use App\Models\Event;
use App\Models\State;
use App\Models\Testimonial;
use App\Services\ActivityLogger;
use Livewire\Component;

/** Menguruskan testimoni yang dipaparkan pada laman awam. */
class TestimonialManager extends Component
{
    use NotifiesUser;

    public ?int $editingId = null;

    public string $name = '';

    public string $role_label = '';

    public string $quote = '';

    public string $rating = '';

    public string $event_id = '';

    public string $state_id = '';

    public bool $is_approved = true;

    public bool $is_featured = false;

    public string $sort_order = '0';

    public bool $showForm = false;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'role_label' => ['nullable', 'string', 'max:120'],
            'quote' => ['required', 'string', 'min:10', 'max:1000'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'event_id' => ['nullable', 'exists:events,id'],
            'state_id' => ['nullable', 'exists:states,id'],
            'is_approved' => ['boolean'],
            'is_featured' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'nama',
            'role_label' => 'peranan',
            'quote' => 'testimoni',
            'rating' => 'penilaian',
            'event_id' => 'program',
            'state_id' => 'negeri',
            'sort_order' => 'susunan',
        ];
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $testimonial = Testimonial::findOrFail($id);

        $this->editingId = $testimonial->id;
        $this->name = $testimonial->name;
        $this->role_label = (string) $testimonial->role_label;
        $this->quote = $testimonial->quote;
        $this->rating = (string) ($testimonial->rating ?? '');
        $this->event_id = (string) ($testimonial->event_id ?? '');
        $this->state_id = (string) ($testimonial->state_id ?? '');
        $this->is_approved = (bool) $testimonial->is_approved;
        $this->is_featured = (bool) $testimonial->is_featured;
        $this->sort_order = (string) $testimonial->sort_order;
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        $payload = [
            'name' => $data['name'],
            'role_label' => $data['role_label'] ?: null,
            'quote' => $data['quote'],
            'rating' => $data['rating'] !== '' ? (int) $data['rating'] : null,
            'event_id' => $data['event_id'] ?: null,
            'state_id' => $data['state_id'] ?: null,
            'is_approved' => $data['is_approved'],
            'is_featured' => $data['is_featured'],
            'sort_order' => (int) ($data['sort_order'] ?: 0),
        ];

        $testimonial = $this->editingId
            ? tap(Testimonial::findOrFail($this->editingId))->update($payload)
            : Testimonial::create($payload);

        ActivityLogger::log(
            $this->editingId ? 'testimonial.updated' : 'testimonial.created',
            $testimonial,
            "Testimoni {$testimonial->name} disimpan.",
        );

        $this->resetForm();
        $this->notify('Testimoni disimpan.', 'success');
    }

    public function toggleApproved(int $id): void
    {
        $testimonial = Testimonial::findOrFail($id);
        $testimonial->update(['is_approved' => ! $testimonial->is_approved]);

        $this->notify($testimonial->is_approved
            ? 'Testimoni diluluskan dan kini dipaparkan.'
            : 'Testimoni ditarik daripada paparan awam.', 'success');
    }

    public function toggleFeatured(int $id): void
    {
        $testimonial = Testimonial::findOrFail($id);
        $testimonial->update(['is_featured' => ! $testimonial->is_featured]);
    }

    public function delete(int $id): void
    {
        Testimonial::findOrFail($id)->delete();

        ActivityLogger::log('testimonial.deleted', null, 'Testimoni dibuang.');
        $this->notify('Testimoni dibuang.', 'success');
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'role_label', 'quote', 'rating',
            'event_id', 'state_id', 'is_featured', 'sort_order', 'showForm']);
        $this->is_approved = true;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.testimonial-manager', [
            'testimonials' => Testimonial::with('event')->ordered()->get(),
            'events' => Event::orderByDesc('starts_at')->limit(100)->get(),
            'states' => State::orderBy('sort_order')->get(),
        ]);
    }
}
