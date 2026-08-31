<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\NotifiesUser;
use App\Models\Event;
use App\Models\EventGallery;
use App\Services\ActivityLogger;
use App\Services\ImpactStatsService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

/**
 * Muat naik, luluskan dan buang gambar program.
 * Gambar hanya dipaparkan kepada umum selepas diluluskan admin.
 */
class GalleryManager extends Component
{
    use NotifiesUser;
    use WithFileUploads, WithPagination;

    public string $eventId = '';

    public string $caption = '';

    /** @var array<int, UploadedFile> */
    #[Validate(['photos.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120'])]
    public array $photos = [];

    public function upload(): void
    {
        $this->validate([
            'eventId' => ['required', 'exists:events,id'],
            'photos' => ['required', 'array', 'min:1', 'max:20'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'caption' => ['nullable', 'string', 'max:255'],
        ], [], [
            'eventId' => 'program',
            'photos' => 'gambar',
            'photos.*' => 'gambar',
        ]);

        $event = Event::findOrFail($this->eventId);
        $next = (int) EventGallery::where('event_id', $event->id)->max('sort_order');

        foreach ($this->photos as $photo) {
            $path = $photo->store("galeri/{$event->short_code}", 'public');

            EventGallery::create([
                'event_id' => $event->id,
                'image_path' => $path,
                'caption' => $this->caption ?: null,
                'sort_order' => ++$next,
                // Dimuat naik oleh admin — terus diluluskan.
                'is_approved' => true,
                'uploaded_by' => auth()->id(),
            ]);
        }

        ActivityLogger::log('gallery.uploaded', $event,
            count($this->photos).' gambar dimuat naik untuk '.$event->title.'.');

        ImpactStatsService::flush();

        $this->reset('photos', 'caption');
        $this->notify('Gambar berjaya dimuat naik.', 'success');
    }

    public function approve(int $id): void
    {
        $photo = EventGallery::findOrFail($id);
        $photo->update(['is_approved' => true]);

        ActivityLogger::log('gallery.approved', $photo, 'Gambar diluluskan untuk paparan awam.');
        ImpactStatsService::flush();

        $this->notify('Gambar diluluskan dan kini dipaparkan kepada umum.', 'success');
    }

    public function unapprove(int $id): void
    {
        $photo = EventGallery::findOrFail($id);
        $photo->update(['is_approved' => false]);

        ActivityLogger::log('gallery.unapproved', $photo, 'Gambar ditarik daripada paparan awam.');
        ImpactStatsService::flush();

        $this->notify('Gambar ditarik daripada paparan awam.', 'info');
    }

    public function updateCaption(int $id, string $value): void
    {
        EventGallery::findOrFail($id)->update([
            'caption' => trim($value) ?: null,
        ]);
    }

    public function delete(int $id): void
    {
        $photo = EventGallery::findOrFail($id);

        // Buang fail sebenar supaya storan tidak menyimpan yatim.
        if ($photo->image_path && Storage::disk('public')->exists($photo->image_path)) {
            Storage::disk('public')->delete($photo->image_path);
        }

        $photo->delete();

        ActivityLogger::log('gallery.deleted', null, 'Gambar galeri dibuang.');
        ImpactStatsService::flush();

        $this->notify('Gambar telah dibuang.', 'success');
    }

    public function render()
    {
        return view('livewire.admin.gallery-manager', [
            'events' => Event::orderByDesc('starts_at')->limit(100)->get(),
            'pending' => EventGallery::where('is_approved', false)
                ->with('event.state')->latest()->get(),
            'approved' => EventGallery::where('is_approved', true)
                ->with('event.state')->orderBy('sort_order')->orderByDesc('id')
                ->paginate(24),
        ]);
    }
}
