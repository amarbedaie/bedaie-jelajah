<?php

namespace App\Livewire\Public;

use App\Models\AreaInterestRequest;
use App\Models\District;
use App\Models\EventCategory;
use App\Models\State;
use App\Services\ActivityLogger;
use App\Services\ImpactStatsService;
use App\Support\Phone;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

/**
 * "Saya Mahu BeDaie Datang ke Sini" — borang minat ringkas.
 * Permintaan digabungkan mengikut negeri, daerah dan poskod supaya
 * admin dapat melihat kawasan dengan permintaan tertinggi.
 */
class InterestForm extends Component
{
    public string $name = '';

    public string $phone = '';

    public string $state_id = '';

    public string $district_id = '';

    public string $postcode = '';

    public string $event_category_id = '';

    public bool $submitted = false;

    public function mount(): void
    {
        if ($user = auth()->user()) {
            $this->name = $user->name;
            $this->phone = $user->phone ?? '';
            $this->state_id = (string) ($user->state_id ?? '');
            $this->district_id = (string) ($user->district_id ?? '');
        }

        // Pautan masuk ?negeri=kedah daripada peta jelajah.
        if ($slug = request()->query('negeri')) {
            $this->state_id = (string) (State::where('slug', $slug)->value('id') ?? $this->state_id);
        }
    }

    public function updatedStateId(): void
    {
        $this->district_id = '';
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'phone' => ['required', 'string', 'min:9', 'max:20'],
            'state_id' => ['required', 'exists:states,id'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'postcode' => ['nullable', 'digits:5'],
            'event_category_id' => ['nullable', 'exists:event_categories,id'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'nama',
            'phone' => 'nombor WhatsApp',
            'state_id' => 'negeri',
            'district_id' => 'daerah',
            'postcode' => 'poskod',
            'event_category_id' => 'program yang diminati',
        ];
    }

    public function submit(): void
    {
        $this->validate();

        $key = 'minat:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 6)) {
            $this->addError('phone', 'Terlalu banyak percubaan. Sila cuba sebentar lagi.');

            return;
        }

        RateLimiter::hit($key, 3600);

        $phone = Phone::normalise($this->phone) ?? $this->phone;

        // Satu permintaan per nombor per negeri — elak kiraan berganda.
        $request = AreaInterestRequest::updateOrCreate(
            ['phone' => $phone, 'state_id' => (int) $this->state_id],
            [
                'name' => $this->name,
                'district_id' => $this->district_id ? (int) $this->district_id : null,
                'postcode' => $this->postcode ?: null,
                'event_category_id' => $this->event_category_id ? (int) $this->event_category_id : null,
                'status' => 'baharu',
            ],
        );

        ActivityLogger::log('area_interest.submitted', $request,
            "Permintaan kawasan baharu daripada {$this->name}.");

        ImpactStatsService::flush();

        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.public.interest-form', [
            'states' => State::orderBy('sort_order')->get(),
            'districts' => $this->state_id
                ? District::where('state_id', $this->state_id)->orderBy('name')->get()
                : collect(),
            'categories' => EventCategory::active()->ordered()->get(),
        ]);
    }
}
