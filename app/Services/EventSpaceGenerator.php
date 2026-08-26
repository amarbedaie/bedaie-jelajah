<?php

namespace App\Services;

use App\Enums\CertificateType;
use App\Enums\EventStatus;
use App\Enums\PricingMode;
use App\Models\Application;
use App\Models\CertificateTemplate;
use App\Models\Event;
use App\Models\QrToken;
use App\Models\Venue;
use Illuminate\Support\Facades\DB;

/**
 * Automasi "Program Disahkan".
 *
 * Penggerak tidak mempunyai page builder — apabila admin mengesahkan
 * permohonan, keseluruhan EventSpace (landing page, link, QR, dashboard,
 * borang pendaftaran, kapasiti, sijil) dijana daripada template rasmi BeDaie.
 */
class EventSpaceGenerator
{
    public function __construct(
        private ReferenceGenerator $references,
        private QrCodeService $qr,
        private PosterGenerator $posters,
    ) {}

    public function createFromApplication(Application $application, array $overrides = []): Event
    {
        return DB::transaction(function () use ($application, $overrides) {
            $venue = $this->resolveVenue($application);
            $startsAt = $this->resolveStartsAt($application, $overrides);
            $title = $overrides['title'] ?? $this->buildTitle($application);

            $event = Event::create([
                'short_code' => $this->references->eventShortCode(),
                'slug' => $this->references->eventSlug($title, $startsAt->year),
                'application_id' => $application->id,
                'event_category_id' => $overrides['event_category_id'] ?? $application->event_category_id,
                'speaker_id' => $overrides['speaker_id'] ?? null,
                'venue_id' => $venue?->id,
                'state_id' => $application->state_id,
                'district_id' => $application->district_id,
                'title' => $title,
                'theme' => $overrides['theme'] ?? $application->topic,
                'description' => $overrides['description'] ?? $this->buildDescription($application),
                'starts_at' => $startsAt,
                'ends_at' => $overrides['ends_at'] ?? $startsAt->copy()->addHours(2),
                'doors_open_at' => $overrides['doors_open_at'] ?? $startsAt->copy()->subMinutes(30),
                'pricing_mode' => $overrides['pricing_mode'] ?? PricingMode::Percuma,
                'price' => $overrides['price'] ?? 0,
                'capacity' => $overrides['capacity'] ?? ($application->estimated_attendees?->suggestedCapacity() ?? 100),
                'registration_opens_at' => $overrides['registration_opens_at'] ?? now(),
                'registration_closes_at' => $overrides['registration_closes_at'] ?? $startsAt->copy()->subHours(2),
                'status' => $overrides['status'] ?? EventStatus::Diterbitkan,
                'target_audience' => $application->target_audience,
                'organizer_name' => $overrides['organizer_name'] ?? $application->venue_name,
                'contact_phone' => $overrides['contact_phone'] ?? config('jelajah.support.phone'),
                'certificate_enabled' => $overrides['certificate_enabled'] ?? true,
                'certificate_template_id' => CertificateTemplate::resolveFor(CertificateType::Penyertaan)?->id,
                'learning_hours' => $overrides['learning_hours'] ?? 2,
                'tentative' => $overrides['tentative'] ?? $this->defaultTentative($startsAt),
                'faqs' => $overrides['faqs'] ?? $this->defaultFaqs(),
                'parking_info' => $overrides['parking_info'] ?? $venue?->parking_info,
                'published_at' => now(),
            ]);

            // Penggerak dipautkan sebagai pemilik program
            if ($application->user_id) {
                $event->mobilizers()->syncWithoutDetaching([
                    $application->user_id => ['role' => 'utama'],
                ]);
            }

            // QR pendaftaran awam untuk poster & sebaran
            QrToken::create([
                'tokenable_type' => Event::class,
                'tokenable_id' => $event->id,
                'purpose' => 'pendaftaran',
            ]);

            $this->qr->store($event->shortUrl(), "qr/program/{$event->short_code}.png", 720);

            // Poster rasmi daripada templat BeDaie — Penggerak tidak perlu
            // mereka apa-apa, hanya memuat turun dan menyebarkan.
            $this->posters->generate($event);

            $application->update(['event_id' => $event->id]);

            ActivityLogger::log(
                'event.generated',
                $event,
                "EventSpace dijana automatik daripada permohonan {$application->reference_no}.",
                ['application_id' => $application->id, 'slug' => $event->slug],
            );

            return $event->fresh(['venue', 'state', 'district', 'speaker', 'category']);
        });
    }

