<?php

namespace App\Livewire\Admin;

use App\Enums\RecordingType;
use App\Enums\RecordingVisibility;
use App\Models\Event;
use App\Models\EventRecording;
use App\Services\ActivityLogger;
use Livewire\Attributes\Locked;
use Livewire\Component;

/** Menguruskan rakaman dan bahan sesuatu program. */
class RecordingManager extends Component
{
    #[Locked]
    public int $eventId;

    public ?int $editingId = null;

    public bool $showForm = false;

    public string $title = '';

    public string $description = '';

    public string $type = 'video';

    public string $provider = 'youtube';

    public string $url = '';

    public string $duration_minutes = '';

    public string $visibility = 'hadir';

    public bool $is_published = false;

    public string $available_from = '';

    public string $sort_order = '0';

    public function mount(Event $event): void
    {
        $this->eventId = $event->id;
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:180'],
            'description' => ['nullable', 'string', 'max:2000'],
            'type' => ['required', 'string'],
            'provider' => ['required', 'in:youtube,vimeo,pautan,fail'],
            'url' => ['required', 'url', 'max:500'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'visibility' => ['required', 'string'],
            'is_published' => ['boolean'],
            'available_from' => ['nullable', 'date'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'title' => 'tajuk', 'description' => 'penerangan', 'type' => 'jenis',
            'provider' => 'sumber', 'url' => 'pautan',
            'duration_minutes' => 'tempoh', 'visibility' => 'siapa boleh tonton',
            'available_from' => 'tarikh dibuka', 'sort_order' => 'susunan',
        ];
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $r = EventRecording::where('event_id', $this->eventId)->findOrFail($id);

        $this->editingId = $r->id;
        $this->title = $r->title;
        $this->description = (string) $r->description;
        $this->type = $r->type->value;
        $this->provider = $r->provider;
        $this->url = (string) $r->url;
        $this->duration_minutes = (string) ($r->duration_minutes ?? '');
        $this->visibility = $r->visibility->value;
        $this->is_published = (bool) $r->is_published;
        $this->available_from = $r->available_from?->format('Y-m-d\TH:i') ?? '';
        $this->sort_order = (string) $r->sort_order;
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        $payload = [
            'event_id' => $this->eventId,
            'title' => $data['title'],
            'description' => $data['description'] ?: null,
            'type' => RecordingType::from($data['type']),
            'provider' => $data['provider'],
            'url' => $data['url'],
            'duration_minutes' => $data['duration_minutes'] !== '' ? (int) $data['duration_minutes'] : null,
            'visibility' => RecordingVisibility::from($data['visibility']),
            'is_published' => $data['is_published'],
            'available_from' => $data['available_from'] ?: null,
            'sort_order' => (int) ($data['sort_order'] ?: 0),
            'uploaded_by' => auth()->id(),
        ];

        $recording = $this->editingId
            ? tap(EventRecording::findOrFail($this->editingId))->update($payload)
            : EventRecording::create($payload);

        ActivityLogger::log($this->editingId ? 'recording.updated' : 'recording.created',
            $recording, "Rakaman \"{$recording->title}\" disimpan.");

        $this->resetForm();
        session()->flash('success', 'Rakaman disimpan.');
    }

    public function togglePublished(int $id): void
    {
        $r = EventRecording::where('event_id', $this->eventId)->findOrFail($id);
        $r->update(['is_published' => ! $r->is_published]);

        session()->flash('success', $r->is_published
            ? 'Rakaman diterbitkan dan kini boleh ditonton peserta yang layak.'
            : 'Rakaman ditarik daripada paparan peserta.');
    }

    public function delete(int $id): void
    {
        EventRecording::where('event_id', $this->eventId)->findOrFail($id)->delete();
        session()->flash('success', 'Rakaman dibuang.');
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'title', 'description', 'url', 'duration_minutes',
            'available_from', 'sort_order', 'showForm', 'is_published']);
        $this->type = 'video';
        $this->provider = 'youtube';
        $this->visibility = 'hadir';
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.recording-manager', [
            'recordings' => EventRecording::where('event_id', $this->eventId)
                ->withCount('views')->ordered()->get(),
            'types' => RecordingType::cases(),
            'visibilities' => RecordingVisibility::cases(),
        ]);
    }
}
