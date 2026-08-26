<?php

namespace App\Enums;

enum TargetAudience: string
{
    case Umum = 'umum';
    case Keluarga = 'keluarga';
    case Wanita = 'wanita';
    case AnakMuda = 'anak_muda';
    case KanakKanak = 'kanak_kanak';
    case Pelajar = 'pelajar';
    case WargaEmas = 'warga_emas';
    case Organisasi = 'organisasi';

    public function label(): string
    {
        return match ($this) {
            self::Umum => 'Umum',
            self::Keluarga => 'Keluarga',
            self::Wanita => 'Wanita',
            self::AnakMuda => 'Anak Muda',
            self::KanakKanak => 'Kanak-kanak',
            self::Pelajar => 'Pelajar',
            self::WargaEmas => 'Warga Emas',
            self::Organisasi => 'Organisasi',
        };
    }

    /**
     * Jantina hanya ditanya apabila ia benar-benar diperlukan oleh penganjur —
     * lazimnya untuk susunan tempat duduk pada program bercampur. Bagi program
     * khusus wanita, jantina sudah terjawab dengan sendirinya.
     */
    public function requiresGender(): bool
    {
        return in_array($this, [self::Umum, self::Keluarga, self::Organisasi], true);
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
