<?php

namespace Database\Seeders;

use App\Models\Blueprint;
use Illuminate\Database\Seeder;

/**
 * Tiga blueprint SOP permulaan (draf — pasukan boleh ubah di admin).
 * Idempoten: blueprint yang sudah wujud (ikut slug) tidak disentuh supaya
 * suntingan pasukan tidak ditimpa.
 *
 * Format item: [tajuk, offset hari dari tarikh program (negatif = sebelum), butiran?]
 */
class BlueprintSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->blueprints() as $sort => $data) {
            if (Blueprint::where('slug', $data['slug'])->exists()) {
                continue;
            }

            $blueprint = Blueprint::create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'venue_type' => $data['venue_type'],
                'description' => $data['description'],
                'is_active' => true,
                'sort_order' => $sort,
            ]);

            foreach ($data['items'] as $phase => $items) {
                foreach ($items as $i => $item) {
                    $blueprint->items()->create([
                        'phase' => $phase,
                        'title' => $item[0],
                        'due_offset_days' => $item[1],
                        'details' => $item[2] ?? null,
                        'sort_order' => $i,
                    ]);
                }
            }
        }
    }

    private function blueprints(): array
    {
        return [
            [
                'name' => 'Blueprint Masjid / Surau',
                'slug' => 'masjid',
                'venue_type' => 'masjid',
                'description' => 'SOP program di masjid atau surau bersama AJK dan ahli kariah.',
                'items' => [
                    'sebelum' => [
                        ['Sahkan tarikh & masa dengan AJK masjid/surau secara bertulis (WhatsApp/surat)', -30],
                        ['Dapatkan kebenaran Pengerusi/Imam dan surat jemputan rasmi', -21],
                        ['Semak keperluan tauliah mengajar penceramah dengan Majlis/Jabatan Agama Negeri', -21],
                        ['Sahkan penceramah dan hantar butiran lokasi, masa & kenalan PIC masjid', -14],
                        ['Siapkan poster & pautan pendaftaran, edar di group kariah', -14],
                        ['Mohon pengumuman selepas solat (termasuk Jumaat) melalui AJK', -7],
                        ['Semak ruang: PA system, mikrofon, projektor/skrin, ruang muslimat', -7],
                        ['Selaras jamuan/minuman (jika ada) dengan AJK', -5],
                        ['Sediakan QR kehadiran, kaunter pendaftaran & banner', -2],
                        ['Hantar peringatan kepada peserta berdaftar', -1],
                        ['Sahkan pasukan bertugas & pembahagian tugas', -1],
                    ],
                    'semasa' => [
                        ['Tiba sekurang-kurangnya 1 jam awal dan sapa AJK masjid', 0],
                        ['Uji PA system, mikrofon dan slaid', 0],
                        ['Buka kaunter pendaftaran & imbas QR kehadiran', 0],
                        ['Pastikan susunan saf/kerusi dan ruang muslimat teratur', 0],
                        ['Ambil gambar & video (dengan izin, jaga aurat peserta)', 0],
                        ['Ucapan alu-aluan wakil masjid', 0],
                        ['Edar QR/borang maklum balas sebelum bersurai', 0],
                        ['Sesi soal jawab dan doa penutup', 0],
                    ],
                    'selepas' => [
                        ['Kemas dan pulangkan ruang seperti asal', 0],
                        ['Ucapan terima kasih kepada AJK masjid', 1],
                        ['Tutup program dalam sistem (sijil & maklum balas dijana)', 1],
                        ['Muat naik gambar terpilih ke Galeri Impak', 3],
                        ['Serahkan sijil penghargaan rakan kepada masjid', 7],
                        ['Semak maklum balas dan tulis laporan program', 7],
                        ['Bincang program susulan dengan AJK', 14],
                    ],
                ],
            ],
            [
                'name' => 'Blueprint Sekolah / Tahfiz',
                'slug' => 'sekolah',
                'venue_type' => 'sekolah',
                'description' => 'SOP program di sekolah, maahad tahfiz atau pondok bersama pihak pentadbir & guru.',
                'items' => [
                    'sebelum' => [
                        ['Hubungi Pengetua/Guru Besar/Mudir dan kenal pasti guru penyelaras', -30],
                        ['Hantar surat rasmi kepada sekolah (dan kebenaran PPD/JPN bagi sekolah kerajaan)', -30],
                        ['Sahkan tarikh ikut takwim sekolah — elak minggu peperiksaan & cuti', -21],
                        ['Sahkan bilangan & tahap murid, pilih modul sesuai umur', -14],
                        ['Sediakan surat kebenaran ibu bapa (jika ada rakaman/aktiviti luar)', -14],
                        ['Semak dokumen/tauliah penceramah jika diminta sekolah', -14],
                        ['Semak dewan/kelas, PA system dan projektor', -7],
                        ['Sediakan bahan aktiviti, hadiah kuiz & cenderamata', -5],
                        ['Dapatkan senarai nama murid untuk sijil', -3],
                        ['Hantar peringatan kepada guru penyelaras', -1],
                    ],
                    'semasa' => [
                        ['Lapor diri di pejabat sekolah & patuhi peraturan keselamatan sekolah', 0],
                        ['Pastikan guru penyelaras bersama sepanjang sesi', 0],
                        ['Rekod kehadiran murid', 0],
                        ['Ambil gambar ikut polisi sekolah (elak wajah murid tanpa izin)', 0],
                        ['Sesi interaktif & kuiz', 0],
                        ['Edar maklum balas guru/murid', 0],
                    ],
                    'selepas' => [
                        ['Ucapan terima kasih kepada pentadbir & guru penyelaras', 1],
                        ['Tutup program dalam sistem dan jana sijil murid', 2],
                        ['Muat naik gambar terpilih ke Galeri Impak', 3],
                        ['Serahkan sijil penghargaan kepada sekolah', 7],
                        ['Hantar laporan ringkas program kepada sekolah', 7],
                        ['Cadang program susulan / kelas berkala', 14],
                    ],
                ],
            ],
            [
                'name' => 'Blueprint Institusi / Kolej / NGO',
                'slug' => 'institusi',
                'venue_type' => 'institusi',
                'description' => 'SOP program bersama institusi pengajian, kolej, korporat atau NGO.',
                'items' => [
                    'sebelum' => [
                        ['Kenal pasti pegawai/wakil penganjur (PIC) dan saluran komunikasi', -30],
                        ['Dapatkan surat/emel pengesahan kerjasama (atau MoU jika berkala)', -30],
                        ['Persetujuan kos/yuran, tajaan dan invois', -21],
                        ['Sahkan format (ceramah/bengkel/forum), tempoh & sasaran peserta', -21],
                        ['Dapatkan kelulusan pengurusan institusi / HEP (bagi pelajar)', -14],
                        ['Promosi bersama di saluran rasmi institusi', -14],
                        ['Logistik: dewan, AV, parkir & pas keselamatan', -7],
                        ['Semak senarai peserta & pendaftaran', -3],
                        ['Hantar peringatan kepada PIC & peserta', -1],
                    ],
                    'semasa' => [
                        ['Lapor diri kepada PIC & pengawal keselamatan', 0],
                        ['Uji AV, mikrofon & slaid', 0],
                        ['Pendaftaran & imbas QR kehadiran', 0],
                        ['Ikut protokol majlis (ucapan wakil institusi)', 0],
                        ['Dokumentasi gambar & video', 0],
                        ['Edar maklum balas sebelum bersurai', 0],
                    ],
                    'selepas' => [
                        ['Ucapan terima kasih kepada PIC & pengurusan', 1],
                        ['Tutup program dalam sistem dan jana sijil', 2],
                        ['Muat naik gambar terpilih ke Galeri Impak', 3],
                        ['Selesaikan invois / bayaran', 7],
                        ['Serahkan sijil penghargaan & laporan kepada institusi', 7],
                        ['Bincang program susulan', 14],
                    ],
                ],
            ],
        ];
    }
}
