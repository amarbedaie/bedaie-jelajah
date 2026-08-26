<?php

namespace App\Enums;

enum AttendeeEstimate: string
{
    case Bawah50 = 'bawah_50';
    case F50_100 = '50_100';
    case F101_300 = '101_300';
    case F301_500 = '301_500';
    case Lebih500 = 'lebih_500';

    public function label(): string
    {
        return match ($this) {
            self::Bawah50 => 'Bawah 50 orang',
            self::F50_100 => '50 - 100 orang',
            self::F101_300 => '101 - 300 orang',
            self::F301_500 => '301 - 500 orang',
            self::Lebih500 => 'Lebih 500 orang',
        };
    }

    /** Anggaran kapasiti lalai apabila program dijana. */
    public function suggestedCapacity(): int
    {
        return match ($this) {
            self::Bawah50 => 50,
            self::F50_100 => 100,
            self::F101_300 => 300,
            self::F301_500 => 500,
            self::Lebih500 => 800,
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
