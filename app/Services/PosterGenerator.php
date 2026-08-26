<?php

namespace App\Services;

use App\Enums\PosterStyle;
use App\Models\Event;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Menjana poster program daripada templat rasmi BeDaie.
 *
 * Dilukis terus dengan ImagickDraw, bukan SVG: pemasangan ImageMagick tanpa
 * librsvg jatuh balik ke MSVG yang tidak menyokong SVG bersarang, corak atau
 * gradien jejari. Fon DejaVu dibundel bersama projek supaya poster kelihatan
 * sama pada mesin pembangunan dan pelayan produksi.
 *
 * Penggerak tidak pernah memilih reka bentuk — setiap program mendapat poster
 * yang konsisten daripada templat yang sama.
 */
class PosterGenerator
{
    private const W = 1080;

    private const H = 1350;   // 4:5 — sesuai untuk WhatsApp, Instagram dan cetakan

    private const NAVY = '#0A083B';

    private const NAVY_DEEP = '#241B63';

    private const BRAND = '#8875FF';

    private const BRAND_SOFT = '#B6AAFF';

    private const MUTED = '#8B88B5';

    private const CREAM = '#FAF9F6';

    public function available(): bool
    {
        return extension_loaded('imagick') && is_file($this->font('sans'));
    }

    /** Menjana dan menyimpan poster; memulangkan laluan storan relatif. */
    public function generate(Event $event, ?PosterStyle $style = null): ?string
    {
        if (! $this->available()) {
            return null;
        }

        $style ??= $event->poster_style ?? PosterStyle::Klasik;

        try {
            $canvas = $this->compose($event, $style);
            $path = "poster/{$event->short_code}/poster.png";

            Storage::disk('public')->put($path, $canvas->getImageBlob());

            // Kad program bernisbah 16:9. Dikarang berasingan, bukan dipotong:
            // keratan poster membawa masuk blok maklumat yang sudah diulang
            // di bawah kad, dan lencana harga bertindih pil kategori.
            $hero = $this->composeHero($event, $style);
            $heroPath = "poster/{$event->short_code}/hero.png";

            Storage::disk('public')->put($heroPath, $hero->getImageBlob());

            $hero->clear();
            $canvas->clear();
        } catch (\Throwable $e) {
            report($e);

            return null;
        }

        $event->forceFill([
            'poster_path' => $path,
            'poster_style' => $style,
            'hero_image_path' => $heroPath,
        ])->save();

        return $path;
    }

