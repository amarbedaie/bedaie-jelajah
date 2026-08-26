<?php

namespace App\Livewire\Admin;

use App\Enums\ApplicationStatus;
use App\Enums\PricingMode;
use App\Models\Application;
use App\Models\ApplicationNote;
use App\Models\Speaker;
use App\Services\ApplicationService;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Panel tindakan admin bagi satu permohonan: tukar status, rekod nota
 * dalaman, dan cipta EventSpace apabila program disahkan.
 */
class ApplicationWorkflow extends Component
{
    #[Locked]
    public int $applicationId;

    public string $status = '';

    public string $publicNote = '';

    public string $internalNote = '';

    // Medan tambahan yang muncul apabila status = Program Disahkan
    public string $title = '';

    public string $startsAt = '';

    public string $speakerId = '';

    public string $capacity = '';

    public string $pricingMode = 'percuma';

    public string $price = '';

    public string $learningHours = '2';

    // Nota komunikasi
    public string $noteBody = '';

    public string $noteChannel = 'whatsapp';

    public function mount(Application $application): void
    {
        $this->applicationId = $application->id;
        $this->status = $application->status->value;
        $this->prefillEventFields($application);
    }

    public function getApplicationProperty(): Application
    {
        return Application::with(['state', 'district', 'category', 'event'])->findOrFail($this->applicationId);
    }

    public function updatedStatus(): void
    {
        if ($this->status === ApplicationStatus::ProgramDisahkan->value) {
            $this->prefillEventFields($this->application);
        }
    }

    private function prefillEventFields(Application $application): void
    {
        $this->title = $this->title ?: trim(($application->category?->name ?? 'Jelajah').': '.$application->venue_name);
        $this->startsAt = $this->startsAt ?: ($application->preferred_date_1?->setTime(20, 30)->format('Y-m-d\TH:i') ?? '');
        $this->capacity = $this->capacity ?: (string) ($application->estimated_attendees?->suggestedCapacity() ?? 100);
    }

    public function confirmsProgram(): bool
    {
        return $this->status === ApplicationStatus::ProgramDisahkan->value;
    }

    protected function rules(): array
    {
        $rules = [
            'status' => ['required', 'string'],
            'publicNote' => ['nullable', 'string', 'max:500'],
            'internalNote' => ['nullable', 'string', 'max:1000'],
        ];

        if ($this->confirmsProgram()) {
            $rules += [
                'title' => ['required', 'string', 'min:5', 'max:180'],
                'startsAt' => ['required', 'date'],
                'speakerId' => ['nullable', 'exists:speakers,id'],
                'capacity' => ['required', 'integer', 'min:1', 'max:100000'],
                'pricingMode' => ['required', 'string'],
                'price' => ['nullable', 'numeric', 'min:0'],
                'learningHours' => ['nullable', 'numeric', 'min:0', 'max:100'],
            ];
        }

        return $rules;
    }

    protected function validationAttributes(): array
    {
        return [
            'status' => 'status',
            'publicNote' => 'nota untuk pemohon',
            'internalNote' => 'nota dalaman',
            'title' => 'tajuk program',
            'startsAt' => 'tarikh dan masa',
            'speakerId' => 'penceramah',
            'capacity' => 'kapasiti',
            'pricingMode' => 'mod harga',
            'price' => 'harga',
            'learningHours' => 'jam pembelajaran',
        ];
    }

    public function save(ApplicationService $applications): void
    {
        $this->validate();

        $application = $this->application;
        $overrides = [];

        if ($this->confirmsProgram()) {
            $overrides = array_filter([
                'title' => $this->title,
                'starts_at' => $this->startsAt,
                'speaker_id' => $this->speakerId ? (int) $this->speakerId : null,
                'capacity' => (int) $this->capacity,
                'pricing_mode' => PricingMode::from($this->pricingMode),
                'price' => $this->pricingMode === PricingMode::Berbayar->value ? (float) $this->price : 0,
                'learning_hours' => $this->learningHours !== '' ? (float) $this->learningHours : null,
            ], fn ($v) => $v !== null);
        }

        $applications->changeStatus(
            $application,
            ApplicationStatus::from($this->status),
            $this->publicNote ?: null,
            $this->internalNote ?: null,
            auth()->user(),
            $overrides,
        );

        $this->reset('publicNote', 'internalNote');

        session()->flash('success', $this->confirmsProgram()
            ? 'Program disahkan. Halaman program, link, QR dan poster telah dijana automatik.'
            : 'Status permohonan dikemas kini.');

        $this->dispatch('permohonan-dikemaskini');
    }

    public function addNote(): void
    {
        $this->validate([
            'noteBody' => ['required', 'string', 'min:3', 'max:2000'],
            'noteChannel' => ['required', 'in:whatsapp,telefon,emel,mesyuarat,lain'],
        ], [], ['noteBody' => 'nota', 'noteChannel' => 'saluran']);

        ApplicationNote::create([
            'application_id' => $this->applicationId,
            'user_id' => auth()->id(),
            'body' => $this->noteBody,
            'is_internal' => true,
            'channel' => $this->noteChannel,
            'occurred_at' => now(),
        ]);

        $this->reset('noteBody');
        $this->dispatch('permohonan-dikemaskini');
    }

    public function render()
    {
        return view('livewire.admin.application-workflow', [
            'application' => $this->application,
            'statuses' => ApplicationStatus::cases(),
            'speakers' => Speaker::active()->ordered()->get(),
            'pricingModes' => PricingMode::cases(),
        ]);
    }
}
