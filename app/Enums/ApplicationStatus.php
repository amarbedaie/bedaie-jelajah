<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case Draf = 'draf';
    case Diterima = 'diterima';
    case DalamSemakan = 'dalam_semakan';
    case PerluMaklumat = 'perlu_maklumat';
    case DalamPerbincangan = 'dalam_perbincangan';
    case CadanganTarikh = 'cadangan_tarikh';
    case Diluluskan = 'diluluskan';
    case ProgramDisahkan = 'program_disahkan';
    case Ditangguhkan = 'ditangguhkan';
    case TidakDapatDipenuhi = 'tidak_dapat_dipenuhi';
    case Selesai = 'selesai';

    /** Label mesra yang dipaparkan kepada Penggerak & orang awam. */
    public function label(): string
    {
        return match ($this) {
            self::Draf => 'Draf',
            self::Diterima => 'Permohonan Diterima',
            self::DalamSemakan => 'Dalam Semakan',
            self::PerluMaklumat => 'Perlu Maklumat Tambahan',
            self::DalamPerbincangan => 'Dalam Perbincangan',
            self::CadanganTarikh => 'Cadangan Tarikh',
            self::Diluluskan => 'Diluluskan',
            self::ProgramDisahkan => 'Program Disahkan',
            self::Ditangguhkan => 'Ditangguhkan',
            self::TidakDapatDipenuhi => 'Tidak Dapat Dipenuhi',
            self::Selesai => 'Selesai',
        };
    }

    /** Penerangan ringkas untuk Penggerak — bahasa mudah, tiada jargon dalaman. */
    public function description(): string
    {
        return match ($this) {
            self::Draf => 'Permohonan anda belum dihantar.',
            self::Diterima => 'Terima kasih! Permohonan anda telah kami terima dan sedang menunggu giliran semakan.',
            self::DalamSemakan => 'Pasukan BeDaie sedang menyemak permohonan anda.',
            self::PerluMaklumat => 'Kami perlukan sedikit maklumat tambahan daripada anda.',
            self::DalamPerbincangan => 'Kami sedang berbincang dengan anda dan pihak lokasi.',
            self::CadanganTarikh => 'Kami telah mencadangkan tarikh. Sila sahkan dengan pihak lokasi.',
            self::Diluluskan => 'Permohonan anda diluluskan. Butiran program sedang dimuktamadkan.',
            self::ProgramDisahkan => 'Program anda telah disahkan. Halaman program & link pendaftaran sudah tersedia.',
            self::Ditangguhkan => 'Program ditangguhkan buat sementara waktu.',
            self::TidakDapatDipenuhi => 'Maaf, permohonan ini tidak dapat kami penuhi buat masa ini.',
            self::Selesai => 'Program telah selesai dilaksanakan. Jazakumullah khairan!',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draf => 'grey',
            self::Diterima => 'purple',
            self::DalamSemakan => 'slate',
            self::PerluMaklumat => 'warning',
            self::CadanganTarikh => 'clay',
            self::DalamPerbincangan => 'navy',
            self::Diluluskan, self::ProgramDisahkan, self::Selesai => 'success',
            self::Ditangguhkan => 'warning',
            self::TidakDapatDipenuhi => 'danger',
        };
    }

    /** Kedudukan pada garis masa (0-100) untuk progress bar Penggerak. */
    public function progress(): int
    {
        return match ($this) {
            self::Draf => 0,
            self::Diterima => 15,
            self::DalamSemakan => 30,
            self::PerluMaklumat => 35,
            self::DalamPerbincangan => 50,
            self::CadanganTarikh => 65,
            self::Diluluskan => 80,
            self::ProgramDisahkan => 100,
            self::Selesai => 100,
            self::Ditangguhkan, self::TidakDapatDipenuhi => 0,
        };
    }

    public function isClosed(): bool
    {
        return in_array($this, [self::TidakDapatDipenuhi, self::Selesai], true);
    }

    /** Status yang dianggap "aktif" dalam corong permohonan. */
    public static function openStatuses(): array
    {
        return [
            self::Diterima, self::DalamSemakan, self::PerluMaklumat,
            self::DalamPerbincangan, self::CadanganTarikh, self::Diluluskan,
        ];
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