    private function compose(Event $event, PosterStyle $style): \Imagick
    {
        $ink = $style->isLight() ? self::NAVY : '#FFFFFF';
        $soft = $style->isLight() ? '#6350D1' : self::BRAND_SOFT;
        $meta = $style->isLight() ? '#55575C' : self::MUTED;
        $w = self::W;
        $h = self::H;

        $canvas = $this->background($w, $h, 0.62, $style);

        $draw = new \ImagickDraw;

        // Jalur jenama
        $draw->setFillColor(self::BRAND);
        $draw->rectangle(0, 0, $w, 10);

        $this->drawLogo($draw, $style->isLight());

        // ── Kategori ──
        $category = mb_strtoupper($event->category?->name ?? 'BeDaie Jelajah');
        $pillW = max(240, (int) ($this->textWidth($category, 21, 'sans-bold', 3.5)) + 56);

        $draw->setFillColor($style->isLight() ? 'rgba(136,117,255,0.12)' : 'rgba(255,255,255,0.10)');
        $draw->setStrokeColor($style->isLight() ? 'rgba(99,80,209,0.30)' : 'rgba(255,255,255,0.18)');
        $draw->setStrokeWidth(1);
        $draw->roundRectangle(80, 232, 80 + $pillW, 286, 27, 27);
        $draw->setStrokeWidth(0);
        $draw->setStrokeColor('transparent');

        $this->text($draw, $category, 108, 266, 21, $soft, 'sans-bold', 3.5);

        // ── Slogan ──
        $this->text($draw, 'MEMBAWA ILMU, MENGHIDUPKAN UMMAH', 80, 356, 24, $meta, 'sans', 2);

        // ── Tajuk ──
        // Gaya "Fokus" mengecilkan tajuk dan membesarkan nama penceramah,
        // kerana itulah sebab orang datang ke program tersebut.
        $titleSize = $style === PosterStyle::Fokus ? 48 : 66;
        $titleStep = $style === PosterStyle::Fokus ? 62 : 84;
        $perLine = $style === PosterStyle::Fokus ? 30 : 22;

        $y = 452;
        foreach ($this->wrap($event->title, $perLine, 3) as $line) {
            $this->text($draw, $line, 80, $y, $titleSize, $ink, 'serif-bold');
            $y += $titleStep;
        }

        if ($style === PosterStyle::Fokus && $event->speaker) {
            $y += 26;
            $this->text($draw, 'BERSAMA', 80, $y, 20, $meta, 'sans', 3);
            $y += 56;

            foreach ($this->wrap($event->speaker->name, 24, 2) as $line) {
                $this->text($draw, $line, 80, $y, 58, $ink, 'serif-bold');
                $y += 70;
            }

            if ($event->speaker->title) {
                $this->text($draw, Str::limit($event->speaker->title, 44), 80, $y + 4, 26, $soft, 'sans');
                $y += 40;
            }
        }

        // ── Tema ──
        if ($event->theme && $style !== PosterStyle::Fokus) {
            $y += 10;
            foreach ($this->wrap($event->theme, 36, 2) as $line) {
                $this->text($draw, $line, 80, $y, 34, $soft, 'sans');
                $y += 46;
            }
        }

        // ── Blok maklumat ──
        // Baris disusun mengikut keutamaan; yang bertanda pilihan digugurkan
        // dahulu apabila tajuk panjang menolak blok ini terlalu ke bawah.
        $area = trim(($event->district?->name ? $event->district->name.', ' : '').($event->state?->name ?? ''));
        $footerY = $h - 268;

        $rows = array_values(array_filter([
            ['TARIKH', $event->dateLabel(), false],
            ['MASA', $event->timeLabel(), false],
            ['LOKASI', $event->venue?->name ?? $event->locationLabel(), false],
            $area !== '' ? ['KAWASAN', $area, true] : null,
            $event->speaker && $style !== PosterStyle::Fokus
                ? ['PENCERAMAH', $event->speaker->name, true] : null,
            ['PENYERTAAN', $event->priceLabel(), false],
        ]));

        $y += 56;
        $available = $footerY - 34 - $y;

        // Gugurkan baris pilihan — yang paling awal dahulu, kerana kawasan
        // sudah tersirat dalam nama lokasi manakala penceramah ialah penarik.
        while (count($rows) > 3 && ($available / count($rows)) < 58) {
            $index = null;

            foreach ($rows as $i => $row) {
                if ($row[2] ?? false) {
                    $index = $i;
                    break;
                }
            }

            if ($index === null) {
                break;
            }

            array_splice($rows, $index, 1);
        }

        // Lantai 44px: blok mesti sentiasa berakhir di atas garis kaki,
        // walaupun gaya Fokus menolaknya jauh ke bawah.
        $rowHeight = (int) max(44, min(74, $available / max(1, count($rows))));
        $valueSize = $rowHeight < 56 ? 26 : 32;
        $valueGap = $rowHeight < 56 ? 28 : 36;

        foreach ($rows as [$label, $value]) {
            $this->text($draw, $label, 80, $y, 20, $meta, 'sans', 3);
            $this->text($draw, Str::limit($value, 36), 80, $y + $valueGap, $valueSize, $ink, 'sans-bold');
            $y += $rowHeight;
        }

        // ── Kaki ──

        $draw->setFillColor($style->isLight() ? 'rgba(10,8,59,0.18)' : 'rgba(255,255,255,0.16)');
        $draw->rectangle(80, $footerY, $w - 80, $footerY + 1);

        // Petak putih untuk QR
        $draw->setFillColor('#FFFFFF');
        $draw->roundRectangle(80, $footerY + 44, 296, $footerY + 260, 20, 20);

        $this->text($draw, 'DAFTAR SEKARANG', 336, $footerY + 108, 21, $meta, 'sans', 3);
        $this->text($draw, $event->shortUrl(), 336, $footerY + 156, 32, $ink, 'sans-bold');
        $this->text($draw, 'Tempat terhad · Anjuran BeDaie', 336, $footerY + 202, 21, $meta, 'sans');

        $canvas->drawImage($draw);
        $draw->destroy();

        // QR dikomposit sebagai raster — lebih tepat daripada melukisnya semula.
        $this->compositeQr($canvas, $event->shortUrl(), 96, $footerY + 60, 184);

        $canvas->setImageFormat('png24');
        $canvas->setImageCompressionQuality(92);

        return $canvas;
    }

