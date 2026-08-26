<?php

namespace App\Livewire\Public;

use App\Enums\PaymentStatus;
use App\Enums\PricingMode;
use App\Models\District;
use App\Models\Event;
use App\Models\State;
use App\Services\RegistrationService;
use App\Support\Phone;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

/**
 * Borang pendaftaran peserta — sengaja pendek.
 * Hanya maklumat yang benar-benar diperlukan untuk kehadiran dan sijil.
 */
class RegistrationForm extends Component
{
    public Event $event;

    public string $name = '';

    public string $phone = '';

    public string $email = '';

    public string $gender = '';

    public string $state_id = '';

    public string $district_id = '';

    public string $invite_code = '';

    public bool $privacy = false;

    /** @var array<int, array{name: string, gender: string, age_group: string}> */
    public array $guests = [];

    public function mount(Event $event): void
    {
        $this->event = $event;

        if ($user = auth()->user()) {
            $this->name = $user->name;
            $this->phone = $user->phone ?? '';
            $this->email = $user->realEmail() ?? '';
            $this->gender = $user->gender ?? '';
            $this->state_id = (string) ($user->state_id ?? '');
            $this->district_id = (string) ($user->district_id ?? '');
        }

        // Peserta dari luar negeri lazimnya mendaftar untuk negeri program.
        if ($this->state_id === '') {
            $this->state_id = (string) $this->event->state_id;
        }
    }

    public function updatedStateId(): void
    {
        $this->district_id = '';
    }

    public function addGuest(): void
    {
        if (! $this->canAddGuest()) {
            return;
        }

        $this->guests[] = ['name' => '', 'gender' => '', 'age_group' => 'dewasa'];
    }

    public function removeGuest(int $index): void
    {
        unset($this->guests[$index]);
        $this->guests = array_values($this->guests);
    }

    public function canAddGuest(): bool
    {
        return $this->event->allow_guest_registration
            && count($this->guests) < (int) $this->event->max_guests_per_registration;
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'phone' => ['required', 'string', 'min:9', 'max:20'],
            'email' => ['nullable', 'email', 'max:180'],
            'gender' => ['nullable', 'in:lelaki,perempuan'],
            'state_id' => ['required', 'exists:states,id'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'invite_code' => [
                $this->event->pricing_mode === PricingMode::JemputanSahaja ? 'required' : 'nullable',
                'string', 'max:40',
            ],
            'guests' => ['array', 'max:'.max(0, (int) $this->event->max_guests_per_registration)],
            'guests.*.name' => ['required', 'string', 'min:2', 'max:120'],
            'guests.*.gender' => ['nullable', 'in:lelaki,perempuan'],
            'guests.*.age_group' => ['nullable', 'in:dewasa,remaja,kanak_kanak,warga_emas'],
            'privacy' => ['accepted'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'nama penuh',
            'phone' => 'nombor WhatsApp',
            'email' => 'e-mel',
            'gender' => 'jantina',
            'state_id' => 'negeri',
            'district_id' => 'daerah',
            'invite_code' => 'kod jemputan',
            'guests.*.name' => 'nama ahli keluarga',
        ];
    }

    protected function messages(): array
    {
        return [
            'privacy.accepted' => 'Sila setuju dengan polisi privasi untuk mendaftar.',
            'invite_code.required' => 'Program ini memerlukan kod jemputan.',
        ];
    }

    public function submit(RegistrationService $registrations)
    {
        $this->validate();

        $key = 'daftar:'.request()->ip().':'.$this->event->id;

        if (RateLimiter::tooManyAttempts($key, 8)) {
            $this->addError('privacy', 'Terlalu banyak percubaan. Sila cuba sebentar lagi.');

            return null;
        }

        RateLimiter::hit($key, 900);

        try {
            $registration = $registrations->register($this->event, [
                'name' => $this->name,
                'phone' => Phone::normalise($this->phone) ?? $this->phone,
                'email' => $this->email ?: null,
                'gender' => $this->gender ?: null,
                'state_id' => (int) $this->state_id,
                'district_id' => $this->district_id ? (int) $this->district_id : null,
                'invite_code' => $this->invite_code ?: null,
                'guests' => $this->guests,
            ], auth()->user());
        } catch (ValidationException $e) {
            throw $e;
        } catch (\RuntimeException $e) {
            $this->addError('privacy', $e->getMessage());

            return null;
        }

        // Program berbayar dibawa ke halaman arahan pembayaran dahulu.
        if ($registration->payment && $registration->payment->status === PaymentStatus::BelumBayar) {
            return redirect()->route('bayaran.show', $registration->payment->public_id);
        }

        return redirect()->route('tiket.show', $registration->public_token);
    }

    public function render()
    {
        return view('livewire.public.registration-form', [
            'states' => State::orderBy('sort_order')->get(),
            'districts' => $this->state_id
                ? District::where('state_id', $this->state_id)->orderBy('name')->get()
                : collect(),
        ]);
    }
}
