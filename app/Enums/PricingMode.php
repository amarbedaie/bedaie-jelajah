<?php

namespace App\Enums;

enum PricingMode: string
{
    case Percuma = 'percuma';
    case Berbayar = 'berbayar';
    case JemputanSahaja = 'jemputan';
    case SumbanganIkhlas = 'sumbangan';
    case Ditaja = 'ditaja';

    public function label(): string
    {
        return match ($this) {
            self::Percuma => 'Percuma',
            self::Berbayar => 'Berbayar',
            self::JemputanSahaja => 'Jemputan Sahaja',
            self::SumbanganIkhlas => 'Sumbangan Ikhlas',
            self::Ditaja => 'Ditaja',
        };
    }

    public function requiresPayment(): bool
    {
        return $this === self::Berbayar;
    }

    public function requiresInviteCode(): bool
    {
        return $this === self::JemputanSahaja;
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
