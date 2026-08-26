<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Penggerak = 'penggerak';
    case Peserta = 'peserta';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Pasukan BeDaie',
            self::Penggerak => 'Penggerak Jelajah',
            self::Peserta => 'Peserta',
        };
    }

    public function homeRoute(): string
    {
        return match ($this) {
            self::Admin => 'admin.dashboard',
            self::Penggerak => 'penggerak.dashboard',
            self::Peserta => 'peserta.dashboard',
        };
    }
}
