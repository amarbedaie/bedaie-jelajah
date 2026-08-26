<?php

namespace App\Enums;

enum CertificateStatus: string
{
    case Dikeluarkan = 'dikeluarkan';
    case Digantikan = 'digantikan';
    case Dibatalkan = 'dibatalkan';

    public function label(): string
    {
        return match ($this) {
            self::Dikeluarkan => 'Sah',
            self::Digantikan => 'Telah Digantikan',
            self::Dibatalkan => 'Dibatalkan',
        };
    }

    public function isValid(): bool
    {
        return $this === self::Dikeluarkan;
    }
}
