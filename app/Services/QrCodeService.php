<?php

namespace App\Services;

use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Storage;

class QrCodeService
{
    /** Menjana QR sebagai markup SVG (untuk paparan web). */
    public function svg(string $payload, int $size = 320, string $foreground = '#141413'): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size, 1, null, null, Fill::uniformColor(
                $this->color('#FFFFFF'), $this->color($foreground)
            )),
            new SvgImageBackEnd,
        );

        return (new Writer($renderer))->writeString($payload);
    }

    /** SVG sebagai data URI — selamat digunakan dalam <img src="…">. */
    public function svgDataUri(string $payload, int $size = 320, string $foreground = '#141413'): string
    {
        return 'data:image/svg+xml;base64,'.base64_encode($this->svg($payload, $size, $foreground));
    }

    /** PNG data URI — diperlukan oleh DomPDF yang tidak menyokong SVG. */
    public function pngDataUri(string $payload, int $size = 320, string $foreground = '#141413'): string
    {
        if (! extension_loaded('imagick')) {
            // Fallback: DomPDF akan memaparkan SVG melalui imej gagal; guna SVG data URI.
            return $this->svgDataUri($payload, $size, $foreground);
        }

        $renderer = new ImageRenderer(
            new RendererStyle($size, 1, null, null, Fill::uniformColor(
                $this->color('#FFFFFF'), $this->color($foreground)
            )),
            new ImagickImageBackEnd,
        );

        return 'data:image/png;base64,'.base64_encode((new Writer($renderer))->writeString($payload));
    }

    /** Simpan QR PNG/SVG ke storan awam dan pulangkan laluan relatif. */
    public function store(string $payload, string $path, int $size = 640): string
    {
        $contents = extension_loaded('imagick')
            ? (new Writer(new ImageRenderer(new RendererStyle($size), new ImagickImageBackEnd)))->writeString($payload)
            : $this->svg($payload, $size);

        Storage::disk('public')->put($path, $contents);

        return $path;
    }

    private function color(string $hex): Rgb
    {
        $hex = ltrim($hex, '#');
        [$r, $g, $b] = [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];

        return new Rgb((int) $r, (int) $g, (int) $b);
    }
}
