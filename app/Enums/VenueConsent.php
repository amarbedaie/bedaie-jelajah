<?php

namespace App\Enums;

enum VenueConsent: string
{
    case SudahBersetuju = 'sudah_bersetuju';
    case SedangBerbincang = 'sedang_berbincang';
    case BelumBersetuju = 'belum_bersetuju';
    case PerluBantuan = 'perlu_bantuan';

    public function label(): string
    {
        return match ($this) {
            self::SudahBersetuju => 'Sudah bersetuju',
            self::SedangBerbincang => 'Sedang berbincang',
            self::BelumBersetuju => 'Belum bersetuju',
            self::PerluBantuan => 'Saya perlukan bantuan BeDaie',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