    /** Imej 16:9 untuk kad program — tajuk sahaja, tanpa maklumat berulang. */
    private function composeHero(Event $event, PosterStyle $style): \Imagick
    {
        $ink = $style->isLight() ? self::NAVY : '#FFFFFF';
        $soft = $style->isLight() ? '#6350D1' : self::BRAND_SOFT;
        $w = self::W;
        $h = 608;

        $canvas = $this->background($w, $h, 0.5, $style);

        $draw = new \ImagickDraw;
        $draw->setFillColor(self::BRAND);
        $draw->rectangle(0, 0, $w, 8);

        // Tiada slogan di sini: pada saiz kad ia tidak terbaca dan berlanggar
        // dengan lencana harga yang diletak di penjuru atas kiri.

        // Tajuk berpusat menegak supaya kad kekal seimbang.
        $lines = $this->wrap($event->title, 26, 3);
        $y = (int) (($h - (count($lines) * 76)) / 2) + 64;

        foreach ($lines as $line) {
            $this->text($draw, $line, 72, $y, 58, $ink, 'serif-bold');
            $y += 76;
        }

        if ($event->theme) {
            $this->text($draw, Str::limit($event->theme, 46), 72, $y + 18, 30, $soft, 'sans');
        }

        $canvas->drawImage($draw);
        $draw->destroy();

        $canvas->setImageFormat('png24');
        $canvas->setImageCompressionQuality(90);

        return $canvas;
    }

    /**
     * Latar berjenama: gradien navy, rosette girih, siluet ruang solat,
     * dan cahaya ungu yang meliputi seluruh kanvas.
     *
     * Cahaya dijana pada saiz penuh kanvas — cahaya kecil yang dikomposit
     * meninggalkan tepi segi empat yang kelihatan seperti kecacatan.
     */
    private function background(int $w, int $h, float $rosetteScale, PosterStyle $style): \Imagick
    {
        $canvas = new \Imagick;

        if ($style->isLight()) {
            // Latar krim suam; dakwat navy. Menjimatkan dakwat cetakan
            // dan lebih mudah dibaca di bawah cahaya siang.
            $canvas->newPseudoImage($w, $h, 'gradient:#FFFFFF-'.self::CREAM);

            if ($style === PosterStyle::Terang) {
                $this->drawGirihField($canvas, $w, $h, 'rgba(136,117,255,0.13)');
                $this->drawRosette($canvas, $w * 0.80, $h * 0.30,
                    min($w, $h) * $rosetteScale, 'rgba(99,80,209,%s)');
            }

            return $canvas;
        }

        $canvas->newPseudoImage($w, $h, 'gradient:'.self::NAVY.'-'.self::NAVY_DEEP);

        $side = (int) (max($w, $h) * 2.2);
        $glow = new \Imagick;
        $glow->newPseudoImage($side, $side, 'radial-gradient:'.self::BRAND.'-'.self::NAVY);
        $glow->setImageColorspace(\Imagick::COLORSPACE_SRGB);
        $glow->evaluateImage(\Imagick::EVALUATE_MULTIPLY,
            $style === PosterStyle::Geometri ? 0.42 : 0.30, \Imagick::CHANNEL_ALL);
        $canvas->compositeImage($glow, \Imagick::COMPOSITE_SCREEN,
            (int) ($w * 0.72 - $side / 2), (int) (-$side / 2 + $h * 0.06));
        $glow->clear();

        match ($style) {
            // Corak khatam besar dan berani, rosette berkembar.
            PosterStyle::Geometri => (function () use ($canvas, $w, $h) {
                $this->drawGirihField($canvas, $w, $h, 'rgba(255,255,255,0.09)', 168);
                $this->drawRosette($canvas, $w * 0.18, $h * 0.16, min($w, $h) * 0.46);
                $this->drawRosette($canvas, $w * 0.92, $h * 0.62, min($w, $h) * 0.60);
            })(),
            // Penceramah menjadi subjek — latar sengaja tenang.
            PosterStyle::Fokus => (function () use ($canvas, $w, $h) {
                $this->drawGirihField($canvas, $w, $h);
                $this->drawRosette($canvas, $w * 0.5, $h * 0.34, min($w, $h) * 0.72);
            })(),
            default => (function () use ($canvas, $w, $h, $rosetteScale) {
                $this->drawGirihField($canvas, $w, $h);
                $this->drawRosette($canvas, $w * 0.80, $h * 0.30, min($w, $h) * $rosetteScale);
            })(),
        };

        if ($style !== PosterStyle::Geometri) {
            $this->drawSkyline($canvas, $w, $h);
        }

        return $canvas;
    }

