<?php

namespace Database\Seeders;

use App\Enums\ApplicantBackground;
use App\Enums\ApplicationStatus;
use App\Enums\AttendanceMethod;
use App\Enums\AttendeeEstimate;
use App\Enums\EventStatus;
use App\Enums\PaymentStatus;
use App\Enums\PricingMode;
use App\Enums\RegistrationStatus;
use App\Enums\TargetAudience;
use App\Enums\UserRole;
use App\Enums\VenueConsent;
use App\Models\Application;
use App\Models\ApplicationNote;
use App\Models\ApplicationStatusHistory;
use App\Models\AreaInterestRequest;
use App\Models\District;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Feedback;
use App\Models\MobilizerProfile;
use App\Models\Registration;
use App\Models\Speaker;
use App\Models\State;
use App\Models\Testimonial;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\CertificateService;
use App\Services\EventLifecycleService;
use App\Services\ImpactStatsService;
use App\Services\ReferenceGenerator;
use App\Services\RegistrationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

/**
 * Data demo yang realistik supaya keseluruhan sistem boleh diuji
 * hujung-ke-hujung. Semua nama masjid/organisasi adalah rekaan dan
 * ditandakan "(Demo)" jika bukan entiti sebenar.
 */
class DemoSeeder extends Seeder
{
    private array $states = [];

    private array $categories = [];

    private array $speakers = [];

    public function run(): void
    {
        // Notifikasi tidak perlu dihantar semasa menjana data demo.
        Queue::fake();

        $this->states = State::with('districts')->get()->keyBy('code')->all();
        $this->categories = EventCategory::all()->keyBy('slug')->all();
        $this->speakers = Speaker::orderBy('sort_order')->get()->all();

        $admin = $this->createAdmin();
        $mobilizers = $this->createMobilizers();
        $participants = $this->createParticipants();

        $this->createApplications($admin, $mobilizers);
        $events = $this->createEvents($mobilizers);

        $this->createRegistrations($events, $participants, $admin);
        $this->completePastEvents($events, $admin);
        $this->createTestimonials($events);
        $this->createAreaInterest();
        $this->createPosters($events);

        ImpactStatsService::flush();
    }

    // ── Pengguna ─────────────────────────────────────────────────────

