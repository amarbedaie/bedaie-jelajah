<?php

namespace App\Enums;

enum RecordingType: string
{
    case Video = 'video';

    case Audio = 'audio';

    case Nota = 'nota';

    case Pautan = 'pautan';

    public function label(): string
    {
        return match ($this) {
            self::Video => 'Rakaman Video',
            self::Audio => 'Rakaman Audio',
            self::Nota => 'Nota / Slaid',
            self::Pautan => 'Pautan Luar',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Video => 'play',
            self::Audio => 'volume',
            self::Nota => 'file',
            self::Pautan => 'external',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
