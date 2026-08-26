<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->settings() as $row) {
            Setting::updateOrCreate(
                ['key' => $row['key']],
                [
                    'value' => $row['value'],
                    'group' => $row['group'],
                    'type' => $row['type'] ?? 'text',
                    'label' => $row['label'],
                    'hint' => $row['hint'] ?? null,
                ],
            );
        }
    }

    private function settings(): array
    {
        return [
            // ── Hero ──
            ['key' => 'hero_title', 'group' => 'homepage', 'label' => 'Tajuk hero',
                'value' => 'BeDaie Jelajah'],
            ['key' => 'hero_subtitle', 'group' => 'homepage', 'label' => 'Subtajuk hero',
                'value' => 'Membawa Ilmu, Menghidupkan Ummah.'],
            ['key' => 'hero_description', 'group' => 'homepage', 'label' => 'Penerangan hero', 'type' => 'textarea',
                'value' => 'Gerakan ilmu yang menghubungkan BeDaie dengan masjid, surau, sekolah, tahfiz dan komuniti di seluruh Malaysia.'],
            ['key' => 'hero_cta_primary', 'group' => 'homepage', 'label' => 'Butang utama hero',
                'value' => 'Jemput BeDaie ke Kawasan Anda'],
            ['key' => 'hero_cta_secondary', 'group' => 'homepage', 'label' => 'Butang kedua hero',
                'value' => 'Lihat Program Akan Datang'],

            // ── Seksyen permintaan komuniti ──
            ['key' => 'demand_title', 'group' => 'homepage', 'label' => 'Tajuk seksyen permintaan',
                'value' => 'BeDaie Belum Sampai ke Kawasan Anda?'],
            ['key' => 'demand_description', 'group' => 'homepage', 'label' => 'Penerangan seksyen permintaan', 'type' => 'textarea',
                'value' => 'Setiap permintaan yang kami terima membantu pasukan BeDaie merancang laluan jelajah seterusnya. Beritahu kami kawasan anda — walaupun anda bukan wakil rasmi mana-mana organisasi.'],
            ['key' => 'demand_cta', 'group' => 'homepage', 'label' => 'Butang seksyen permintaan',
                'value' => 'Saya Mahu BeDaie Datang ke Sini'],

            // ── Tentang ──
            ['key' => 'about_intro', 'group' => 'tentang', 'label' => 'Pengenalan', 'type' => 'textarea',
                'value' => 'BeDaie Jelajah ialah gerakan ilmu di bawah '.config('jelajah.org').'. Kami membawa pengisian ilmu Islam yang bersanad dan mudah difahami terus kepada masyarakat — di masjid, surau, sekolah, tahfiz, pejabat dan kampung di seluruh Malaysia.'],
            ['key' => 'about_mission', 'group' => 'tentang', 'label' => 'Misi', 'type' => 'textarea',
                'value' => 'Menjadikan setiap rumah mempunyai seorang daie — insan yang faham agamanya, mengamalkannya, dan menyampaikannya kepada keluarga serta masyarakat sekelilingnya.'],
            ['key' => 'about_approach', 'group' => 'tentang', 'label' => 'Pendekatan', 'type' => 'textarea',
                'value' => 'Pengisian kami berpegang kepada Mazhab Syafi\'i dalam fiqh dan Ahli Sunnah Wal Jamaah dalam akidah, disampaikan dengan bahasa yang mudah, tanpa polemik, dan sesuai untuk semua peringkat umur.'],

            // ── Langkah ──
            ['key' => 'steps', 'group' => 'homepage', 'label' => 'Lima langkah membawa BeDaie', 'type' => 'json',
                'value' => json_encode([
                    ['tajuk' => 'Mohon', 'penerangan' => 'Isi borang ringkas — hanya 4 langkah. Anda tidak perlu jadi penganjur profesional.'],
                    ['tajuk' => 'Kami Hubungi', 'penerangan' => 'Pasukan BeDaie akan menghubungi anda melalui WhatsApp untuk berbincang.'],
                    ['tajuk' => 'Program Disahkan', 'penerangan' => 'Kami tetapkan penceramah, tarikh dan kapasiti. Halaman program dijana automatik.'],
                    ['tajuk' => 'Sebarkan Link', 'penerangan' => 'Anda terima link, QR dan poster rasmi. Kongsi di WhatsApp kariah anda.'],
                    ['tajuk' => 'BeDaie Hadir', 'penerangan' => 'Kami datang. Peserta imbas QR, dan sijil dikeluarkan automatik selepas program.'],
                ], JSON_UNESCAPED_UNICODE)],

            // ── Privasi & terma ──
            ['key' => 'privacy_policy', 'group' => 'polisi', 'label' => 'Polisi privasi', 'type' => 'textarea',
                'hint' => 'Teks placeholder — sila semak dengan penasihat undang-undang sebelum dilancarkan.',
                'value' => "PLACEHOLDER — sila semak dengan penasihat undang-undang sebelum dilancarkan.\n\n1. Maklumat yang kami kumpul\nKami hanya mengumpul maklumat yang benar-benar diperlukan untuk menguruskan program: nama, nombor telefon/WhatsApp, emel (pilihan), negeri dan daerah. Nama yang anda berikan akan dicetak pada sijil.\n\n2. Tujuan penggunaan\nMaklumat digunakan untuk mengesahkan pendaftaran, menghantar peringatan program, merekod kehadiran, mengeluarkan sijil dan menyediakan laporan impak secara agregat.\n\n3. Perkongsian\nKami tidak menjual data anda. Maklumat peserta dikongsi dengan Penggerak Jelajah hanya dalam bentuk terhad (nama dan nombor yang disamarkan) untuk urusan program.\n\n4. Penyimpanan\nData disimpan selagi ia diperlukan untuk rekod pembelajaran dan pengesahan sijil anda.\n\n5. Hak anda\nAnda boleh meminta pembetulan atau pemadaman data dengan menghubungi kami di ".config('jelajah.support.email').'.'],

            ['key' => 'terms', 'group' => 'polisi', 'label' => 'Terma penggunaan', 'type' => 'textarea',
                'hint' => 'Teks placeholder — sila semak dengan penasihat undang-undang sebelum dilancarkan.',
                'value' => "PLACEHOLDER — sila semak dengan penasihat undang-undang sebelum dilancarkan.\n\n1. Permohonan program\nSemua permohonan dinilai dan ditentukan oleh pasukan BeDaie. Penghantaran permohonan tidak menjamin program akan dilaksanakan.\n\n2. Penetapan penceramah\nBeDaie menentukan penceramah, tarikh, masa dan kapasiti program berdasarkan kesesuaian dan ketersediaan.\n\n3. Pendaftaran peserta\nTempat adalah terhad dan tertakluk kepada kapasiti lokasi. Pendaftaran boleh dibatalkan melalui pautan tiket anda.\n\n4. Sijil\nSijil hanya dikeluarkan kepada peserta yang hadir dan telah merekodkan kehadiran melalui QR. Sijil yang dijana semula akan menggantikan sijil terdahulu.\n\n5. Pembayaran\nBagi program berbayar, terma bayaran balik akan dinyatakan pada halaman program berkenaan."],

            ['key' => 'consent_text', 'group' => 'polisi', 'label' => 'Teks persetujuan borang', 'type' => 'textarea',
                'value' => 'Saya bersetuju maklumat yang diberikan digunakan oleh BeDaie untuk menguruskan program ini, menghantar peringatan dan mengeluarkan sijil, selaras dengan Polisi Privasi.'],

            // ── Operasi ──
            ['key' => 'default_capacity', 'group' => 'operasi', 'label' => 'Kapasiti lalai program baharu', 'type' => 'number', 'value' => '150'],
            ['key' => 'application_sla_days', 'group' => 'operasi', 'label' => 'Sasaran hari maklum balas permohonan', 'type' => 'number', 'value' => '5'],
            ['key' => 'allow_public_registration', 'group' => 'operasi', 'label' => 'Benarkan pendaftaran awam', 'type' => 'boolean', 'value' => '1'],
        ];
    }
}