    /**
     * Rosette girih 10-mata — geometri Islam sebenar, bukan berlian bersarang.
     * Dilukis besar dan sangat lut sinar sebagai subjek visual poster.
     */
    private function drawRosette(\Imagick $canvas, float $cx, float $cy, float $r, string $tint = 'rgba(255,255,255,%s)'): void
    {
        $draw = new \ImagickDraw;
        $draw->setFillColor('transparent');
        $n = 10;

        $ring = function (float $radius, float $opacity, float $width, int $step = 1)
            use ($draw, $cx, $cy, $n, $tint) {
            $draw->setStrokeColor(sprintf($tint, $opacity));
            $draw->setStrokeWidth($width);

            $pts = [];
            for ($i = 0; $i < $n; $i++) {
                $k = ($i * $step) % $n;
                $a = -M_PI / 2 + 2 * M_PI * $k / $n;
                $pts[] = ['x' => $cx + $radius * cos($a), 'y' => $cy + $radius * sin($a)];
            }
            $draw->polygon($pts);
        };

        // Dekagon luar, bintang {10/3} berjalin, dan teras dalam.
        $ring($r, 0.10, 2.0);
        $ring($r * 0.94, 0.14, 2.4, 3);
        $ring($r * 0.62, 0.12, 2.0, 3);
        $ring($r * 0.40, 0.16, 2.2);
        $ring($r * 0.24, 0.12, 2.0, 3);

        // Jejari halus dari teras ke bucu — ciri strapwork girih.
        $draw->setStrokeColor(sprintf($tint, '0.07'));
        $draw->setStrokeWidth(1.4);
        for ($i = 0; $i < $n; $i++) {
            $a = -M_PI / 2 + 2 * M_PI * $i / $n;
            $draw->line($cx + $r * 0.24 * cos($a), $cy + $r * 0.24 * sin($a),
                        $cx + $r * cos($a), $cy + $r * sin($a));
        }

        $canvas->drawImage($draw);
        $draw->destroy();
    }

    /**
     * Siluet ruang solat pada kaki imej — gerbang lancip tanpa kubah,
     * mengikut garis panduan jenama.
     */
    /**
     * Siluet arked masjid pada kaki imej: gerbang lancip yang lebar dan
     * rendah, tanpa kubah. Menara sengaja ditinggalkan — pada saiz ini
     * ia terbaca sebagai anak panah, bukan seni bina.
     */
    private function drawSkyline(\Imagick $canvas, int $w, int $h): void
    {
        $draw = new \ImagickDraw;
        $draw->setStrokeColor('transparent');
        $draw->setFillColor('rgba(10,8,59,0.62)');

        $base = (float) $h;
        $eaves = $h - max(34, $h * 0.045);          // garis bumbung
        $rise = max(20, $h * 0.028);                 // ketinggian gerbang

        $unit = max(190.0, $w / 5.0);
        $points = [['x' => -20.0, 'y' => $base]];
        $x = -20.0;

        while ($x < $w + $unit) {
            // Dinding rata, kemudian gerbang lancip yang lembut.
            $points[] = ['x' => $x, 'y' => $eaves];
            $points[] = ['x' => $x + $unit * 0.30, 'y' => $eaves];
            $points[] = ['x' => $x + $unit * 0.50, 'y' => $eaves - $rise];
            $points[] = ['x' => $x + $unit * 0.70, 'y' => $eaves];
            $x += $unit;
        }

        $points[] = ['x' => (float) ($w + 20), 'y' => $eaves];
        $points[] = ['x' => (float) ($w + 20), 'y' => $base];
        $draw->polygon($points);

        $canvas->drawImage($draw);
        $draw->destroy();
    }

    /** Motif girih halus — satu-satunya ornamen yang dibenarkan jenama. */
    private function drawGirihField(\Imagick $canvas, ?int $width = null, ?int $height = null, string $stroke = 'rgba(255,255,255,0.055)', int $tile = 120): void
    {
        $width ??= self::W;
        $height ??= self::H;

        $draw = new \ImagickDraw;
        $draw->setFillColor('transparent');
        $draw->setStrokeColor($stroke);
        $draw->setStrokeWidth(1.5);

        $half = $tile / 2;

        for ($x = -$tile; $x < $width + $tile; $x += $tile) {
            for ($y = -$tile; $y < $height + $tile; $y += $tile) {
                $cx = $x + $half;
                $cy = $y + $half;

                $draw->polygon([
                    ['x' => $cx, 'y' => $cy - 54], ['x' => $cx + 54, 'y' => $cy],
                    ['x' => $cx, 'y' => $cy + 54], ['x' => $cx - 54, 'y' => $cy],
                ]);
                $draw->polygon([
                    ['x' => $cx, 'y' => $cy - 30], ['x' => $cx + 30, 'y' => $cy],
                    ['x' => $cx, 'y' => $cy + 30], ['x' => $cx - 30, 'y' => $cy],
                ]);
            }
        }

        $canvas->drawImage($draw);
        $draw->destroy();
    }

