<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventGallery;
use App\Models\Partner;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Services\ImpactStatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class PageController extends Controller
{
    public function __construct(private ImpactStatsService $stats) {}

    public function categories()
    {
        return view('public.categories', [
            'categories' => EventCategory::active()->ordered()->withCount([
                'events' => fn ($q) => $q->whereNotNull('published_at'),
            ])->get(),
        ]);
    }

    public function gallery()
    {
        return view('public.gallery', [
            'photos' => EventGallery::approved()->ordered()
                ->with(['event.state', 'event.venue'])
                ->paginate(24),
            'headline' => $this->stats->headline(),
        ]);
    }

    public function partners()
    {
        return view('public.partners', [
            'partners' => Partner::active()->ordered()->get()->groupBy('type'),
            'headline' => $this->stats->headline(),
        ]);
    }

    public function about()
    {
        return view('public.about', [
            'headline' => $this->stats->headline(),
            'testimonials' => Testimonial::approved()->ordered()->limit(6)->get(),
            'recent' => Event::completed()->with(['state', 'venue'])->limit(4)->get(),
        ]);
    }

    public function privacy()
    {
        return view('public.legal', [
            'pageTitle' => 'Polisi Privasi',
            'body' => Setting::get('legal.privacy', $this->defaultPrivacy()),
            'updated' => Setting::get('legal.privacy_updated_at'),
        ]);
    }

    public function terms()
    {
        return view('public.legal', [
            'pageTitle' => 'Terma Penggunaan',
            'body' => Setting::get('legal.terms', $this->defaultTerms()),
            'updated' => Setting::get('legal.terms_updated_at'),
        ]);
    }

    /** Manifest PWA — sistem ini mobile-first dan boleh dipasang. */
    /**
     * Service worker dengan versi terikat kepada binaan semasa.
     *
     * Dihidangkan melalui laluan, bukan fail statik, supaya setiap deploy
     * menghasilkan nama cache baharu dan aset lama benar-benar dibuang.
     */
    public function serviceWorker(): Response
    {
        $script = str_replace(
            '__JELAJAH_SW_VERSION__',
            'jelajah-'.$this->buildFingerprint(),
            (string) file_get_contents(resource_path('sw.js')),
        );

        return response($script, 200, [
            'Content-Type' => 'application/javascript; charset=utf-8',
            'Cache-Control' => 'no-cache, must-revalidate',
            'Service-Worker-Allowed' => '/',
        ]);
    }

    /** Cap jari binaan aset semasa. */
    private function buildFingerprint(): string
    {
        $manifest = public_path('build/manifest.json');

        return is_file($manifest)
            ? substr(md5_file($manifest), 0, 12)
            : 'dev';
    }

    public function manifest(): JsonResponse
    {
        return response()->json([
            'name' => 'BeDaie Jelajah',
            'short_name' => 'Jelajah',
            'description' => config('jelajah.tagline'),
            'start_url' => '/',
            'scope' => '/',
            'display' => 'standalone',
            'orientation' => 'portrait-primary',
            'background_color' => '#F4F3F7',
            'theme_color' => '#8040C0',
            'lang' => 'ms',
            'icons' => [
                ['src' => asset('img/icon-192.png'), 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => asset('img/icon-512.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => asset('img/icon-maskable.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ],
            'shortcuts' => [
                ['name' => 'Jemput BeDaie', 'url' => route('jemput', absolute: false)],
                ['name' => 'Program Akan Datang', 'url' => route('program.index', absolute: false)],
                ['name' => 'Semak Sijil', 'url' => route('sijil.semak', absolute: false)],
            ],
        ])->header('Content-Type', 'application/manifest+json');
    }

    private function defaultPrivacy(): string
    {
        return <<<'MD'
        ## Ringkasan

        BeDaie Jelajah mengumpul maklumat minimum yang diperlukan untuk menguruskan
        permohonan program, pendaftaran peserta, kehadiran dan penerbitan sijil.

        ## Maklumat yang kami kumpul

        - Nama penuh (seperti yang akan dicetak pada sijil)
        - Nombor telefon/WhatsApp
        - Alamat e-mel (jika diberikan)
        - Negeri dan daerah
        - Rekod kehadiran dan maklum balas program

        ## Cara kami menggunakannya

        Maklumat digunakan semata-mata untuk menghubungi anda berkaitan program yang
        anda daftar, mengesahkan kehadiran, mengeluarkan sijil dan menyediakan laporan
        impak dalam bentuk agregat.

        ## Perkongsian

        Kami tidak menjual data anda. Nombor telefon peserta tidak dipaparkan secara
        penuh kepada Penggerak Jelajah atau umum. Laporan awam hanya memaparkan jumlah
        agregat, bukan maklumat individu.

        ## Hak anda

        Anda boleh meminta pembetulan atau pemadaman data dengan menghubungi kami.
        Pendaftaran program boleh dibatalkan sendiri melalui pautan selamat pada tiket anda.

        > Teks ini adalah draf placeholder. Ia perlu disemak oleh penasihat undang-undang
        > sebelum digunakan secara rasmi.
        MD;
    }

    private function defaultTerms(): string
    {
        return <<<'MD'
        ## Penggunaan platform

        BeDaie Jelajah disediakan untuk memudahkan komuniti menjemput program dakwah
        BeDaie ke kawasan masing-masing.

        ## Permohonan program

        Menghantar permohonan tidak menjamin kelulusan. Setiap permohonan dinilai oleh
        pasukan BeDaie berdasarkan kesesuaian lokasi, tarikh, kapasiti dan keutamaan
        jelajah. Pemilihan penceramah ditentukan sepenuhnya oleh pasukan BeDaie.

        ## Pendaftaran peserta

        Tempat adalah terhad dan diberikan mengikut giliran pendaftaran. Pendaftaran
        yang tidak hadir tanpa pembatalan boleh menjejaskan keutamaan pada program
        akan datang.

        ## Sijil

        Sijil hanya dikeluarkan kepada peserta yang kehadirannya direkodkan melalui
        sistem QR atau pengesahan admin. Sijil yang dijana semula akan menggantikan
        sijil terdahulu.

        ## Pembayaran

        Bagi program berbayar, bayaran yang telah dibuat tertakluk kepada polisi
        pemulangan yang dinyatakan pada halaman program berkenaan.

        > Teks ini adalah draf placeholder. Ia perlu disemak oleh penasihat undang-undang
        > sebelum digunakan secara rasmi.
        MD;
    }
}
