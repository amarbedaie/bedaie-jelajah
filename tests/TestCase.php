<?php

namespace Tests;

use App\Enums\PricingMode;
use App\Enums\UserRole;
use App\Models\CertificateTemplate;
use App\Models\District;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Speaker;
use App\Models\State;
use App\Models\User;
use App\Models\Venue;
use App\Services\ReferenceGenerator;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Str;

abstract class TestCase extends BaseTestCase
{
    /**
     * Data rujukan (negeri, daerah, kategori, penceramah, template sijil)
     * yang diperlukan hampir setiap ujian.
     */
    protected function seedReferenceData(): void
    {
        $this->seed(\Database\Seeders\StateSeeder::class);
        $this->seed(\Database\Seeders\CatalogSeeder::class);
        $this->seed(\Database\Seeders\SettingSeeder::class);
        $this->seed(\Database\Seeders\NotificationTemplateSeeder::class);
    }

    protected function admin(array $attributes = []): User
    {
        return $this->makeUser(UserRole::Admin, $attributes);
    }

    protected function penggerak(array $attributes = []): User
    {
        return $this->makeUser(UserRole::Penggerak, $attributes);
    }

    protected function peserta(array $attributes = []): User
    {
        return $this->makeUser(UserRole::Peserta, $attributes);
    }

    private function makeUser(UserRole $role, array $attributes = []): User
    {
        static $counter = 0;
        $counter++;

        return User::create(array_merge([
            'name' => ucfirst($role->value)." Ujian {$counter}",
            'email' => "{$role->value}{$counter}@ujian.test",
            'phone' => '6019'.str_pad((string) (1000000 + $counter), 7, '0', STR_PAD_LEFT),
            'password' => 'password',
            'role' => $role,
            'email_verified_at' => now(),
            'state_id' => State::where('code', 'SGR')->value('id'),
        ], $attributes));
    }

    /** Program yang siap untuk diuji, lengkap dengan lokasi dan QR pendaftaran. */
    protected function makeEvent(array $attributes = []): Event
    {
        $state = State::where('code', 'SGR')->firstOrFail();
        $district = District::where('state_id', $state->id)->first();

        $venue = Venue::create([
            'name' => 'Masjid Ujian '.Str::random(5),
            'address' => 'Alamat ujian',
            'state_id' => $state->id,
            'district_id' => $district?->id,
        ]);

        $startsAt = $attributes['starts_at'] ?? now()->addDays(14)->setTime(20, 30);
        $title = $attributes['title'] ?? 'Program Ujian '.Str::random(4);
        $references = app(ReferenceGenerator::class);

        $event = Event::create(array_merge([
            'short_code' => $references->eventShortCode(),
            'slug' => $references->eventSlug($title, $startsAt->year),
            'event_category_id' => EventCategory::value('id'),
            'speaker_id' => Speaker::value('id'),
            'venue_id' => $venue->id,
            'state_id' => $state->id,
            'district_id' => $district?->id,
            'title' => $title,
            'theme' => 'Tema ujian',
            'description' => 'Penerangan program ujian.',
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addHours(2),
            'doors_open_at' => $startsAt->copy()->subMinutes(30),
            'pricing_mode' => PricingMode::Percuma,
            'price' => 0,
            'capacity' => 10,
            'allow_waiting_list' => true,
            'registration_opens_at' => now()->subDay(),
            'registration_closes_at' => $startsAt->copy()->subHours(2),
            'status' => \App\Enums\EventStatus::Diterbitkan,
            'target_audience' => \App\Enums\TargetAudience::Umum,
            'certificate_enabled' => true,
            'certificate_template_id' => CertificateTemplate::where('is_default', true)->value('id'),
            'learning_hours' => 2,
            'published_at' => now(),
        ], $attributes));

        \App\Models\QrToken::create([
            'tokenable_type' => Event::class,
            'tokenable_id' => $event->id,
            'purpose' => 'pendaftaran',
        ]);

        return $event->fresh();
    }

    /**
     * Mengalihkan program ke masa lampau selepas pendaftaran dibuat —
     * meniru aliran sebenar (peserta daftar dahulu, program berlangsung kemudian).
     */
    protected function movePast(Event $event, int $daysAgo = 1): Event
    {
        $startsAt = now()->subDays($daysAgo)->setTime(20, 30);

        $event->forceFill([
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addHours(2),
            'doors_open_at' => $startsAt->copy()->subMinutes(30),
            'registration_closes_at' => $startsAt->copy()->subHours(2),
        ])->save();

        return $event->fresh();
    }

    /** Data borang permohonan yang sah. */
    protected function applicationPayload(array $overrides = []): array
    {
        $state = State::where('code', 'KEL')->firstOrFail();

        return array_merge([
            'applicant_name' => 'Ahmad bin Abdullah',
            'applicant_phone' => '60123456789',
            'applicant_email' => 'ahmad@ujian.test',
            'background' => \App\Enums\ApplicantBackground::WakilMasjid,
            'state_id' => $state->id,
            'district_id' => District::where('state_id', $state->id)->value('id'),
            'venue_name' => 'Masjid Al-Ujian',
            'venue_address' => 'Jalan Ujian 1, Kota Bharu',
            'venue_consent' => \App\Enums\VenueConsent::SudahBersetuju,
            'event_category_id' => EventCategory::value('id'),
            'topic' => 'Kami mahukan sesi tentang qadha solat untuk ahli kariah.',
            'preferred_date_1' => now()->addDays(45)->toDateString(),
            'estimated_attendees' => \App\Enums\AttendeeEstimate::F101_300,
            'target_audience' => \App\Enums\TargetAudience::Umum,
        ], $overrides);
    }

    /** Data borang pendaftaran peserta yang sah. */
    protected function registrationPayload(array $overrides = []): array
    {
        static $counter = 0;
        $counter++;

        return array_merge([
            'name' => "Peserta Ujian {$counter}",
            'phone' => '6018'.str_pad((string) (2000000 + $counter), 7, '0', STR_PAD_LEFT),
            'email' => "peserta{$counter}@ujian.test",
            'gender' => 'lelaki',
            'state_id' => State::where('code', 'SGR')->value('id'),
        ], $overrides);
    }
}
