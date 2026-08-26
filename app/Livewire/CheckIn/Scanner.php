<?php

namespace App\Livewire\CheckIn;

use App\Enums\AttendanceMethod;
use App\Models\Event;
use App\Models\Registration;
use App\Services\AttendanceService;
use App\Services\CheckInResult;
use App\Services\ReferenceGenerator;
use App\Services\RegistrationService;
use App\Support\Phone;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Skrin check-in di pintu masuk. Kamera adalah cara utama, tetapi
 * carian manual dan pendaftaran walk-in sentiasa tersedia sebagai sandaran.
 */
class Scanner extends Component
{
    #[Locked]
    public int $eventId;

    public string $manualCode = '';

    public string $search = '';

    /** Keputusan imbasan terakhir untuk dipaparkan pada skrin. */
    public ?array $result = null;

    // Pendaftaran walk-in
    public bool $showWalkIn = false;

    public string $walkInName = '';

    public string $walkInPhone = '';

    public string $walkInGender = '';

    public function mount(Event $event): void
    {
        $this->authorize('checkIn', $event);

        $this->eventId = $event->id;
    }

    public function getEventProperty(): Event
    {
        return Event::with(['venue', 'state'])->findOrFail($this->eventId);
    }

    /** Dipanggil oleh pengimbas kamera dan input kod manual. */
    public function scan(string $token, AttendanceService $attendance): void
    {
        $token = trim($token);

        if ($token === '') {
            return;
        }

        // QR mengandungi URL penuh; ambil segmen terakhir sebagai token.
        if (str_contains($token, '/')) {
            $token = (string) str($token)->afterLast('/');
        }

        $event = $this->event;
        $lookup = $attendance->resolveToken($token, $event);

        if ($lookup->outcome === CheckInResult::FAIL) {
            $this->present($lookup);

            return;
        }

        $this->present($attendance->checkIn($lookup->registration, auth()->user(), AttendanceMethod::Qr));
        $this->manualCode = '';
    }

    /** Check-in manual daripada hasil carian nama/telefon. */
    public function checkInManually(int $registrationId, AttendanceService $attendance): void
    {
        $registration = Registration::where('event_id', $this->eventId)->findOrFail($registrationId);

        $this->present($attendance->checkIn($registration, auth()->user(), AttendanceMethod::Manual));
        $this->search = '';
    }

    public function registerWalkIn(
        RegistrationService $registrations,
        AttendanceService $attendance,
        ReferenceGenerator $references,
    ): void {
        $this->validate([
            'walkInName' => ['required', 'string', 'min:3', 'max:120'],
            'walkInPhone' => ['required', 'string', 'min:9', 'max:20'],
            'walkInGender' => ['nullable', 'in:lelaki,perempuan'],
        ], [], [
            'walkInName' => 'nama penuh',
            'walkInPhone' => 'nombor telefon',
        ]);

        $event = $this->event;

        $registration = $registrations->register($event, [
            'name' => $this->walkInName,
            'phone' => Phone::normalise($this->walkInPhone) ?? $this->walkInPhone,
            'gender' => $this->walkInGender ?: null,
            'state_id' => $event->state_id,
            'district_id' => $event->district_id,
            'invite_code' => $event->invite_code,
        ], null, 'walk_in');

        $this->present($attendance->checkIn($registration, auth()->user(), AttendanceMethod::WalkIn));

        $this->reset('walkInName', 'walkInPhone', 'walkInGender', 'showWalkIn');
    }

    public function dismiss(): void
    {
        $this->result = null;
    }

    private function present(CheckInResult $result): void
    {
        $this->result = $result->toArray();

        // Isyarat bunyi/getar diuruskan di pihak pelayar.
        $this->dispatch('imbasan-selesai', outcome: $result->outcome);
    }

    public function render(AttendanceService $attendance)
    {
        $event = $this->event;

        return view('livewire.checkin.scanner', [
            'event' => $event,
            'stats' => $attendance->liveStats($event),
            'matches' => strlen($this->search) >= 3
                ? $attendance->search($event, $this->search)
                : collect(),
        ]);
    }
}
