<?php

namespace App\Enums;

/**
 * Gaya reka bentuk sijil. Setiap gaya membawa susunan tersendiri supaya
 * kelas berlainan (cth. Qadha Solat, Darah Wanita) boleh tampil berbeza.
 */
enum CertificateDesign: string
{
    case Klasik = 'klasik';
    case Moden = 'moden';
    case Islamik = 'islamik';
    case Minimal = 'minimal';

    public function label(): string
    {
        return match ($this) {
            self::Klasik => 'Klasik',
            self::Moden => 'Moden',
            self::Islamik => 'Islamik',
            self::Minimal => 'Minimal',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Klasik => 'Susunan tengah, bingkai berganda dan jalur warna di atas.',
            self::Moden => 'Susunan kiri, jalur warna tebal di sisi, nama besar.',
            self::Islamik => 'Susunan tengah, bingkai berhias corak geometri di setiap penjuru.',
            self::Minimal => 'Susunan tengah, tanpa bingkai — garis halus dan ruang lapang.',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }

    public static function orientations(): array
    {
        return ['landscape' => 'Landskap (A4 melintang)', 'portrait' => 'Potret (A4 menegak)'];
    }
}
