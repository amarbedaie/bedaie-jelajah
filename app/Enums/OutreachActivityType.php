<?php

namespace App\Enums;

enum OutreachActivityType: string
{
    case Panggilan = 'panggilan';

    case WhatsApp = 'whatsapp';

    case Emel = 'emel';

    case Lawatan = 'lawatan';

    case Mesyuarat = 'mesyuarat';

    case Nota = 'nota';

    case Peringkat = 'peringkat';

    public function label(): string
    {
        return match ($this) {
            self::Panggilan => 'Panggilan Telefon',
            self::WhatsApp => 'WhatsApp',
            self::Emel => 'E-mel',
            self::Lawatan => 'Lawatan',
            self::Mesyuarat => 'Mesyuarat',
            self::Nota => 'Nota',
            self::Peringkat => 'Perubahan Peringkat',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Panggilan => 'phone',
            self::WhatsApp => 'whatsapp',
            self::Emel => 'mail',
            self::Lawatan => 'pin',
            self::Mesyuarat => 'users',
            self::Nota => 'file',
            self::Peringkat => 'arrow-right',
        };
    }

    /** Jenis yang boleh dipilih staf secara manual. */
    public static function manual(): array
    {
        return array_filter(self::cases(), fn ($c) => $c !== self::Peringkat);
    }
}
