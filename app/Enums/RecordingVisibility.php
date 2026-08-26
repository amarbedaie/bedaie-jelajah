<?php

namespace App\Enums;

/** Siapa yang layak menonton rakaman sesuatu program. */
enum RecordingVisibility: string
{
    case Hadir = 'hadir';

    case Berdaftar = 'berdaftar';

    case Awam = 'awam';

    public function label(): string
    {
        return match ($this) {
            self::Hadir => 'Peserta yang hadir sahaja',
            self::Berdaftar => 'Semua yang mendaftar',
            self::Awam => 'Terbuka kepada umum',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Hadir => 'Hanya peserta yang QR kehadirannya diimbas boleh menonton.',
            self::Berdaftar => 'Sesiapa yang mendaftar boleh menonton, walaupun tidak hadir.',
            self::Awam => 'Sesiapa sahaja boleh menonton tanpa mendaftar.',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Hadir => 'success',
            self::Berdaftar => 'purple',
            self::Awam => 'grey',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