    private function createAdmin(): User
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@bedaie.test'],
            [
                'name' => 'Pasukan BeDaie',
                'phone' => '60123000001',
                'password' => 'password',
                'role' => UserRole::Admin,
                'email_verified_at' => now(),
                'state_id' => $this->state('SGR')->id,
            ],
        );

        User::updateOrCreate(
            ['email' => 'operasi@bedaie.test'],
            [
                'name' => 'Nabilah Operasi',
                'phone' => '60123000002',
                'password' => 'password',
                'role' => UserRole::Admin,
                'email_verified_at' => now(),
                'state_id' => $this->state('SGR')->id,
            ],
        );

        return $admin;
    }

    /** @return array<string, User> */
    private function createMobilizers(): array
    {
        $rows = [
            [
                'key' => 'kelantan',
                'name' => 'Ustaz Zulkifli bin Hassan',
                'email' => 'penggerak@bedaie.test',
                'phone' => '60193456789',
                'state' => 'KEL', 'district' => 'Kota Bharu',
                'background' => ApplicantBackground::WakilMasjid,
                'organization' => 'Masjid Al-Hidayah Kubang Kerian (Demo)',
                'about' => 'Ahli jawatankuasa masjid yang aktif menganjurkan kuliah maghrib untuk ahli kariah.',
            ],
            [
                'key' => 'selangor',
                'name' => 'Puan Siti Aminah binti Osman',
                'email' => 'aminah@bedaie.test',
                'phone' => '60127654321',
                'state' => 'SGR', 'district' => 'Kajang',
                'background' => ApplicantBackground::BekasPelajar,
                'organization' => 'Surau An-Nur Bandar Baru Bangi (Demo)',
                'about' => 'Bekas pelajar BeDaie yang ingin membawa program ilmu ke taman perumahannya.',
            ],
            [
                'key' => 'johor',
                'name' => 'Encik Rahman bin Abdullah',
                'email' => 'rahman@bedaie.test',
                'phone' => '60137778888',
                'state' => 'JHR', 'district' => 'Batu Pahat',
                'background' => ApplicantBackground::WakilSekolah,
                'organization' => 'Maahad Tahfiz Nurul Iman (Demo)',
                'about' => 'Guru besar tahfiz yang menganjurkan program motivasi untuk pelajar.',
            ],
        ];

        $mobilizers = [];

        foreach ($rows as $row) {
            $state = $this->state($row['state']);
            $district = $this->district($state, $row['district']);

            $user = User::updateOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'phone' => $row['phone'],
                    'password' => 'password',
                    'role' => UserRole::Penggerak,
                    'email_verified_at' => now(),
                    'state_id' => $state->id,
                    'district_id' => $district?->id,
                ],
            );

            MobilizerProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'background' => $row['background'],
                    'organization_name' => $row['organization'],
                    'about' => $row['about'],
                    'whatsapp' => $row['phone'],
                    'state_id' => $state->id,
                    'district_id' => $district?->id,
                    'verified_at' => now(),
                ],
            );

            $mobilizers[$row['key']] = $user;
        }

        return $mobilizers;
    }

    /** @return \Illuminate\Support\Collection<int, User> */
    private function createParticipants(): \Illuminate\Support\Collection
    {
        // Nama dipasangkan mengikut jantina supaya "bin"/"binti" sentiasa betul.
        $lelaki = [
            'Ahmad', 'Muhammad', 'Hafiz', 'Amir', 'Ridzuan', 'Syafiq', 'Idris', 'Rosli',
            'Danial', 'Iqbal', 'Azman', 'Haziq', 'Faizal', 'Zulhilmi', 'Amirul',
        ];
        $perempuan = [
            'Nurul', 'Siti', 'Aisyah', 'Farah', 'Zainab', 'Khadijah', 'Hana', 'Aida',
            'Maisarah', 'Sofea', 'Nadia', 'Alya', 'Liyana', 'Suriani', 'Balqis',
        ];
        $bapa = [
            'Abdullah', 'Ismail', 'Hassan', 'Yusof', 'Rahman', 'Omar',
            'Ibrahim', 'Salleh', 'Kamal', 'Aziz', 'Latif', 'Zakaria',
        ];

        $stateCodes = ['KEL', 'SGR', 'JHR', 'PHG', 'PRK', 'TRG', 'KDH', 'PNG', 'NSN', 'SWK', 'SBH', 'MLK'];
        $participants = collect();

        // Akaun peserta demo yang boleh dilog masuk
        $demo = User::updateOrCreate(
            ['email' => 'peserta@bedaie.test'],
            [
                'name' => 'Muhammad Danial bin Ismail',
                'phone' => '60181234567',
                'password' => 'password',
                'role' => UserRole::Peserta,
                'email_verified_at' => now(),
                'gender' => 'lelaki',
                'state_id' => $this->state('SGR')->id,
                'district_id' => $this->district($this->state('SGR'), 'Kajang')?->id,
            ],
        );
        $participants->push($demo);

        for ($i = 1; $i <= 74; $i++) {
            $isLelaki = $i % 2 === 0;
            $gender = $isLelaki ? 'lelaki' : 'perempuan';

            $nama = $isLelaki
                ? $lelaki[($i * 7) % count($lelaki)]
                : $perempuan[($i * 7) % count($perempuan)];

            $name = $nama.($isLelaki ? ' bin ' : ' binti ').$bapa[($i * 5) % count($bapa)];
            $state = $this->state($stateCodes[$i % count($stateCodes)]);

            $participants->push(User::updateOrCreate(
                ['email' => "peserta{$i}@demo.bedaie.test"],
                [
                    'name' => $name,
                    'phone' => '601'.str_pad((string) (20000000 + $i * 137), 8, '0', STR_PAD_LEFT),
                    'password' => 'password',
                    'role' => UserRole::Peserta,
                    'email_verified_at' => now(),
                    'gender' => $gender,
                    'state_id' => $state->id,
                    'district_id' => $state->districts->first()?->id,
                ],
            ));
        }

        return $participants;
    }

    // ── Permohonan ───────────────────────────────────────────────────

    private function createApplications(User $admin, array $mobilizers): void
    {
        $references = app(ReferenceGenerator::class);

        $rows = [
            [
                'user' => $mobilizers['selangor'],
                'venue' => 'Surau Ar-Raudhah Taman Cheras Jaya (Demo)',
                'state' => 'SGR', 'district' => 'Kajang',
                'category' => 'jelajah-keluarga',
                'status' => ApplicationStatus::DalamSemakan,
                'topic' => 'Kami mahukan sesi khusus untuk pasangan muda tentang komunikasi rumah tangga dan mendidik anak.',
                'consent' => VenueConsent::SudahBersetuju,
                'estimate' => AttendeeEstimate::F101_300,
                'audience' => TargetAudience::Keluarga,
                'days' => 46,
                'submitted' => 6,
            ],
            [
                'user' => null,
                'name' => 'Hajah Rokiah binti Daud',
                'phone' => '60125556677',
                'email' => 'rokiah.demo@example.com',
                'venue' => 'Masjid Jamek Kampung Baru Sungai Petani (Demo)',
                'state' => 'KDH', 'district' => 'Sungai Petani',
                'category' => 'jelajah-wanita',
                'status' => ApplicationStatus::PerluMaklumat,
                'topic' => 'Kelas fiqh wanita untuk muslimat kariah — fokus kepada haid, nifas dan istihadah.',
                'consent' => VenueConsent::SedangBerbincang,
                'estimate' => AttendeeEstimate::F50_100,
                'audience' => TargetAudience::Wanita,
                'days' => 60,
                'submitted' => 11,
                'background' => ApplicantBackground::AhliKariah,
            ],
            [
                'user' => null,
                'name' => 'Encik Faizal bin Kamarudin',
                'phone' => '60194443322',
                'email' => 'faizal.demo@example.com',
                'venue' => 'Dewan Serbaguna Felda Chini 3 (Demo)',
                'state' => 'PHG', 'district' => 'Pekan',
                'category' => 'jelajah-prihatin',
                'status' => ApplicationStatus::DalamPerbincangan,
                'topic' => 'Program santunan ilmu untuk komuniti Felda yang jauh daripada pusat bandar.',
                'consent' => VenueConsent::PerluBantuan,
                'estimate' => AttendeeEstimate::F101_300,
                'audience' => TargetAudience::Umum,
                'days' => 75,
                'submitted' => 15,
                'background' => ApplicantBackground::OrangAwam,
            ],
            [
                'user' => null,
                'name' => 'Ustazah Hafizah binti Mokhtar',
                'phone' => '60138889900',
                'email' => 'hafizah.demo@example.com',
                'venue' => 'SMK Seri Bintang Utara (Demo)',
                'state' => 'KUL', 'district' => 'Cheras',
                'category' => 'jelajah-sekolah-tahfiz',
                'status' => ApplicationStatus::CadanganTarikh,
                'topic' => 'Modul adab menuntut ilmu untuk pelajar tingkatan 4 dan 5 sebelum peperiksaan.',
                'consent' => VenueConsent::SudahBersetuju,
                'estimate' => AttendeeEstimate::F301_500,
                'audience' => TargetAudience::Pelajar,
                'days' => 38,
                'submitted' => 9,
                'background' => ApplicantBackground::WakilSekolah,
            ],
            [
                'user' => null,
                'name' => 'Tuan Haji Sulaiman bin Yaakob',
                'phone' => '60111122334',
                'email' => 'sulaiman.demo@example.com',
                'venue' => 'Masjid Al-Muttaqin Miri (Demo)',
                'state' => 'SWK', 'district' => 'Miri',
                'category' => 'jelajah-masjid',
                'status' => ApplicationStatus::Diterima,
                'topic' => 'Program memakmurkan masjid — menarik anak muda ke masjid selepas Maghrib.',
                'consent' => VenueConsent::SudahBersetuju,
                'estimate' => AttendeeEstimate::F101_300,
                'audience' => TargetAudience::Umum,
                'days' => 90,
                'submitted' => 2,
                'background' => ApplicantBackground::WakilMasjid,
            ],
            [
                'user' => null,
                'name' => 'Cik Norhayati binti Jamal',
                'phone' => '60175554433',
                'email' => 'norhayati.demo@example.com',
                'venue' => 'Balai Raya Kampung Tersusun Sitiawan (Demo)',
                'state' => 'PRK', 'district' => 'Sitiawan',
                'category' => 'jelajah-solat',
                'status' => ApplicationStatus::Ditangguhkan,
                'topic' => 'Bengkel qadha solat untuk warga emas kampung.',
                'consent' => VenueConsent::BelumBersetuju,
                'estimate' => AttendeeEstimate::Bawah50,
                'audience' => TargetAudience::WargaEmas,
                'days' => 55,
                'submitted' => 30,
                'background' => ApplicantBackground::OrangAwam,
            ],
        ];

        foreach ($rows as $row) {
            $state = $this->state($row['state']);
            $district = $this->district($state, $row['district']);
            $user = $row['user'] ?? null;
            $submittedAt = now()->subDays($row['submitted']);

            $application = Application::updateOrCreate(
                ['venue_name' => $row['venue']],
                [
                    'reference_no' => $references->application(),
                    'user_id' => $user?->id,
                    'applicant_name' => $user?->name ?? $row['name'],
                    'applicant_phone' => $user?->phone ?? $row['phone'],
                    'applicant_email' => $user?->email ?? $row['email'],
                    'background' => $row['background'] ?? $user?->mobilizerProfile?->background ?? ApplicantBackground::OrangAwam,
                    'state_id' => $state->id,
                    'district_id' => $district?->id,
                    'venue_address' => 'Alamat demo, '.($district?->name ?? $state->name).', '.$state->name,
                    'venue_consent' => $row['consent'],
                    'venue_pic_name' => 'PIC Demo',
                    'venue_pic_phone' => '601'.random_int(10000000, 99999999),
                    'event_category_id' => $this->category($row['category'])->id,
                    'topic' => $row['topic'],
                    'preferred_date_1' => now()->addDays($row['days'])->toDateString(),
                    'preferred_date_2' => now()->addDays($row['days'] + 7)->toDateString(),
                    'estimated_attendees' => $row['estimate'],
                    'target_audience' => $row['audience'],
                    'status' => $row['status'],
                    'status_changed_at' => $submittedAt->copy()->addDays(2),
                    'assigned_admin_id' => $admin->id,
                    'submitted_at' => $submittedAt,
                    'privacy_consent_at' => $submittedAt,
                    'created_at' => $submittedAt,
                ],
            );

            if ($application->statusHistories()->count() === 0) {
                ApplicationStatusHistory::create([
                    'application_id' => $application->id,
                    'to_status' => ApplicationStatus::Diterima->value,
                    'public_note' => ApplicationStatus::Diterima->description(),
                    'created_at' => $submittedAt,
                    'updated_at' => $submittedAt,
                ]);

                if ($row['status'] !== ApplicationStatus::Diterima) {
                    ApplicationStatusHistory::create([
                        'application_id' => $application->id,
                        'from_status' => ApplicationStatus::Diterima->value,
                        'to_status' => $row['status']->value,
                        'user_id' => $admin->id,
                        'public_note' => $row['status']->description(),
                        'internal_note' => 'Nota dalaman demo — tidak dipaparkan kepada Penggerak.',
                        'created_at' => $submittedAt->copy()->addDays(2),
                        'updated_at' => $submittedAt->copy()->addDays(2),
                    ]);
                }

                ApplicationNote::create([
                    'application_id' => $application->id,
                    'user_id' => $admin->id,
                    'body' => 'Dihubungi melalui WhatsApp. Pemohon responsif dan lokasi sesuai. (Nota demo)',
                    'is_internal' => true,
                    'channel' => 'whatsapp',
                    'occurred_at' => $submittedAt->copy()->addDay(),
                ]);
            }
        }
    }

    // ── Program ──────────────────────────────────────────────────────

    /** @return \Illuminate\Support\Collection<int, Event> */
    private function createEvents(array $mobilizers): \Illuminate\Support\Collection
    {
        $references = app(ReferenceGenerator::class);
        $events = collect();

        foreach ($this->eventBlueprints() as $row) {
            $state = $this->state($row['state']);
            $district = $this->district($state, $row['district']);
            $startsAt = $row['starts_at'];

            $venue = \App\Models\Venue::updateOrCreate(
                ['name' => $row['venue'], 'state_id' => $state->id, 'district_id' => $district?->id],
                [
                    'address' => $row['venue'].', '.($district?->name ?? '').', '.$state->name,
                    'postcode' => (string) random_int(10000, 89999),
                    'pic_name' => 'Setiausaha '.Str::before($row['venue'], ' '),
                    'pic_phone' => '601'.random_int(10000000, 99999999),
                    'parking_info' => 'Parkir percuma tersedia di perkarangan. Sila tiba 20 minit awal pada waktu puncak.',
                ],
            );

            $event = Event::updateOrCreate(
                ['slug' => Str::slug($row['title']).'-'.$startsAt->year.'-'.$row['code']],
                [
                    'short_code' => $references->eventShortCode(),
                    'event_category_id' => $this->category($row['category'])->id,
                    'speaker_id' => $this->speakers[$row['speaker']]->id,
                    'venue_id' => $venue->id,
                    'state_id' => $state->id,
                    'district_id' => $district?->id,
                    'title' => $row['title'],
                    'theme' => $row['theme'],
                    'description' => $row['description'],
                    'starts_at' => $startsAt,
                    'ends_at' => $startsAt->copy()->addHours(2)->addMinutes(30),
                    'doors_open_at' => $startsAt->copy()->subMinutes(30),
                    'pricing_mode' => $row['pricing'],
                    'price' => $row['price'] ?? 0,
                    'capacity' => $row['capacity'],
                    'allow_waiting_list' => true,
                    'invite_code' => $row['pricing'] === PricingMode::JemputanSahaja ? 'BEDAIE'.strtoupper(Str::random(4)) : null,
                    'registration_opens_at' => $startsAt->copy()->subDays(30),
                    'registration_closes_at' => $startsAt->copy()->subHours(2),
                    'status' => $row['status'],
                    'target_audience' => $row['audience'],
                    'organizer_name' => $row['venue'],
                    'contact_phone' => config('jelajah.support.phone'),
                    'certificate_enabled' => true,
                    'certificate_template_id' => \App\Models\CertificateTemplate::where('is_default', true)->value('id'),
                    'learning_hours' => 2.5,
                    'tentative' => [
                        ['masa' => $startsAt->copy()->subMinutes(30)->format('g:ia'), 'aktiviti' => 'Pendaftaran & imbas QR kehadiran'],
                        ['masa' => $startsAt->format('g:ia'), 'aktiviti' => 'Bacaan doa & ucapan aluan'],
                        ['masa' => $startsAt->copy()->addMinutes(15)->format('g:ia'), 'aktiviti' => 'Sesi pengisian utama'],
                        ['masa' => $startsAt->copy()->addMinutes(105)->format('g:ia'), 'aktiviti' => 'Sesi soal jawab'],
                        ['masa' => $startsAt->copy()->addMinutes(150)->format('g:ia'), 'aktiviti' => 'Penutup & bersurai'],
                    ],
                    'faqs' => [
                        ['soalan' => 'Adakah program ini terbuka kepada umum?', 'jawapan' => 'Ya, terbuka kepada semua yang berdaftar melalui pautan rasmi. Tempat adalah terhad.'],
                        ['soalan' => 'Bolehkah saya membawa ahli keluarga?', 'jawapan' => 'Boleh. Sila tambah nama mereka semasa mendaftar supaya tempat mencukupi.'],
                        ['soalan' => 'Adakah sijil disediakan?', 'jawapan' => 'Ya. Sijil digital dikeluarkan automatik kepada peserta yang mengimbas QR kehadiran.'],
                    ],
                    'parking_info' => $venue->parking_info,
                    'published_at' => $startsAt->copy()->subDays(30),
                    'completed_at' => $row['status'] === EventStatus::Selesai ? $startsAt->copy()->addHours(3) : null,
                ],
            );

            if (isset($row['mobilizer']) && isset($mobilizers[$row['mobilizer']])) {
                $event->mobilizers()->syncWithoutDetaching([
                    $mobilizers[$row['mobilizer']]->id => ['role' => 'utama'],
                ]);
            }

            if ($event->qrTokens_count ?? true) {
                \App\Models\QrToken::firstOrCreate(
                    ['tokenable_type' => Event::class, 'tokenable_id' => $event->id, 'purpose' => 'pendaftaran'],
                    [],
                );
            }

            $events->push($event);
        }

        return $events;
    }

    private function eventBlueprints(): array
    {
        $desc = fn (string $place, string $what) => "BeDaie Jelajah hadir di {$place} membawa {$what}.\n\nProgram ini dianjurkan dengan kerjasama komuniti setempat dan terbuka kepada semua yang berdaftar. Dari Masjid ke Masjid, Dari Hati ke Hati.";

        return [
            // ── 8 program SELESAI ──
            ['code' => 'a1', 'title' => 'Jelajah Solat: Masjid Al-Hidayah Kubang Kerian', 'theme' => 'Perbaiki Solat, Perbaiki Hidup',
                'state' => 'KEL', 'district' => 'Kota Bharu', 'venue' => 'Masjid Al-Hidayah Kubang Kerian (Demo)',
                'category' => 'jelajah-solat', 'speaker' => 0, 'audience' => TargetAudience::Umum,
                'starts_at' => now()->subDays(96)->setTime(20, 30), 'capacity' => 300, 'pricing' => PricingMode::Percuma,
                'status' => EventStatus::Selesai, 'mobilizer' => 'kelantan',
                'description' => $desc('Masjid Al-Hidayah Kubang Kerian', 'bengkel praktikal solat dan qadha solat mengikut Mazhab Syafi\'i')],

            ['code' => 'a2', 'title' => 'Jelajah Wanita: Surau An-Nur Bangi', 'theme' => 'Fiqh Muslimah Untukmu',
                'state' => 'SGR', 'district' => 'Kajang', 'venue' => 'Surau An-Nur Bandar Baru Bangi (Demo)',
                'category' => 'jelajah-wanita', 'speaker' => 1, 'audience' => TargetAudience::Wanita,
                'starts_at' => now()->subDays(78)->setTime(9, 0), 'capacity' => 150, 'pricing' => PricingMode::Percuma,
                'status' => EventStatus::Selesai, 'mobilizer' => 'selangor',
                'description' => $desc('Surau An-Nur Bandar Baru Bangi', 'kelas fiqh darah wanita — haid, nifas dan istihadah')],

            ['code' => 'a3', 'title' => 'Jelajah Al-Quran: Maahad Tahfiz Nurul Iman', 'theme' => 'Tuhfatul Athfal & Makhraj',
                'state' => 'JHR', 'district' => 'Batu Pahat', 'venue' => 'Maahad Tahfiz Nurul Iman (Demo)',
                'category' => 'jelajah-al-quran', 'speaker' => 2, 'audience' => TargetAudience::Pelajar,
                'starts_at' => now()->subDays(64)->setTime(10, 0), 'capacity' => 120, 'pricing' => PricingMode::Percuma,
                'status' => EventStatus::Selesai, 'mobilizer' => 'johor',
                'description' => $desc('Maahad Tahfiz Nurul Iman', 'bengkel tajwid dan pembetulan makhraj untuk pelajar tahfiz')],

            ['code' => 'a4', 'title' => 'Jelajah Keluarga: Masjid Sultan Ahmad Shah Kuantan', 'theme' => 'Rumah Tangga Berkat',
                'state' => 'PHG', 'district' => 'Kuantan', 'venue' => 'Masjid Sultan Ahmad Shah Kuantan (Demo)',
                'category' => 'jelajah-keluarga', 'speaker' => 0, 'audience' => TargetAudience::Keluarga,
                'starts_at' => now()->subDays(52)->setTime(20, 30), 'capacity' => 250, 'pricing' => PricingMode::Percuma,
                'status' => EventStatus::Selesai,
                'description' => $desc('Masjid Sultan Ahmad Shah Kuantan', 'sesi rumah tangga dan parenting dari perspektif Islam')],

            ['code' => 'a5', 'title' => 'Jelajah Anak Muda: Dewan Komuniti Ipoh', 'theme' => 'Jati Diri Anak Muda Islam',
                'state' => 'PRK', 'district' => 'Ipoh', 'venue' => 'Dewan Komuniti Bandar Ipoh (Demo)',
                'category' => 'jelajah-anak-muda', 'speaker' => 2, 'audience' => TargetAudience::AnakMuda,
                'starts_at' => now()->subDays(41)->setTime(15, 0), 'capacity' => 200, 'pricing' => PricingMode::Percuma,
                'status' => EventStatus::Selesai,
                'description' => $desc('Dewan Komuniti Bandar Ipoh', 'sesi santai bersama anak muda tentang jati diri dan hala tuju hidup')],

            ['code' => 'a6', 'title' => 'Jelajah Ilmu: Masjid Terapung Kuala Terengganu', 'theme' => 'Fardhu Ain Harian',
                'state' => 'TRG', 'district' => 'Kuala Terengganu', 'venue' => 'Masjid Terapung Kuala Ibai (Demo)',
                'category' => 'jelajah-ilmu', 'speaker' => 0, 'audience' => TargetAudience::Umum,
                'starts_at' => now()->subDays(33)->setTime(20, 30), 'capacity' => 280, 'pricing' => PricingMode::Percuma,
                'status' => EventStatus::Selesai,
                'description' => $desc('Masjid Terapung Kuala Ibai', 'pengisian fardhu ain harian dan fiqh bersuci')],

            ['code' => 'a7', 'title' => 'Jelajah Masjid: Masjid Al-Muttaqin Kuching', 'theme' => 'Memakmurkan Rumah Allah',
                'state' => 'SWK', 'district' => 'Kuching', 'venue' => 'Masjid Al-Muttaqin Kuching (Demo)',
                'category' => 'jelajah-masjid', 'speaker' => 1, 'audience' => TargetAudience::Umum,
                'starts_at' => now()->subDays(24)->setTime(20, 0), 'capacity' => 180, 'pricing' => PricingMode::Percuma,
                'status' => EventStatus::Selesai,
                'description' => $desc('Masjid Al-Muttaqin Kuching', 'program memakmurkan masjid bersama jawatankuasa kariah')],

            ['code' => 'a8', 'title' => 'Jelajah Korporat: Menara Ilmu Kota Kinabalu', 'theme' => 'Keberkatan Rezeki',
                'state' => 'SBH', 'district' => 'Kota Kinabalu', 'venue' => 'Menara Ilmu Kota Kinabalu (Demo)',
                'category' => 'jelajah-korporat', 'speaker' => 0, 'audience' => TargetAudience::Organisasi,
                'starts_at' => now()->subDays(12)->setTime(14, 30), 'capacity' => 90, 'pricing' => PricingMode::Berbayar,
                'price' => 45, 'status' => EventStatus::Selesai,
                'description' => $desc('Menara Ilmu Kota Kinabalu', 'sesi korporat tentang keberkatan rezeki dan solat di tempat kerja')],

            // ── 2 program SEDANG DIBUKA (hampir, pendaftaran hangat) ──
            ['code' => 'b1', 'title' => 'Jelajah Ilmu: Masjid Negeri Shah Alam', 'theme' => 'Bidayatul Hidayah',
                'state' => 'SGR', 'district' => 'Shah Alam', 'venue' => 'Masjid Sultan Salahuddin Abdul Aziz Shah (Demo)',
                'category' => 'jelajah-ilmu', 'speaker' => 0, 'audience' => TargetAudience::Umum,
                'starts_at' => now()->addDays(9)->setTime(20, 30), 'capacity' => 300, 'pricing' => PricingMode::Percuma,
                'status' => EventStatus::Diterbitkan, 'mobilizer' => 'selangor',
                'description' => $desc('Masjid Sultan Salahuddin Abdul Aziz Shah', 'pengajian kitab Bidayatul Hidayah karya Imam Al-Ghazali')],

            ['code' => 'b2', 'title' => 'Jelajah Wanita: Dewan Muslimat Kota Bharu', 'theme' => 'Untukmu Wanita',
                'state' => 'KEL', 'district' => 'Kota Bharu', 'venue' => 'Dewan Muslimat Kota Bharu (Demo)',
                'category' => 'jelajah-wanita', 'speaker' => 1, 'audience' => TargetAudience::Wanita,
                'starts_at' => now()->addDays(16)->setTime(9, 30), 'capacity' => 120, 'pricing' => PricingMode::Percuma,
                'status' => EventStatus::Diterbitkan, 'mobilizer' => 'kelantan',
                'description' => $desc('Dewan Muslimat Kota Bharu', 'kelas fiqh dan tasawuf muslimah bersama Ustazah BeDaie')],

            // ── 4 program AKAN DATANG ──
            ['code' => 'c1', 'title' => 'Jelajah Al-Quran: Masjid Kapitan Keling', 'theme' => 'Tadabbur Surah Al-Mulk',
                'state' => 'PNG', 'district' => 'Georgetown', 'venue' => 'Masjid Kapitan Keling Georgetown (Demo)',
                'category' => 'jelajah-al-quran', 'speaker' => 2, 'audience' => TargetAudience::Umum,
                'starts_at' => now()->addDays(29)->setTime(20, 30), 'capacity' => 200, 'pricing' => PricingMode::Percuma,
                'status' => EventStatus::Diterbitkan,
                'description' => $desc('Masjid Kapitan Keling Georgetown', 'tadabbur Surah Al-Mulk — menelusuri hikmah surah penyelamat')],

            ['code' => 'c2', 'title' => 'Jelajah Keluarga: Seminar Rahsia Suami Isteri', 'theme' => 'Sentuhan Kasih',
                'state' => 'JHR', 'district' => 'Johor Bahru', 'venue' => 'Dewan Jubli Intan Johor Bahru (Demo)',
                'category' => 'jelajah-keluarga', 'speaker' => 1, 'audience' => TargetAudience::Keluarga,
                'starts_at' => now()->addDays(38)->setTime(9, 0), 'capacity' => 150, 'pricing' => PricingMode::Berbayar,
                'price' => 60, 'status' => EventStatus::Diterbitkan, 'mobilizer' => 'johor',
                'description' => $desc('Dewan Jubli Intan Johor Bahru', 'seminar sehari khusus pasangan suami isteri')],

            ['code' => 'c3', 'title' => 'Jelajah Sekolah: SMK Seri Bintang Utara', 'theme' => 'Adab Sebelum Ilmu',
                'state' => 'KUL', 'district' => 'Cheras', 'venue' => 'SMK Seri Bintang Utara (Demo)',
                'category' => 'jelajah-sekolah-tahfiz', 'speaker' => 2, 'audience' => TargetAudience::Pelajar,
                'starts_at' => now()->addDays(47)->setTime(10, 30), 'capacity' => 400, 'pricing' => PricingMode::JemputanSahaja,
                'status' => EventStatus::Diterbitkan,
                'description' => $desc('SMK Seri Bintang Utara', 'modul adab menuntut ilmu untuk pelajar menengah atas')],

            ['code' => 'c4', 'title' => 'Jelajah Prihatin: Felda Chini Pekan', 'theme' => 'Ilmu Sampai ke Pelosok',
                'state' => 'PHG', 'district' => 'Pekan', 'venue' => 'Dewan Serbaguna Felda Chini 3 (Demo)',
                'category' => 'jelajah-prihatin', 'speaker' => 0, 'audience' => TargetAudience::Umum,
                'starts_at' => now()->addDays(58)->setTime(20, 0), 'capacity' => 180, 'pricing' => PricingMode::Ditaja,
                'status' => EventStatus::Diterbitkan,
                'description' => $desc('Dewan Serbaguna Felda Chini 3', 'program santunan ilmu untuk komuniti Felda — ditaja sepenuhnya')],
        ];
    }

    // ── Pendaftaran, kehadiran & sijil ───────────────────────────────

    private function createRegistrations(\Illuminate\Support\Collection $events, \Illuminate\Support\Collection $participants, User $admin): void
    {
        $service = app(RegistrationService::class);
        $pool = $participants->values();

        foreach ($events as $index => $event) {
            $isPast = $event->starts_at->isPast();

            // Program lepas: isian tinggi. Program akan datang: isian sederhana.
            $target = $isPast
                ? (int) round($event->capacity * random_int(62, 88) / 100)
                : (int) round($event->capacity * random_int(28, 74) / 100);

            $target = min($target, $pool->count());

            // Satu program sengaja diisi penuh supaya senarai menunggu boleh diuji.
            $forceWaitlist = $event->short_code && $index === 9; // program "sedang dibuka" kedua
            if ($forceWaitlist) {
                $target = min($pool->count(), $event->capacity + 6);
            }

            $offset = ($index * 11) % max(1, $pool->count());
            $chosen = $pool->slice($offset)->concat($pool->slice(0, $offset))->take($target);

            foreach ($chosen as $i => $user) {
                if ($event->registrations()->where('phone', $user->phone)->exists()) {
                    continue;
                }

                $guests = [];
                if ($i % 6 === 0) {
                    $guests[] = ['name' => 'Ahli Keluarga '.($i + 1), 'gender' => 'perempuan', 'age_group' => 'dewasa'];
                }
                if ($i % 11 === 0) {
                    $guests[] = ['name' => 'Anak '.($i + 1), 'gender' => 'lelaki', 'age_group' => 'kanak_kanak'];
                }

                try {
                    $registration = $service->register($event, [
                        'name' => $user->name,
                        'phone' => $user->phone,
                        'email' => $user->email,
                        'gender' => $user->gender,
                        'state_id' => $user->state_id,
                        'district_id' => $user->district_id,
                        'guests' => $guests,
                        'invite_code' => $event->invite_code,
                    ], $user, $isPast ? 'admin' : 'online');

                    $registration->forceFill([
                        'registered_at' => $event->starts_at->copy()->subDays(random_int(2, 25)),
                    ])->save();
                } catch (\Throwable $e) {
                    continue; // penuh atau pendaftaran ditutup — dijangka
                }
            }

            // Bayaran demo untuk program berbayar
            if ($event->pricing_mode === PricingMode::Berbayar) {
                foreach ($event->registrations()->with('payment')->get() as $k => $registration) {
                    $payment = $registration->payment;
                    if (! $payment) {
                        continue;
                    }

                    if ($k % 7 === 0) {
                        continue; // kekal menunggu pengesahan
                    }

                    $payment->update([
                        'status' => PaymentStatus::Berjaya,
                        'paid_at' => $registration->registered_at,
                        'verified_by' => $admin->id,
                        'gateway_reference' => 'DEMO-'.strtoupper(Str::random(10)),
                    ]);

                    $service->confirm($registration);
                }
            }

            $service->refreshCounts($event);
        }
    }

    private function completePastEvents(\Illuminate\Support\Collection $events, User $admin): void
    {
        $attendance = app(AttendanceService::class);
        $certificates = app(CertificateService::class);
        $lifecycle = app(EventLifecycleService::class);

        foreach ($events as $event) {
            if ($event->status !== EventStatus::Selesai) {
                continue;
            }

            $registrations = $event->registrations()->active()->orderBy('id')->get();

            foreach ($registrations as $i => $registration) {
                if ($i % 9 === 0) {
                    continue; // ~11% tidak hadir — supaya kelayakan sijil boleh diuji
                }

                $method = $i % 13 === 0 ? AttendanceMethod::Manual : AttendanceMethod::Qr;
                $result = $attendance->checkIn($registration, $admin, $method);

                $result->record?->forceFill([
                    'checked_in_at' => $event->starts_at->copy()->subMinutes(random_int(5, 40)),
                ])->save();
            }

            // Beberapa peserta walk-in
            for ($w = 1; $w <= 4; $w++) {
                $phone = '6019'.random_int(1000000, 9999999);
                if ($event->registrations()->where('phone', $phone)->exists()) {
                    continue;
                }

                $walkIn = Registration::create([
                    'reference_no' => app(ReferenceGenerator::class)->registration(),
                    'event_id' => $event->id,
                    'name' => 'Peserta Walk-in '.$w.' ('.$event->short_code.')',
                    'phone' => $phone,
                    'gender' => $w % 2 ? 'lelaki' : 'perempuan',
                    'state_id' => $event->state_id,
                    'district_id' => $event->district_id,
                    'status' => RegistrationStatus::Disahkan,
                    'guests_count' => 0,
                    'source' => 'walk_in',
                    'registered_at' => $event->starts_at,
                    'confirmed_at' => $event->starts_at,
                    'privacy_consent_at' => $event->starts_at,
                ]);

                \App\Models\QrToken::create([
                    'tokenable_type' => Registration::class,
                    'tokenable_id' => $walkIn->id,
                    'purpose' => 'checkin',
                ]);

                $attendance->checkIn($walkIn, $admin, AttendanceMethod::WalkIn);
            }

            // Maklum balas peserta
            $attended = $event->registrations()->attended()->get();
            foreach ($attended as $i => $registration) {
                if ($i % 3 !== 0) {
                    continue;
                }

                Feedback::updateOrCreate(
                    ['registration_id' => $registration->id],
                    [
                        'event_id' => $event->id,
                        'rating' => [5, 5, 4, 5, 4, 3][$i % 6],
                        'most_beneficial' => [
                            'Penerangan yang sangat mudah difahami dan terus boleh diamalkan.',
                            'Sesi soal jawab yang menjawab kekeliruan saya selama ini.',
                            'Contoh praktikal yang diberikan sangat membantu.',
                            'Penyampaian tenang dan tidak menghukum.',
                        ][$i % 4],
                        'next_topic' => ['Fiqh muamalat', 'Tafsir juzuk 30', 'Parenting anak remaja', 'Qadha solat', 'Tazkiyatun nafs'][$i % 5],
                        'wants_advanced' => $i % 2 === 0,
                        'is_published' => $i % 5 === 0,
                    ],
                );
            }

            $certificates->issueForEvent($event);

            foreach ($event->mobilizers as $mobilizer) {
                $certificates->issueMobilizerCertificate($event, $mobilizer);
            }

            if ($event->organizer_name) {
                $certificates->issuePartnerCertificate($event, $event->organizer_name);
            }

            app(RegistrationService::class)->refreshCounts($event);
        }
    }

    /** Poster rasmi untuk setiap program — muka depan tidak sepatutnya kosong. */
    private function createPosters(\Illuminate\Support\Collection $events): void
    {
        $posters = app(\App\Services\PosterGenerator::class);

        if (! $posters->available()) {
            return;
        }

        foreach ($events as $event) {
            $posters->generate($event->fresh(['category', 'speaker', 'venue', 'state', 'district']));
        }
    }

    private function createTestimonials(\Illuminate\Support\Collection $events): void
    {
        $rows = [
            ['Hajah Zaleha binti Ahmad', 'Ahli Kariah, Kota Bharu', 'Baru kali ini saya faham cara qadha solat dengan betul. Penyampaian ustaz sangat sabar dan tidak menghukum sesiapa.', 5],
            ['Ustaz Kamarul bin Hashim', 'Nazir Masjid, Shah Alam', 'Proses jemputan sangat mudah. Kami hanya isi borang, pasukan BeDaie uruskan selebihnya sampai ke sijil peserta.', 5],
            ['Puan Rohana binti Ismail', 'Penggerak Jelajah, Bangi', 'Sebagai orang biasa yang bukan penganjur profesional, saya rasa sangat dibantu. Link dan poster dapat terus, tinggal kongsi di WhatsApp sahaja.', 5],
            ['Muhammad Aiman bin Zulkifli', 'Peserta, Ipoh', 'Sesi anak muda yang tak membosankan. Terus terang, ini pertama kali saya duduk sampai habis kuliah.', 4],
            ['Cikgu Norsiah binti Awang', 'Guru, Batu Pahat', 'Pelajar tahfiz kami dapat pembetulan makhraj secara individu. Sangat berbaloi dan sijil pun terus dapat.', 5],
            ['Encik Sulaiman bin Yaakob', 'Ahli JK Masjid, Kuching', 'Alhamdulillah, masjid kami penuh malam itu. Sistem QR memudahkan kami rekod kehadiran tanpa kelam-kabut.', 5],
        ];

        $completed = $events->where('status', EventStatus::Selesai)->values();

        foreach ($rows as $i => [$name, $role, $quote, $rating]) {
            Testimonial::updateOrCreate(
                ['name' => $name],
                [
                    'role_label' => $role,
                    'quote' => $quote,
                    'rating' => $rating,
                    'event_id' => $completed[$i % max(1, $completed->count())]->id ?? null,
                    'state_id' => $completed[$i % max(1, $completed->count())]->state_id ?? null,
                    'is_approved' => true,
                    'is_featured' => $i < 4,
                    'sort_order' => $i + 1,
                ],
            );
        }
    }

    private function createAreaInterest(): void
    {
        $rows = [
            ['KDH', 'Alor Setar', '05100', 'jelajah-ilmu', 9],
            ['KDH', 'Kulim', '09000', 'jelajah-solat', 5],
            ['TRG', 'Kemaman', '24000', 'jelajah-keluarga', 7],
            ['NSN', 'Seremban', '70000', 'jelajah-wanita', 12],
            ['MLK', 'Melaka Tengah', '75000', 'jelajah-ilmu', 6],
            ['PLS', 'Kangar', '01000', 'jelajah-masjid', 4],
            ['SBH', 'Sandakan', '90000', 'jelajah-al-quran', 8],
            ['SWK', 'Sibu', '96000', 'jelajah-anak-muda', 5],
            ['PRK', 'Taiping', '34000', 'jelajah-solat', 6],
            ['JHR', 'Muar', '84000', 'jelajah-keluarga', 10],
            ['LBN', 'Labuan', '87000', 'jelajah-ilmu', 3],
            ['PJY', 'Putrajaya', '62000', 'jelajah-korporat', 7],
        ];

        $names = ['Ahmad', 'Siti', 'Nurul', 'Hafiz', 'Aisyah', 'Rahim', 'Zainab', 'Iskandar', 'Maryam', 'Yusof', 'Salmah', 'Hakim'];

        foreach ($rows as $r => [$code, $districtName, $postcode, $categorySlug, $count]) {
            $state = $this->state($code);
            $district = $this->district($state, $districtName);

            for ($i = 0; $i < $count; $i++) {
                $phone = '601'.str_pad((string) (40000000 + $r * 1000 + $i * 13), 8, '0', STR_PAD_LEFT);

                AreaInterestRequest::updateOrCreate(
                    ['phone' => $phone, 'state_id' => $state->id],
                    [
                        'name' => $names[($r + $i) % count($names)].' (Demo)',
                        'district_id' => $district?->id,
                        'postcode' => $postcode,
                        'event_category_id' => $this->category($categorySlug)->id,
                        'status' => $i === 0 ? 'disemak' : 'baharu',
                        'created_at' => now()->subDays(random_int(1, 120)),
                    ],
                );
            }
        }
    }

    // ── Pembantu ─────────────────────────────────────────────────────

    private function state(string $code): State
    {
        return $this->states[$code];
    }

    private function district(State $state, string $name): ?District
    {
        return $state->districts->firstWhere('name', $name) ?? $state->districts->first();
    }

    private function category(string $slug): EventCategory
    {
        return $this->categories[$slug];
    }
}
