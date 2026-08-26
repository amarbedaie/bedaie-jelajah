<?php

namespace App\Livewire\Public;

use App\Models\Feedback;
use App\Models\Registration;
use App\Services\ActivityLogger;
use App\Services\CertificateService;
use Livewire\Component;

/**
 * Maklum balas peserta — sengaja pendek (empat soalan).
 * Admin boleh menetapkan maklum balas sebagai syarat sebelum sijil dilepaskan.
 */
class FeedbackForm extends Component
{
    public Registration $registration;

    public int $rating = 0;

    public string $most_beneficial = '';

    public string $next_topic = '';

    public bool $wants_advanced = false;

    public bool $submitted = false;

    public function mount(Registration $registration): void
    {
        $this->registration = $registration;

        if ($existing = $registration->feedback) {
            $this->rating = (int) $existing->rating;
            $this->most_beneficial = (string) $existing->most_beneficial;
            $this->next_topic = (string) $existing->next_topic;
            $this->wants_advanced = (bool) $existing->wants_advanced;
            $this->submitted = true;
        }
    }

    public function setRating(int $value): void
    {
        $this->rating = max(1, min(5, $value));
    }

    protected function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'most_beneficial' => ['nullable', 'string', 'max:1000'],
            'next_topic' => ['nullable', 'string', 'max:255'],
            'wants_advanced' => ['boolean'],
        ];
    }

    protected function messages(): array
    {
        return ['rating.required' => 'Sila pilih penilaian anda dari 1 hingga 5.'];
    }

    public function submit(CertificateService $certificates): void
    {
        $this->validate();

        Feedback::updateOrCreate(
            ['registration_id' => $this->registration->id],
            [
                'event_id' => $this->registration->event_id,
                'rating' => $this->rating,
                'most_beneficial' => $this->most_beneficial ?: null,
                'next_topic' => $this->next_topic ?: null,
                'wants_advanced' => $this->wants_advanced,
            ],
        );

        ActivityLogger::log('feedback.submitted', $this->registration,
            "{$this->registration->name} memberi maklum balas ({$this->rating}/5).");

        // Sijil mungkin tertahan sehingga maklum balas diterima.
        $certificates->issueForRegistration($this->registration->fresh(['event', 'attendance', 'feedback']));

        $this->registration->refresh();
        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.public.feedback-form');
    }
}
