<?php

namespace App\Enums;

/** Dari mana sasaran ini datang — penting untuk mengukur sumbangan rakan. */
enum OutreachSource: string
{
    case StafTerus = 'staf_terus';

    case Rakan = 'rakan';

    case Penggerak = 'penggerak';

    case Rujukan = 'rujukan';

    case PermintaanKawasan = 'permintaan_kawasan';

    public function label(): string
    {
        return match ($this) {
            self::StafTerus => 'Staf Hubungi Terus',
            self::Rakan => 'Melalui Rakan',
            self::Penggerak => 'Melalui Penggerak',
            self::Rujukan => 'Rujukan Individu',
            self::PermintaanKawasan => 'Permintaan Kawasan',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::StafTerus => 'Staf BeDaie mencari dan menghubungi lokasi ini sendiri.',
            self::Rakan => 'Rakan atau penaja memperkenalkan lokasi ini kepada kami.',
            self::Penggerak => 'Seorang Penggerak Jelajah mencadangkan lokasi ini.',
            self::Rujukan => 'Individu yang bukan dalam sistem memberi rujukan.',
            self::PermintaanKawasan => 'Datang daripada permintaan komuniti pada peta jelajah.',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::StafTerus => 'user',
            self::Rakan => 'handshake',
            self::Penggerak => 'map',
            self::Rujukan => 'chat',
            self::PermintaanKawasan => 'heart',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::StafTerus => 'grey',
            self::Rakan => 'purple',
            self::Penggerak => 'success',
            self::Rujukan => 'warning',
            self::PermintaanKawasan => 'purple',
        };
    }

    /** Sumber yang memerlukan rakan dipilih. */
    public function needsPartner(): bool
    {
        return $this === self::Rakan;
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