    /** Tanda jenama BeDaie — sepadan dengan logo placeholder. */
    private function drawLogo(\ImagickDraw $draw, bool $light = false): void
    {
        $draw->setFillColor(self::BRAND);
        $draw->setStrokeColor('transparent');
        $draw->roundRectangle(80, 88, 156, 164, 20, 20);

        $cx = 118;
        $cy = 126;

        $draw->setFillColor('transparent');
        $draw->setStrokeColor('rgba(255,255,255,0.55)');
        $draw->setStrokeWidth(2.6);
        $draw->polygon([
            ['x' => $cx, 'y' => $cy - 24], ['x' => $cx + 24, 'y' => $cy],
            ['x' => $cx, 'y' => $cy + 24], ['x' => $cx - 24, 'y' => $cy],
        ]);

        $draw->setStrokeColor('#FFFFFF');
        $draw->polygon([
            ['x' => $cx, 'y' => $cy - 12], ['x' => $cx + 12, 'y' => $cy],
            ['x' => $cx, 'y' => $cy + 12], ['x' => $cx - 12, 'y' => $cy],
        ]);

        $draw->setStrokeWidth(0);
        $draw->setStrokeColor('transparent');

        $this->text($draw, 'BeDaie', 178, 124, 38, $light ? self::NAVY : '#FFFFFF', 'sans-bold');
        $this->text($draw, 'JELAJAH', 178, 154, 18, $light ? '#6350D1' : self::BRAND_SOFT, 'sans-bold', 7);
    }

    private function compositeQr(\Imagick $canvas, string $payload, int $x, int $y, int $size): void
    {
        $blob = (new Writer(
            new ImageRenderer(new RendererStyle($size, 1), new ImagickImageBackEnd)
        ))->writeString($payload);

        $qr = new \Imagick;
        $qr->readImageBlob($blob);
        $qr->setImageFormat('png');

        $canvas->compositeImage($qr, \Imagick::COMPOSITE_OVER, $x, $y);
        $qr->clear();
    }

    // ── Teks ─────────────────────────────────────────────────

    private function font(string $face): string
    {
        $dir = base_path('vendor/dompdf/dompdf/lib/fonts');

        return match ($face) {
            'sans-bold' => $dir.'/DejaVuSans-Bold.ttf',
            'serif-bold' => $dir.'/DejaVuSerif-Bold.ttf',
            'serif' => $dir.'/DejaVuSerif.ttf',
            default => $dir.'/DejaVuSans.ttf',
        };
    }

    private function text(
        \ImagickDraw $draw,
        string $value,
        int $x,
        int $y,
        int $size,
        string $colour,
        string $face = 'sans',
        float $tracking = 0,
    ): void {
        $draw->setFont($this->font($face));
        $draw->setFontSize($size);
        $draw->setFillColor($colour);
        $draw->setStrokeColor('transparent');
        $draw->setTextKerning($tracking);
        $draw->annotation($x, $y, $value);
        $draw->setTextKerning(0);
    }

    private function textWidth(string $value, int $size, string $face, float $tracking = 0): float
    {
        // Anggaran cukup untuk saiz pil; mengelak kos metrik penuh Imagick.
        return (mb_strlen($value) * $size * 0.62) + (mb_strlen($value) * $tracking);
    }

    /**
     * Memecahkan teks pada sempadan perkataan supaya tajuk panjang
     * tidak terkeluar dari poster.
     *
     * @return array<int, string>
     */
    private function wrap(string $text, int $perLine, int $maxLines): array
    {
        $words = preg_split('/\s+/', trim($text)) ?: [];
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            $candidate = $current === '' ? $word : $current.' '.$word;

            if (mb_strlen($candidate) <= $perLine) {
                $current = $candidate;

                continue;
            }

            if ($current !== '') {
                $lines[] = $current;
                $current = '';
            }

            if (count($lines) >= $maxLines) {
                break;
            }

            $current = $word;
        }

        if ($current !== '' && count($lines) < $maxLines) {
            $lines[] = $current;
        }

        if (count($lines) === $maxLines && mb_strlen(implode(' ', $lines)) < mb_strlen($text)) {
            $lines[$maxLines - 1] = rtrim(mb_substr($lines[$maxLines - 1], 0, $perLine - 1)).'…';
        }

        return $lines ?: [$text];
    }
}
