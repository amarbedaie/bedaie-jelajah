<?php

namespace App\Enums;

enum AttendanceMethod: string
{
    case Qr = 'qr';
    case Manual = 'manual';
    case WalkIn = 'walk_in';

    public function label(): string
    {
        return match ($this) {
            self::Qr => 'Imbas QR',
            self::Manual => 'Carian Manual',
            self::WalkIn => 'Walk-in',
        };
    }
}
