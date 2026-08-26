<?php

namespace App\Enums;

enum RegistrationStatus: string
{
    case MenungguPengesahan = 'menunggu_pengesahan';
    case Disahkan = 'disahkan';
    case SenaraiMenunggu = 'senarai_menunggu';
    case Dibatalkan = 'dibatalkan';
    case Ditolak = 'ditolak';

    public function label(): string
    {
        return match ($this) {
            self::MenungguPengesahan => 'Menunggu Pengesahan',
            self::Disahkan => 'Disahkan',
            self::SenaraiMenunggu => 'Senarai Menunggu',
            self::Dibatalkan => 'Dibatalkan',
            self::Ditolak => 'Ditolak',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::MenungguPengesahan => 'warning',
            self::Disahkan => 'success',
            self::SenaraiMenunggu => 'purple',
            self::Dibatalkan, self::Ditolak => 'danger',
        };
    }

    /** Status yang menggunakan kuota tempat duduk. */
    public function occupiesSeat(): bool
    {
        return in_array($this, [self::MenungguPengesahan, self::Disahkan], true);
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
