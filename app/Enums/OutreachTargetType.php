<?php

namespace App\Enums;

enum OutreachTargetType: string
{
    case Masjid = 'masjid';

    case Surau = 'surau';

    case Sekolah = 'sekolah';

    case Tahfiz = 'tahfiz';

    case Persatuan = 'persatuan';

    case Korporat = 'korporat';

    case Komuniti = 'komuniti';

    case Lain = 'lain';

    public function label(): string
    {
        return match ($this) {
            self::Masjid => 'Masjid',
            self::Surau => 'Surau',
            self::Sekolah => 'Sekolah',
            self::Tahfiz => 'Maahad / Tahfiz',
            self::Persatuan => 'Persatuan',
            self::Korporat => 'Korporat',
            self::Komuniti => 'Komuniti / Kampung',
            self::Lain => 'Lain-lain',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Masjid, self::Surau => 'mosque',
            self::Sekolah, self::Tahfiz => 'book',
            self::Korporat => 'building',
            self::Persatuan, self::Komuniti => 'users',
            self::Lain => 'pin',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
