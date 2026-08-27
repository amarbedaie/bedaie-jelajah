<?php

namespace App\Enums;

/**
 * Gaya poster rasmi BeDaie. Staf memilih satu; sistem menjananya.
 * Setiap gaya ialah susun atur yang benar-benar berbeza, bukan
 * sekadar tukar warna.
 */
enum PosterStyle: string
{
    case Klasik = 'klasik';

    case Terang = 'terang';

    case Fokus = 'fokus';

    case Geometri = 'geometri';

    case Minimalis = 'minimalis';

    public function label(): string
    {
        return match ($this) {
            self::Klasik => 'Klasik',
            self::Terang => 'Terang',
            self::Fokus => 'Fokus Penceramah',
            self::Geometri => 'Geometri',
            self::Minimalis => 'Minimalis',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Klasik => 'Kertas tulang dengan rosette girih tanah liat. Lalai untuk kebanyakan program.',
            self::Terang => 'Latar krim dengan dakwat navy. Mudah dibaca di bawah cahaya siang dan menjimatkan dakwat cetakan.',
            self::Fokus => 'Nama penceramah dibesarkan. Untuk program yang menarik kerana penceramahnya.',
            self::Geometri => 'Corak khatam besar dan berani. Untuk program anak muda dan kempen bermusim.',
            self::Minimalis => 'Hampir semua taip, garis halus sahaja. Bersih, tenang, dan paling mudah dibaca.',
        };
    }

    /** Adakah gaya ini berlatar cerah? Menentukan warna dakwat. */
    public function isLight(): bool
    {
        // Kertas ialah lalai dunia ini; hanya Fokus dan Geometri gelap,
        // untuk program malam yang mahukan kontras kuat.
        return ! in_array($this, [self::Fokus, self::Geometri], true);
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
