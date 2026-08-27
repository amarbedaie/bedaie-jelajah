<?php

namespace App\Enums;

/**
 * Perjalanan sasaran jelajah: daripada senarai kosong sehingga
 * program benar-benar berlangsung.
 */
enum OutreachStage: string
{
    case Sasaran = 'sasaran';

    case CariKontak = 'cari_kontak';

    case KontakDijumpai = 'kontak_dijumpai';

    case Dihubungi = 'dihubungi';

    case Berbincang = 'berbincang';

    case Setuju = 'setuju';

    case Dijadualkan = 'dijadualkan';

    case Berjaya = 'berjaya';

    case TidakBerminat = 'tidak_berminat';

    case Tangguh = 'tangguh';

    public function label(): string
    {
        return match ($this) {
            self::Sasaran => 'Senarai Sasaran',
            self::CariKontak => 'Mencari Kontak',
            self::KontakDijumpai => 'Kontak Dijumpai',
            self::Dihubungi => 'Telah Dihubungi',
            self::Berbincang => 'Dalam Perbincangan',
            self::Setuju => 'Pihak Lokasi Setuju',
            self::Dijadualkan => 'Program Dijadualkan',
            self::Berjaya => 'Jelajah Berjaya',
            self::TidakBerminat => 'Tidak Berminat',
            self::Tangguh => 'Ditangguh',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Sasaran => 'Lokasi dikenal pasti, kontak belum dicari.',
            self::CariKontak => 'Sedang mencari nombor atau nama pegawai yang boleh dihubungi.',
            self::KontakDijumpai => 'Kontak diperoleh, belum dihubungi.',
            self::Dihubungi => 'Hubungan pertama telah dibuat.',
            self::Berbincang => 'Perbincangan tentang tarikh dan pengisian sedang berjalan.',
            self::Setuju => 'Pihak lokasi bersetuju menerima jelajah.',
            self::Dijadualkan => 'Permohonan dibuka dan program sedang diuruskan.',
            self::Berjaya => 'Program telah berlangsung di lokasi ini.',
            self::TidakBerminat => 'Pihak lokasi tidak berminat buat masa ini.',
            self::Tangguh => 'Ditangguh — boleh dicuba semula kemudian.',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Sasaran, self::CariKontak => 'quiet',
            self::KontakDijumpai, self::Dihubungi => 'soft',
            self::Berbincang => 'strong',
            self::Setuju, self::Dijadualkan => 'solid',
            self::Berjaya => 'solid',
            self::TidakBerminat => 'alert',
            self::Tangguh => 'quiet',
        };
    }

    /** Kedudukan pada papan kanban — 0 bermakna tidak dipaparkan pada papan. */
    public function boardPosition(): int
    {
        return match ($this) {
            self::Sasaran => 1,
            self::CariKontak => 2,
            self::KontakDijumpai => 3,
            self::Dihubungi => 4,
            self::Berbincang => 5,
            self::Setuju => 6,
            self::Dijadualkan => 7,
            self::Berjaya => 8,
            default => 0,
        };
    }

    /** Peratus kemajuan untuk bar visual. */
    public function progress(): int
    {
        return match ($this) {
            self::Sasaran => 5,
            self::CariKontak => 15,
            self::KontakDijumpai => 30,
            self::Dihubungi => 45,
            self::Berbincang => 60,
            self::Setuju => 80,
            self::Dijadualkan => 92,
            self::Berjaya => 100,
            self::TidakBerminat, self::Tangguh => 100,
        };
    }

    public function isOpen(): bool
    {
        return ! in_array($this, [self::Berjaya, self::TidakBerminat], true);
    }

    public function isWon(): bool
    {
        return $this === self::Berjaya;
    }

    /** Peringkat yang layak ditukar kepada permohonan rasmi. */
    public function canConvert(): bool
    {
        return in_array($this, [self::Setuju, self::Berbincang], true);
    }

    /** @return array<int, self> */
    public static function board(): array
    {
        return collect(self::cases())
            ->filter(fn (self $s) => $s->boardPosition() > 0)
            ->sortBy(fn (self $s) => $s->boardPosition())
            ->values()->all();
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
