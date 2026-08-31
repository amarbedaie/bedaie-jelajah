<?php

namespace Database\Seeders;

use App\Enums\CertificateType;
use App\Models\CertificateTemplate;
use App\Models\EventCategory;
use App\Models\Partner;
use App\Models\Speaker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $this->categories();
        $this->speakers();
        $this->certificateTemplates();
        $this->partners();
    }

    private function categories(): void
    {
        $rows = [
            ['Jelajah Ilmu', 'book', 'Kuliah & pengajian umum', 'Pengisian ilmu asas fardhu ain, fiqh harian dan tazkiyah untuk seluruh ahli kariah.'],
            ['Jelajah Al-Quran', 'sparkle', 'Tadabbur & tajwid', 'Kelas tajwid, makhraj dan tadabbur surah pilihan bersama tenaga pengajar BeDaie.'],
            ['Jelajah Solat', 'mosque', 'Perbaiki solat kita', 'Bengkel praktikal solat, qadha solat dan sunat-sunat solat mengikut Mazhab Syafi\'i.'],
            ['Jelajah Keluarga', 'heart', 'Rumah tangga & parenting', 'Program untuk pasangan dan ibu bapa membina rumah tangga yang tenang dan berilmu.'],
            ['Jelajah Anak Muda', 'users', 'Belia & mahasiswa', 'Sesi santai bersama anak muda tentang jati diri, adab dan hala tuju hidup.'],
            ['Jelajah Wanita', 'user', 'Fiqh & tasawuf muslimah', 'Fiqh darah wanita, kehamilan, penyusuan dan tazkiyah khusus untuk muslimah.'],
            ['Jelajah Sekolah & Tahfiz', 'book', 'Pelajar & pendidik', 'Modul motivasi, adab menuntut ilmu dan bimbingan hafazan untuk pelajar.'],
            ['Jelajah Masjid', 'mosque', 'Memakmurkan rumah Allah', 'Program bersama jawatankuasa masjid untuk menghidupkan aktiviti kariah.'],
            ['Jelajah Korporat', 'handshake', 'Warga kerja & organisasi', 'Sesi ilmu di premis organisasi — solat di tempat kerja, adab dan keberkatan rezeki.'],
            ['Jelajah Prihatin', 'heart', 'Komuniti asnaf & pedalaman', 'Program santunan ilmu untuk komuniti terpinggir, asnaf dan kawasan pedalaman.'],
        ];

        foreach ($rows as $i => [$name, $icon, $tagline, $description]) {
            EventCategory::updateOrCreate(
                ['slug' => Str::slug($name)],
                compact('name', 'icon', 'tagline', 'description') + ['sort_order' => $i + 1, 'is_active' => true],
            );
        }
    }

    private function speakers(): void
    {
        $rows = [
            [
                'name' => 'Ustaz Ahmad Faiz bin Ismail',
                'title' => 'Tenaga Pengajar BeDaie · Fiqh Mazhab Syafi\'i',
                'bio' => 'Lulusan pengajian Islam dengan pengkhususan fiqh Mazhab Syafi\'i. Aktif mengendalikan kelas fardhu ain dan bengkel solat di masjid seluruh Malaysia. (Data demo)',
            ],
            [
                'name' => 'Ustazah Nur Hidayah binti Rahman',
                'title' => 'Tenaga Pengajar BeDaie · Fiqh Wanita',
                'bio' => 'Pengajar khusus fiqh darah wanita, kehamilan dan tasawuf muslimah. Penulis modul Untukmu Wanita. (Data demo)',
            ],
            [
                'name' => 'Ustaz Muhammad Hakim bin Yusof',
                'title' => 'Tenaga Pengajar BeDaie · Al-Quran & Tajwid',
                'bio' => 'Pemegang sanad qiraat dan pengajar tajwid serta tadabbur al-Quran untuk semua peringkat umur. (Data demo)',
            ],
        ];

        foreach ($rows as $i => $row) {
            Speaker::updateOrCreate(
                ['slug' => Str::slug($row['name'])],
                $row + ['is_active' => true, 'sort_order' => $i + 1],
            );
        }
    }

    private function certificateTemplates(): void
    {
        $rows = [
            [
                'name' => 'Sijil Penyertaan Rasmi',
                'type' => CertificateType::Penyertaan,
                'orientation' => 'landscape',
                'intro_text' => 'BeDaie Jelajah dengan sukacitanya memperakui penyertaan',
                'closing_text' => 'Semoga ilmu yang dipelajari menjadi amal jariah yang berkekalan.',
                'is_default' => true,
            ],
            [
                'name' => 'Sijil Tamat Program',
                'type' => CertificateType::TamatProgram,
                'orientation' => 'landscape',
                'intro_text' => 'BeDaie Jelajah memperakui bahawa peserta telah menamatkan program',
                'closing_text' => 'Semoga menjadi bekalan ilmu yang bermanfaat dunia dan akhirat.',
            ],
            [
                'name' => 'Sijil Pencapaian',
                'type' => CertificateType::Pencapaian,
                'orientation' => 'landscape',
                'intro_text' => 'BeDaie Jelajah memperakui pencapaian cemerlang',
                'closing_text' => 'Tahniah atas komitmen dan usaha yang ditunjukkan.',
            ],
            [
                'name' => 'Sijil Penghargaan Penggerak',
                'type' => CertificateType::PenghargaanPenggerak,
                'orientation' => 'landscape',
                'intro_text' => 'BeDaie Jelajah merakamkan setinggi-tinggi penghargaan kepada',
                'closing_text' => 'Jazakumullahu khairan kathira atas usaha membawa ilmu kepada masyarakat.',
            ],
            [
                'name' => 'Sijil Penghargaan Rakan Jelajah',
                'type' => CertificateType::PenghargaanRakan,
                'orientation' => 'landscape',
                'intro_text' => 'BeDaie Jelajah merakamkan setinggi-tinggi penghargaan kepada',
                'closing_text' => 'Terima kasih atas kerjasama memakmurkan program ilmu bersama kami.',
            ],
        ];

        foreach ($rows as $row) {
            CertificateTemplate::updateOrCreate(
                ['slug' => Str::slug($row['name'])],
                array_merge($row, [
                    'accent_color' => '#7A3BB8',
                    'signature_name' => 'Pengarah BeDaie Jelajah',
                    'signature_title' => config('jelajah.org'),
                    'is_active' => true,
                    'is_default' => $row['is_default'] ?? false,
                ]),
            );
        }
    }

    private function partners(): void
    {
        $rows = [
            ['Jabatan Agama Islam Negeri (Demo)', 'rakan', 'Strategik'],
            ['Yayasan Wakaf Ilmu (Demo)', 'penaja', 'Platinum'],
            ['Koperasi Masjid Malaysia (Demo)', 'rakan', 'Strategik'],
            ['Ameer Legacy Resources', 'rakan', 'Penerbitan'],
            ['Klinik Wildan', 'penaja', 'Emas'],
            ['Rahsia Ilahi', 'rakan', 'Penerbitan'],
        ];

        foreach ($rows as $i => [$name, $type, $tier]) {
            Partner::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'type' => $type,
                    'tier' => $tier,
                    'is_featured' => $i < 4,
                    'is_active' => true,
                    'sort_order' => $i + 1,
                ],
            );
        }
    }
}
