<?php

namespace App\Enums;

enum CertificateType: string
{
    case Penyertaan = 'penyertaan';
    case TamatProgram = 'tamat_program';
    case Pencapaian = 'pencapaian';
    case PenghargaanPenggerak = 'penghargaan_penggerak';
    case PenghargaanRakan = 'penghargaan_rakan';

    public function label(): string
    {
        return match ($this) {
            self::Penyertaan => 'Sijil Penyertaan',
            self::TamatProgram => 'Sijil Tamat Program',
            self::Pencapaian => 'Sijil Pencapaian',
            self::PenghargaanPenggerak => 'Sijil Penghargaan Penggerak',
            self::PenghargaanRakan => 'Sijil Penghargaan Rakan',
        };
    }

    public function statement(): string
    {
        return match ($this) {
            self::Penyertaan => 'Dengan ini disahkan bahawa',
            self::TamatProgram => 'Dengan ini disahkan bahawa',
            self::Pencapaian => 'Dengan ini disahkan bahawa',
            self::PenghargaanPenggerak => 'Setinggi-tinggi penghargaan kepada',
            self::PenghargaanRakan => 'Setinggi-tinggi penghargaan kepada',
        };
    }

    public function predicate(): string
    {
        return match ($this) {
            self::Penyertaan => 'telah menyertai program',
            self::TamatProgram => 'telah menamatkan program',
            self::Pencapaian => 'telah mencapai tahap cemerlang dalam program',
            self::PenghargaanPenggerak => 'atas sumbangan sebagai Penggerak Jelajah bagi program',
            self::PenghargaanRakan => 'atas kerjasama sebagai Rakan Jelajah bagi program',
        };
    }

    public function isAppreciation(): bool
    {
        return in_array($this, [self::PenghargaanPenggerak, self::PenghargaanRakan], true);
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
