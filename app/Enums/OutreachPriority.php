<?php

namespace App\Enums;

enum OutreachPriority: string
{
    case Tinggi = 'tinggi';

    case Sederhana = 'sederhana';

    case Rendah = 'rendah';

    public function label(): string
    {
        return match ($this) {
            self::Tinggi => 'Tinggi',
            self::Sederhana => 'Sederhana',
            self::Rendah => 'Rendah',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Tinggi => 'alert',
            self::Sederhana => 'strong',
            self::Rendah => 'quiet',
        };
    }

    public function weight(): int
    {
        return match ($this) {
            self::Tinggi => 3,
            self::Sederhana => 2,
            self::Rendah => 1,
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
