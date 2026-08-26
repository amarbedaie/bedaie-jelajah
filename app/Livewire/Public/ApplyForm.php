<?php

namespace App\Livewire\Public;

use App\Enums\ApplicantBackground;
use App\Enums\AttendeeEstimate;
use App\Enums\TargetAudience;
use App\Enums\VenueConsent;
use App\Models\District;
use App\Models\EventCategory;
use App\Models\State;
use App\Services\ApplicationService;
use App\Support\Phone;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

/**
 * Borang "Jemput BeDaie" — empat langkah, sengaja ringkas.
 * Pemohon mungkin bukan penganjur profesional, jadi kami hanya
 * meminta maklumat yang benar-benar diperlukan pada peringkat ini.
 */
class ApplyForm extends Component
{
    public int $step = 1;

    public const LAST_STEP = 4;

    // ── Langkah 1: Tentang anda ──
    public string $applicant_name = '';

    public string $applicant_phone = '';

    public string $applicant_email = '';

    public string $state_id = '';

    public string $district_id = '';

    public string $background = '';

    public string $background_other = '';

    // ── Langkah 2: Cadangan lokasi ──
    public string $venue_name = '';

    public string $venue_address = '';

    public string $venue_maps_url = '';

    public string $venue_consent = '';

    public string $venue_pic_name = '';

    public string $venue_pic_phone = '';

    // ── Langkah 3: Cadangan program ──
    public string $event_category_id = '';

    public string $topic = '';

    public string $preferred_date_1 = '';

    public string $preferred_date_2 = '';

    public string $estimated_attendees = '';

    public string $target_audience = '';

    // ── Langkah 4: Semak & hantar ──
    public string $notes = '';

    public bool $privacy = false;

    public function mount(): void
    {
        // Isi awal daripada akaun sedia ada supaya kurang menaip.
        if ($user = auth()->user()) {
            $this->applicant_name = $user->name;
            $this->applicant_phone = $user->phone ?? '';
            $this->applicant_email = str_contains($user->email, '@jelajah.bedaie.local') ? '' : $user->email;
            $this->state_id = (string) ($user->state_id ?? $this->state_id);
            $this->district_id = (string) ($user->district_id ?? '');
            $this->background = $user->mobilizerProfile?->background?->value ?? '';
        }

        // Sokong pautan masuk ?negeri=selangor dan ?kategori=jelajah-ilmu
        // daripada peta dan halaman kategori. Slug sengaja tidak diikat pada
        // URL selepas ini supaya ID pangkalan data tidak terdedah.
        if ($slug = request()->query('negeri')) {
            $this->state_id = (string) (State::where('slug', $slug)->value('id') ?? $this->state_id);
        }

        if ($slug = request()->query('kategori')) {
            $this->event_category_id = (string) (EventCategory::where('slug', $slug)->value('id') ?? '');
        }
    }

    public function updatedStateId(): void
    {
        $this->district_id = '';
    }

    /** Peraturan per langkah — pengesahan hanya untuk langkah semasa. */
    protected function rulesForStep(int $step): array
    {
        return match ($step) {
            1 => [
                'applicant_name' => ['required', 'string', 'min:3', 'max:120'],
                'applicant_phone' => ['required', 'string', 'min:9', 'max:20'],
                'applicant_email' => ['nullable', 'email', 'max:180'],
                'state_id' => ['required', 'exists:states,id'],
                'district_id' => ['nullable', 'exists:districts,id'],
                'background' => ['required', 'string'],
                'background_other' => ['nullable', 'string', 'max:120'],
            ],
            2 => [
                'venue_name' => ['required', 'string', 'min:3', 'max:180'],
                'venue_address' => ['required', 'string', 'min:5', 'max:500'],
                'venue_maps_url' => ['nullable', 'url', 'max:500'],
                'venue_consent' => ['required', 'string'],
                'venue_pic_name' => ['nullable', 'string', 'max:120'],
                'venue_pic_phone' => ['nullable', 'string', 'max:20'],
            ],
            3 => [
                'event_category_id' => ['required', 'exists:event_categories,id'],
                'topic' => ['required', 'string', 'min:10', 'max:1000'],
                'preferred_date_1' => ['required', 'date', 'after:today'],
                'preferred_date_2' => ['nullable', 'date', 'after:today'],
                'estimated_attendees' => ['required', 'string'],
                'target_audience' => ['required', 'string'],
            ],
            default => [
                'notes' => ['nullable', 'string', 'max:1000'],
                'privacy' => ['accepted'],
            ],
        };
    }

