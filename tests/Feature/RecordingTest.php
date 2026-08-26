<?php

namespace Tests\Feature;

use App\Enums\RecordingType;
use App\Enums\RecordingVisibility;
use App\Models\EventRecording;
use App\Services\AttendanceService;
use App\Services\RegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RecordingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
        Queue::fake();
    }

    private function recording(int $eventId, array $attributes = []): EventRecording
    {
        return EventRecording::create(array_merge([
            'event_id' => $eventId,
            'title' => 'Sesi Penuh Ujian',
            'type' => RecordingType::Video,
            'provider' => 'youtube',
            'url' => 'https://youtu.be/abc123XYZ',
            'visibility' => RecordingVisibility::Hadir,
            'is_published' => true,
        ], $attributes));
    }

    public function test_peserta_hadir_boleh_menonton_rakaman_terhad(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent();
        $registration = app(RegistrationService::class)->register($event, $this->registrationPayload());

        $event = $this->movePast($event);
        app(AttendanceService::class)->checkIn($registration, $admin);

        $recording = $this->recording($event->id);

        $this->assertTrue($recording->viewableBy($registration->fresh()));

        $this->get(route('rakaman.show', [$registration->public_token, $recording]))
            ->assertOk()
            ->assertSee('Sesi Penuh Ujian');
    }

    public function test_peserta_tidak_hadir_tidak_boleh_menonton_rakaman_terhad(): void
    {
        $event = $this->makeEvent();
        $registration = app(RegistrationService::class)->register($event, $this->registrationPayload());
        $this->movePast($event);

        $recording = $this->recording($event->id);

        $this->assertFalse($recording->viewableBy($registration->fresh()));
        $this->assertStringContainsString('hadir', $recording->lockedReason($registration->fresh()));

        $this->get(route('rakaman.show', [$registration->public_token, $recording]))
            ->assertForbidden();
    }

    public function test_rakaman_berdaftar_terbuka_kepada_yang_tidak_hadir(): void
    {
        $event = $this->makeEvent();
        $registration = app(RegistrationService::class)->register($event, $this->registrationPayload());

        $recording = $this->recording($event->id, ['visibility' => RecordingVisibility::Berdaftar]);

        $this->assertTrue($recording->viewableBy($registration->fresh()));
    }

    public function test_rakaman_belum_diterbitkan_tersembunyi(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent();
        $registration = app(RegistrationService::class)->register($event, $this->registrationPayload());
        $event = $this->movePast($event);
        app(AttendanceService::class)->checkIn($registration, $admin);

        $recording = $this->recording($event->id, ['is_published' => false]);

        $this->assertFalse($recording->viewableBy($registration->fresh()));
        $this->get(route('rakaman.show', [$registration->public_token, $recording]))->assertForbidden();
    }

    public function test_rakaman_berjadual_belum_dibuka(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent();
        $registration = app(RegistrationService::class)->register($event, $this->registrationPayload());
        $event = $this->movePast($event);
        app(AttendanceService::class)->checkIn($registration, $admin);

        $recording = $this->recording($event->id, ['available_from' => now()->addDays(3)]);

        $this->assertFalse($recording->viewableBy($registration->fresh()));
        $this->assertStringContainsString('dibuka', $recording->lockedReason($registration->fresh()));
    }

    public function test_rakaman_program_lain_ditolak(): void
    {
        $eventA = $this->makeEvent();
        $eventB = $this->makeEvent();
        $registration = app(RegistrationService::class)->register($eventA, $this->registrationPayload());

        $recording = $this->recording($eventB->id, ['visibility' => RecordingVisibility::Awam]);

        $this->get(route('rakaman.show', [$registration->public_token, $recording]))
            ->assertNotFound();
    }

    public function test_url_youtube_ditukar_kepada_url_benam(): void
    {
        $event = $this->makeEvent();

        foreach ([
            'https://youtu.be/abc123XYZ',
            'https://www.youtube.com/watch?v=abc123XYZ',
            'https://www.youtube.com/embed/abc123XYZ',
        ] as $url) {
            $recording = $this->recording($event->id, ['url' => $url]);

            $this->assertSame('https://www.youtube-nocookie.com/embed/abc123XYZ', $recording->embedUrl());
        }
    }

    public function test_tontonan_direkod_sekali_sehari(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent();
        $registration = app(RegistrationService::class)->register($event, $this->registrationPayload());
        $event = $this->movePast($event);
        app(AttendanceService::class)->checkIn($registration, $admin);

        $recording = $this->recording($event->id);
        $url = route('rakaman.show', [$registration->public_token, $recording]);

        $this->get($url)->assertOk();
        $this->get($url)->assertOk();

        $this->assertSame(1, $recording->views()->count());
    }

    public function test_senarai_rakaman_menunjukkan_yang_terkunci(): void
    {
        $event = $this->makeEvent();
        $registration = app(RegistrationService::class)->register($event, $this->registrationPayload());
        $this->movePast($event);

        $this->recording($event->id, ['title' => 'Rakaman Terhad Ujian']);

        $this->get(route('rakaman.index', $registration->public_token))
            ->assertOk()
            ->assertSee('Rakaman Terhad Ujian')
            ->assertSee('hanya untuk peserta yang hadir');
    }
}
