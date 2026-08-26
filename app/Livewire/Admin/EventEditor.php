<?php

namespace App\Livewire\Admin;

use App\Enums\EventStatus;
use App\Enums\PricingMode;
use App\Enums\TargetAudience;
use App\Models\District;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Speaker;
use App\Models\State;
use App\Models\Venue;
use App\Services\ActivityLogger;
use App\Services\EventLifecycleService;
use App\Services\ImpactStatsService;
use App\Services\Notifications\NotificationRecipient;
use App\Services\Notifications\NotificationService;
use App\Services\RegistrationService;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Menyunting program yang telah dijana. Perubahan tarikh atau lokasi
 * memberitahu peserta berdaftar secara automatik.
 */
class EventEditor extends Component
{
    use WithFileUploads;

    #[Locked]
    public int $eventId;

    public bool $open = false;

    public bool $posterPanel = false;

    // ── Maklumat teras ──
    public string $title = '';

    public string $theme = '';

    public string $description = '';

    public string $starts_at = '';

    public string $ends_at = '';

    public string $doors_open_at = '';

    public string $event_category_id = '';

    public string $speaker_id = '';

    public string $target_audience = '';

    public string $learning_hours = '';

    // ── Lokasi ──
    public string $venue_name = '';

    public string $venue_address = '';

    public string $google_maps_url = '';

    public string $state_id = '';

    public string $district_id = '';

    public string $parking_info = '';

    // ── Kapasiti & harga ──
    public string $capacity = '';

    public string $pricing_mode = '';

    public string $price = '';

    public bool $allow_waiting_list = true;

    public bool $allow_guest_registration = true;

    public string $max_guests_per_registration = '4';

    public string $invite_code = '';

    public string $registration_closes_at = '';

    // ── Lain-lain ──
    public string $organizer_name = '';

    public string $contact_phone = '';

    public bool $certificate_enabled = true;

    public $poster;

    public function mount(Event $event): void
    {
        $this->eventId = $event->id;
        $this->fill_from($event);
    }

    public function getEventProperty(): Event
    {
        return Event::with(['venue', 'state', 'district'])->findOrFail($this->eventId);
    }

    private function fill_from(Event $event): void
    {
        $this->title = $event->title;
        $this->theme = (string) $event->theme;
        $this->description = (string) $event->description;
        $this->starts_at = $event->starts_at?->format('Y-m-d\TH:i') ?? '';
        $this->ends_at = $event->ends_at?->format('Y-m-d\TH:i') ?? '';
        $this->doors_open_at = $event->doors_open_at?->format('Y-m-d\TH:i') ?? '';
        $this->event_category_id = (string) ($event->event_category_id ?? '');
        $this->speaker_id = (string) ($event->speaker_id ?? '');
        $this->target_audience = $event->target_audience?->value ?? '';
        $this->learning_hours = (string) ($event->learning_hours ?? '');

        $this->venue_name = (string) $event->venue?->name;
        $this->venue_address = (string) $event->venue?->address;
        $this->google_maps_url = (string) $event->venue?->google_maps_url;
        $this->state_id = (string) ($event->state_id ?? '');
        $this->district_id = (string) ($event->district_id ?? '');
        $this->parking_info = (string) $event->parking_info;

        $this->capacity = (string) ($event->capacity ?? '');
        $this->pricing_mode = $event->pricing_mode?->value ?? PricingMode::Percuma->value;
        $this->price = (string) ($event->price ?? '0');
        $this->allow_waiting_list = (bool) $event->allow_waiting_list;
        $this->allow_guest_registration = (bool) $event->allow_guest_registration;
        $this->max_guests_per_registration = (string) $event->max_guests_per_registration;
        $this->invite_code = (string) $event->invite_code;
        $this->registration_closes_at = $event->registration_closes_at?->format('Y-m-d\TH:i') ?? '';

        $this->organizer_name = (string) $event->organizer_name;
        $this->contact_phone = (string) $event->contact_phone;
        $this->certificate_enabled = (bool) $event->certificate_enabled;
    }

    public function updatedStateId(): void
    {
        $this->district_id = '';
    }

