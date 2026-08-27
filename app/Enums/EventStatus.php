<?php

namespace App\Enums;

enum EventStatus: string
{
    case Draf = 'draf';
    case Diterbitkan = 'diterbitkan';
    case Berlangsung = 'berlangsung';
    case Selesai = 'selesai';
    case Ditangguhkan = 'ditangguhkan';
    case Dibatalkan = 'dibatalkan';

    public function label(): string
    {
        return match ($this) {
            self::Draf => 'Draf',
            self::Diterbitkan => 'Diterbitkan',
            self::Berlangsung => 'Sedang Berlangsung',
            self::Selesai => 'Selesai',
            self::Ditangguhkan => 'Ditangguhkan',
            self::Dibatalkan => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draf => 'quiet',
            self::Diterbitkan => 'soft',
            self::Berlangsung => 'strong',
            self::Selesai => 'solid',
            self::Ditangguhkan => 'quiet',
            self::Dibatalkan => 'alert',
        };
    }

    public function isPublic(): bool
    {
        return in_array($this, [self::Diterbitkan, self::Berlangsung, self::Selesai], true);
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
