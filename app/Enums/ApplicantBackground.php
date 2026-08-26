<?php

namespace App\Enums;

enum ApplicantBackground: string
{
    case BekasPelajar = 'bekas_pelajar';
    case WakilMasjid = 'wakil_masjid';
    case AhliKariah = 'ahli_kariah';
    case WakilSekolah = 'wakil_sekolah';
    case WakilPersatuan = 'wakil_persatuan';
    case OrangAwam = 'orang_awam';
    case LainLain = 'lain_lain';

    public function label(): string
    {
        return match ($this) {
            self::BekasPelajar => 'Bekas pelajar BeDaie',
            self::WakilMasjid => 'Wakil masjid / surau',
            self::AhliKariah => 'Ahli kariah',
            self::WakilSekolah => 'Wakil sekolah / tahfiz',
            self::WakilPersatuan => 'Wakil persatuan / komuniti',
            self::OrangAwam => 'Orang awam',
            self::LainLain => 'Lain-lain',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
