<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case BelumBayar = 'belum_bayar';
    case MenungguPengesahan = 'menunggu_pengesahan';
    case Berjaya = 'berjaya';
    case Gagal = 'gagal';
    case Dipulangkan = 'dipulangkan';
    case Dikecualikan = 'dikecualikan';
    case Ditaja = 'ditaja';

    public function label(): string
    {
        return match ($this) {
            self::BelumBayar => 'Belum Bayar',
            self::MenungguPengesahan => 'Menunggu Pengesahan',
            self::Berjaya => 'Berjaya',
            self::Gagal => 'Gagal',
            self::Dipulangkan => 'Dipulangkan',
            self::Dikecualikan => 'Dikecualikan',
            self::Ditaja => 'Ditaja',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::BelumBayar => 'grey',
            self::MenungguPengesahan => 'warning',
            self::Berjaya, self::Dikecualikan, self::Ditaja => 'success',
            self::Gagal => 'danger',
            self::Dipulangkan => 'navy',
        };
    }

    /** Adakah status ini melayakkan pendaftaran disahkan? */
    public function isSettled(): bool
    {
        return in_array($this, [self::Berjaya, self::Dikecualikan, self::Ditaja], true);
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