    /**
     * Menggeser masa tamat dan masa pendaftaran bersama-sama apabila tarikh
     * mula diubah — supaya admin tidak perlu membetulkan tiga medan.
     */
    public function updatedStartsAt(string $value): void
    {
        $original = $this->event->starts_at;

        if (! $original || $value === '') {
            return;
        }

        try {
            $new = \Illuminate\Support\Carbon::parse($value);
        } catch (\Throwable) {
            return;
        }

        $shift = $original->diffInSeconds($new, false);

        if ($shift === 0) {
            return;
        }

        foreach (['ends_at', 'doors_open_at', 'registration_closes_at'] as $field) {
            if ($this->{$field} === '') {
                continue;
            }

            $this->{$field} = \Illuminate\Support\Carbon::parse($this->{$field})
                ->addSeconds($shift)->format('Y-m-d\TH:i');
        }
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:180'],
            'theme' => ['nullable', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'doors_open_at' => ['nullable', 'date', 'before_or_equal:starts_at'],
            'event_category_id' => ['required', 'exists:event_categories,id'],
            'speaker_id' => ['nullable', 'exists:speakers,id'],
            'target_audience' => ['required', 'string'],
            'learning_hours' => ['nullable', 'numeric', 'min:0', 'max:100'],

            'venue_name' => ['required', 'string', 'max:180'],
            'venue_address' => ['nullable', 'string', 'max:500'],
            'google_maps_url' => ['nullable', 'url', 'max:500'],
            'state_id' => ['required', 'exists:states,id'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'parking_info' => ['nullable', 'string', 'max:500'],

            'capacity' => ['required', 'integer', 'min:1', 'max:100000'],
            'pricing_mode' => ['required', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'max_guests_per_registration' => ['required', 'integer', 'min:0', 'max:20'],
            'invite_code' => ['nullable', 'string', 'max:40'],
            'registration_closes_at' => ['nullable', 'date'],

            'organizer_name' => ['nullable', 'string', 'max:180'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'poster' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'title' => 'tajuk', 'theme' => 'tema', 'description' => 'penerangan',
            'starts_at' => 'tarikh mula', 'ends_at' => 'tarikh tamat',
            'doors_open_at' => 'masa pendaftaran dibuka',
            'event_category_id' => 'kategori', 'speaker_id' => 'penceramah',
            'target_audience' => 'sasaran peserta', 'learning_hours' => 'jam pembelajaran',
            'venue_name' => 'nama lokasi', 'venue_address' => 'alamat',
            'google_maps_url' => 'pautan Google Maps', 'state_id' => 'negeri',
            'district_id' => 'daerah', 'parking_info' => 'maklumat parkir',
            'capacity' => 'kapasiti', 'pricing_mode' => 'mod harga', 'price' => 'harga',
            'max_guests_per_registration' => 'had ahli keluarga',
            'invite_code' => 'kod jemputan', 'registration_closes_at' => 'penutupan pendaftaran',
            'organizer_name' => 'rakan lokasi', 'contact_phone' => 'telefon hubungan',
            'poster' => 'poster',
        ];
    }

    public function save(RegistrationService $registrations, NotificationService $notifications): void
    {
        $data = $this->validate();
        $event = $this->event;

        // Kapasiti tidak boleh diturunkan di bawah tempat yang telah diambil.
        $taken = $event->seatsTaken();
        if ((int) $data['capacity'] < $taken) {
            $this->addError('capacity',
                "Kapasiti tidak boleh kurang daripada {$taken} tempat yang telah diambil.");

            return;
        }

        $scheduleChanged = $event->starts_at?->format('Y-m-d\TH:i') !== $data['starts_at'];
        $venueChanged = $event->venue?->name !== $data['venue_name'];

        // Lokasi dikemas kini atau dicipta jika perlu.
        $venue = $event->venue ?? new Venue;
        $venue->fill([
            'name' => $data['venue_name'],
            'address' => $data['venue_address'] ?: null,
            'google_maps_url' => $data['google_maps_url'] ?: null,
            'state_id' => (int) $data['state_id'],
            'district_id' => $data['district_id'] ? (int) $data['district_id'] : null,
            'parking_info' => $data['parking_info'] ?: null,
        ])->save();

        $payload = [
            'title' => $data['title'],
            'theme' => $data['theme'] ?: null,
            'description' => $data['description'] ?: null,
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'] ?: null,
            'doors_open_at' => $data['doors_open_at'] ?: null,
            'event_category_id' => (int) $data['event_category_id'],
            'speaker_id' => $data['speaker_id'] ? (int) $data['speaker_id'] : null,
            'target_audience' => TargetAudience::from($data['target_audience']),
            'learning_hours' => $data['learning_hours'] !== '' ? (float) $data['learning_hours'] : null,
            'venue_id' => $venue->id,
            'state_id' => (int) $data['state_id'],
            'district_id' => $data['district_id'] ? (int) $data['district_id'] : null,
            'parking_info' => $data['parking_info'] ?: null,
            'capacity' => (int) $data['capacity'],
            'pricing_mode' => PricingMode::from($data['pricing_mode']),
            'price' => $data['pricing_mode'] === PricingMode::Berbayar->value ? (float) $data['price'] : 0,
            'allow_waiting_list' => $this->allow_waiting_list,
            'allow_guest_registration' => $this->allow_guest_registration,
            'max_guests_per_registration' => (int) $data['max_guests_per_registration'],
            'invite_code' => $data['invite_code'] ?: null,
            'registration_closes_at' => $data['registration_closes_at'] ?: null,
            'organizer_name' => $data['organizer_name'] ?: null,
            'contact_phone' => $data['contact_phone'] ?: null,
            'certificate_enabled' => $this->certificate_enabled,
        ];

        if ($this->poster) {
            // Buang poster lama supaya storan tidak menyimpan fail yatim.
            if ($event->poster_path && Storage::disk('public')->exists($event->poster_path)) {
                Storage::disk('public')->delete($event->poster_path);
            }

            $payload['poster_path'] = $this->poster->store("poster/{$event->short_code}", 'public');
        }

        $event->update($payload);
        $registrations->refreshCounts($event->fresh());
        ImpactStatsService::flush();

        ActivityLogger::log('event.updated', $event,
            "Program {$event->short_code} dikemas kini oleh ".auth()->user()->name.'.');

        // Peserta perlu tahu jika masa atau lokasi berubah.
        if ($scheduleChanged || $venueChanged) {
            $this->notifyParticipants($event->fresh(['venue']), $notifications);
        }

        $this->reset('poster');
        $this->open = false;

        session()->flash('success', $scheduleChanged || $venueChanged
            ? 'Program dikemas kini. Peserta berdaftar telah dimaklumkan tentang perubahan.'
            : 'Program dikemas kini.');
    }

    /** Memaklumkan peserta aktif tentang perubahan masa atau lokasi. */
    private function notifyParticipants(Event $event, NotificationService $notifications): void
    {
        $event->registrations()->active()->chunkById(200, function ($registrations) use ($event, $notifications) {
            foreach ($registrations as $registration) {
                $notifications->queue(
                    'peringatan_program',
                    NotificationRecipient::fromRegistration($registration),
                    [
                        'participant_name' => $registration->name,
                        'event_name' => $event->title,
                        'event_date' => $event->dateLabel(),
                        'event_time' => $event->timeLabel(),
                        'venue' => $event->venue?->name ?? $event->locationLabel(),
                        'qr_link' => $registration->ticketUrl(),
                    ],
                    $registration,
                    ['url' => $registration->ticketUrl(), 'action_label' => 'Lihat Tiket'],
                );
            }
        });
    }

    /** Menutup program secara manual: sijil dilepaskan, maklum balas diminta. */
    public function complete(EventLifecycleService $lifecycle): void
    {
        $event = $this->event;

        if ($event->status === EventStatus::Selesai) {
            session()->flash('info', 'Program ini telah pun ditutup.');

            return;
        }

        $result = $lifecycle->complete($event, auth()->user());

        session()->flash('success', sprintf(
            'Program ditutup. %d sijil dikeluarkan, %d permintaan maklum balas dihantar.',
            $result['certificates'] ?? 0,
            $result['feedback_requests'] ?? 0,
        ));
    }

    /**
     * Menjana semula poster dalam gaya yang dipilih.
     *
     * Penjana diselesaikan melalui bekas, bukan disuntik ke tanda tangan:
     * Livewire menyuntik parameter berjenis, yang mengelirukan pemetaan
     * hujah apabila kaedah ini dipanggil dengan gaya daripada papan.
     */
    public function regeneratePoster(?string $style = null): void
    {
        $chosen = $style
            ? \App\Enums\PosterStyle::tryFrom($style)
            : $this->event->poster_style;

        $path = app(\App\Services\PosterGenerator::class)->generate($this->event, $chosen);

        $path
            ? session()->flash('success',
                'Poster dijana semula dalam gaya '.($chosen?->label() ?? 'Klasik').'.')
            : session()->flash('warning',
                'Poster tidak dapat dijana pada pelayan ini (Imagick tidak tersedia).');
    }

    public function postpone(EventLifecycleService $lifecycle): void
    {
        $lifecycle->postpone($this->event, 'Ditangguhkan oleh pasukan BeDaie.');
        session()->flash('warning', 'Program ditandakan sebagai ditangguhkan.');
    }

    public function cancelEvent(EventLifecycleService $lifecycle): void
    {
        $lifecycle->cancel($this->event, 'Dibatalkan oleh pasukan BeDaie.');
        session()->flash('warning', 'Program dibatalkan dan peserta dimaklumkan.');
    }

    public function render()
    {
        $event = $this->event;

        return view('livewire.admin.event-editor', [
            'event' => $event,
            'categories' => EventCategory::active()->ordered()->get(),
            'speakers' => Speaker::active()->ordered()->get(),
            'states' => State::orderBy('sort_order')->get(),
            'districts' => $this->state_id
                ? District::where('state_id', $this->state_id)->orderBy('name')->get()
                : collect(),
            'pricingModes' => PricingMode::cases(),
            'posterStyles' => \App\Enums\PosterStyle::cases(),
            'audiences' => TargetAudience::cases(),
        ]);
    }
}