    protected function validationAttributes(): array
    {
        return [
            'applicant_name' => 'nama penuh',
            'applicant_phone' => 'nombor WhatsApp',
            'applicant_email' => 'e-mel',
            'state_id' => 'negeri',
            'district_id' => 'daerah',
            'background' => 'latar belakang anda',
            'venue_name' => 'nama lokasi',
            'venue_address' => 'alamat',
            'venue_maps_url' => 'pautan Google Maps',
            'venue_consent' => 'persetujuan pihak lokasi',
            'venue_pic_phone' => 'nombor PIC lokasi',
            'event_category_id' => 'jenis program',
            'topic' => 'topik atau keperluan komuniti',
            'preferred_date_1' => 'cadangan tarikh pertama',
            'preferred_date_2' => 'cadangan tarikh kedua',
            'estimated_attendees' => 'anggaran peserta',
            'target_audience' => 'sasaran peserta',
        ];
    }

    protected function messages(): array
    {
        return [
            'privacy.accepted' => 'Sila setuju dengan polisi privasi untuk menghantar permohonan.',
            'preferred_date_1.after' => 'Cadangan tarikh mestilah selepas hari ini.',
            'preferred_date_2.after' => 'Cadangan tarikh kedua mestilah selepas hari ini.',
            'topic.min' => 'Ceritakan sedikit lagi supaya kami faham keperluan komuniti anda.',
        ];
    }

    public function next(): void
    {
        $this->validate($this->rulesForStep($this->step));

        $this->step = min(self::LAST_STEP, $this->step + 1);
        $this->dispatch('langkah-berubah');
    }

    public function back(): void
    {
        $this->step = max(1, $this->step - 1);
        $this->dispatch('langkah-berubah');
    }

    /** Membenarkan lompat ke langkah yang sudah dilepasi sahaja. */
    public function goTo(int $step): void
    {
        if ($step < $this->step) {
            $this->step = max(1, $step);
            $this->dispatch('langkah-berubah');
        }
    }

    public function submit(ApplicationService $applications)
    {
        // Semak semula semua langkah — pengguna mungkin mengubah data terdahulu.
        foreach (range(1, self::LAST_STEP) as $step) {
            $this->validate($this->rulesForStep($step));
        }

        $key = 'permohonan:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('privacy', 'Terlalu banyak percubaan. Sila cuba sebentar lagi.');

            return null;
        }

        RateLimiter::hit($key, 3600);

        $application = $applications->submit([
            'applicant_name' => $this->applicant_name,
            'applicant_phone' => Phone::normalise($this->applicant_phone) ?? $this->applicant_phone,
            'applicant_email' => $this->applicant_email ?: null,
            'background' => ApplicantBackground::from($this->background),
            'background_other' => $this->background_other ?: null,
            'state_id' => (int) $this->state_id,
            'district_id' => $this->district_id ? (int) $this->district_id : null,
            'venue_name' => $this->venue_name,
            'venue_address' => $this->venue_address,
            'venue_maps_url' => $this->venue_maps_url ?: null,
            'venue_consent' => VenueConsent::from($this->venue_consent),
            'venue_pic_name' => $this->venue_pic_name ?: null,
            'venue_pic_phone' => $this->venue_pic_phone ? Phone::normalise($this->venue_pic_phone) : null,
            'event_category_id' => (int) $this->event_category_id,
            'topic' => $this->topic,
            'preferred_date_1' => $this->preferred_date_1,
            'preferred_date_2' => $this->preferred_date_2 ?: null,
            'estimated_attendees' => AttendeeEstimate::from($this->estimated_attendees),
            'target_audience' => TargetAudience::from($this->target_audience),
            'notes' => $this->notes ?: null,
            'ip_address' => request()->ip(),
        ], auth()->user());

        return redirect()->route('jemput.berjaya', $application->public_id);
    }

    public function render()
    {
        return view('livewire.public.apply-form', [
            'states' => State::orderBy('sort_order')->get(),
            'districts' => $this->state_id
                ? District::where('state_id', $this->state_id)->orderBy('name')->get()
                : collect(),
            'categories' => EventCategory::active()->ordered()->get(),
            'backgrounds' => ApplicantBackground::cases(),
            'consents' => VenueConsent::cases(),
            'estimates' => AttendeeEstimate::cases(),
            'audiences' => TargetAudience::cases(),
        ]);
    }
}