    private function resolveVenue(Application $application): ?Venue
    {
        if (! $application->venue_name) {
            return null;
        }

        return Venue::firstOrCreate(
            [
                'name' => $application->venue_name,
                'state_id' => $application->state_id,
                'district_id' => $application->district_id,
            ],
            [
                'address' => $application->venue_address,
                'google_maps_url' => $application->venue_maps_url,
                'pic_name' => $application->venue_pic_name,
                'pic_phone' => $application->venue_pic_phone,
            ],
        );
    }

    private function resolveStartsAt(Application $application, array $overrides): \Illuminate\Support\Carbon
    {
        if (! empty($overrides['starts_at'])) {
            return \Illuminate\Support\Carbon::parse($overrides['starts_at']);
        }

        $date = $application->preferred_date_1 ?? $application->preferred_date_2 ?? now()->addWeeks(4);

        return \Illuminate\Support\Carbon::parse($date)->setTime(20, 30);
    }

    private function buildTitle(Application $application): string
    {
        $category = $application->category?->name ?? 'Jelajah Ilmu';
        $place = $application->venue_name ?: ($application->district?->name ?? $application->state?->name ?? 'Malaysia');

        return "{$category}: {$place}";
    }

    private function buildDescription(Application $application): string
    {
        $topic = $application->topic ?: 'pengisian ilmu bersama pasukan BeDaie';
        $place = $application->venue_name ?: 'lokasi program';

        return implode("\n\n", [
            "BeDaie Jelajah hadir di {$place} membawa {$topic}.",
            'Program ini terbuka kepada '.strtolower($application->target_audience?->label() ?? 'umum').
            ' dan dianjurkan dengan kerjasama komuniti setempat.',
            'Dari Masjid ke Masjid, Dari Hati ke Hati.',
        ]);
    }

    private function defaultTentative(\Illuminate\Support\Carbon $startsAt): array
    {
        $t = $startsAt->copy();

        return [
            ['masa' => $t->copy()->subMinutes(30)->format('g:ia'), 'aktiviti' => 'Pendaftaran & imbas QR kehadiran'],
            ['masa' => $t->format('g:ia'), 'aktiviti' => 'Bacaan doa & ucapan aluan'],
            ['masa' => $t->copy()->addMinutes(15)->format('g:ia'), 'aktiviti' => 'Sesi pengisian utama'],
            ['masa' => $t->copy()->addMinutes(90)->format('g:ia'), 'aktiviti' => 'Sesi soal jawab'],
            ['masa' => $t->copy()->addMinutes(120)->format('g:ia'), 'aktiviti' => 'Penutup & bersurai'],
        ];
    }

    private function defaultFaqs(): array
    {
        return [
            [
                'soalan' => 'Adakah program ini terbuka kepada umum?',
                'jawapan' => 'Ya. Program ini terbuka kepada semua yang berdaftar melalui pautan rasmi. Tempat adalah terhad mengikut kapasiti lokasi.',
            ],
            [
                'soalan' => 'Bolehkah saya membawa ahli keluarga?',
                'jawapan' => 'Boleh. Semasa mendaftar, anda boleh menambah nama ahli keluarga supaya kami dapat menyediakan tempat yang mencukupi.',
            ],
            [
                'soalan' => 'Adakah sijil disediakan?',
                'jawapan' => 'Ya. Sijil penyertaan digital akan dikeluarkan secara automatik kepada peserta yang hadir dan telah mengimbas QR kehadiran.',
            ],
            [
                'soalan' => 'Bagaimana jika saya tidak dapat hadir?',
                'jawapan' => 'Sila batalkan pendaftaran anda melalui pautan tiket supaya tempat tersebut boleh diberikan kepada peserta dalam senarai menunggu.',
            ],
        ];
    }
}
