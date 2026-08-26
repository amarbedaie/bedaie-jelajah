<?php

namespace Database\Seeders;

use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->templates() as $row) {
            foreach ($row['channels'] as $channel => $content) {
                NotificationTemplate::updateOrCreate(
                    ['key' => $row['key'], 'channel' => $channel],
                    [
                        'name' => $row['name'],
                        'subject' => $content['subject'] ?? null,
                        'body' => $content['body'],
                        'placeholders' => NotificationTemplate::commonPlaceholders(),
                        'is_active' => true,
                    ],
                );
            }
        }
    }

    private function templates(): array
    {
        $sign = "\n\n_{{brand}} Jelajah — Membawa Ilmu, Menghidupkan Ummah._";

        return [
            [
                'key' => 'permohonan_diterima',
                'name' => 'Permohonan diterima',
                'channels' => [
                    'email' => [
                        'subject' => 'Permohonan {{reference_no}} telah kami terima',
                        'body' => "Assalamualaikum {{mobilizer_name}},\n\nTerima kasih kerana ingin membawa BeDaie ke {{venue}}. Permohonan anda telah kami terima dengan nombor rujukan {{reference_no}}.\n\nPasukan kami akan menyemak permohonan ini dan menghubungi anda melalui WhatsApp dalam masa 3-5 hari bekerja.\n\nAnda boleh menyemak status permohonan pada bila-bila masa melalui Ruang Penggerak.",
                    ],
                    'whatsapp' => [
                        'subject' => 'Permohonan Diterima',
                        'body' => "Assalamualaikum {{mobilizer_name}} 🌿\n\nPermohonan anda untuk membawa BeDaie ke *{{venue}}* telah kami terima.\n\n📄 Rujukan: *{{reference_no}}*\n\nPasukan kami akan menghubungi anda tidak lama lagi, insyaAllah.".$sign,
                    ],
                    'inapp' => [
                        'subject' => 'Permohonan diterima',
                        'body' => "Permohonan {{reference_no}} untuk {{venue}} telah kami terima. Kami akan menghubungi anda tidak lama lagi.",
                    ],
                ],
            ],
            [
                'key' => 'status_permohonan_berubah',
                'name' => 'Status permohonan berubah',
                'channels' => [
                    'email' => [
                        'subject' => 'Kemas kini permohonan {{reference_no}}: {{status}}',
                        'body' => "Assalamualaikum {{mobilizer_name}},\n\nStatus permohonan {{reference_no}} kini: {{status}}.\n\n{{status_note}}\n\nSila log masuk ke Ruang Penggerak untuk butiran penuh.",
                    ],
                    'whatsapp' => [
                        'subject' => 'Kemas Kini Permohonan',
                        'body' => "Assalamualaikum {{mobilizer_name}} 🌿\n\nStatus permohonan *{{reference_no}}* kini: *{{status}}*\n\n{{status_note}}".$sign,
                    ],
                    'inapp' => [
                        'subject' => 'Status permohonan: {{status}}',
                        'body' => '{{status_note}}',
                    ],
                ],
            ],
            [
                'key' => 'program_disahkan',
                'name' => 'Program disahkan & halaman program tersedia',
                'channels' => [
                    'email' => [
                        'subject' => 'Alhamdulillah! Program anda telah disahkan',
                        'body' => "Assalamualaikum {{mobilizer_name}},\n\nAlhamdulillah, program anda telah disahkan!\n\nProgram: {{event_name}}\nTarikh: {{event_date}}\nMasa: {{event_time}}\nLokasi: {{venue}}\n\nHalaman program dan link pendaftaran telah dijana secara automatik:\n{{registration_link}}\n\nAnda kini boleh menyebarkan link ini kepada ahli kariah dan komuniti anda. Poster rasmi dan kod QR pendaftaran juga tersedia di Ruang Penggerak.",
                    ],
                    'whatsapp' => [
                        'subject' => 'Program Disahkan',
                        'body' => "Assalamualaikum {{mobilizer_name}} 🎉\n\nAlhamdulillah! Program anda telah *DISAHKAN*.\n\n📌 {{event_name}}\n🗓️ {{event_date}}\n🕐 {{event_time}}\n📍 {{venue}}\n\n🔗 Link pendaftaran:\n{{registration_link}}\n\nSila sebarkan link ini kepada ahli kariah. Poster & QR boleh dimuat turun di Ruang Penggerak.".$sign,
                    ],
                    'inapp' => [
                        'subject' => 'Program anda telah disahkan',
                        'body' => "{{event_name}} pada {{event_date}}. Halaman program & link pendaftaran sudah tersedia.",
                    ],
                ],
            ],
            [
                'key' => 'pendaftaran_berjaya',
                'name' => 'Pendaftaran peserta berjaya',
                'channels' => [
                    'email' => [
                        'subject' => 'Pendaftaran anda disahkan — {{event_name}}',
                        'body' => "Assalamualaikum {{participant_name}},\n\nPendaftaran anda telah disahkan. Terima kasih!\n\nProgram: {{event_name}}\nPenceramah: {{speaker}}\nTarikh: {{event_date}}\nMasa: {{event_time}}\nLokasi: {{venue}}\nRujukan: {{reference_no}}\n\nSila simpan tiket dan kod QR anda untuk imbasan kehadiran di lokasi:\n{{qr_link}}\n\nJumpa di sana, insyaAllah!",
                    ],
                    'whatsapp' => [
                        'subject' => 'Pendaftaran Disahkan',
                        'body' => "Assalamualaikum {{participant_name}} ✅\n\nPendaftaran anda *DISAHKAN*.\n\n📌 {{event_name}}\n🎙️ {{speaker}}\n🗓️ {{event_date}}\n🕐 {{event_time}}\n📍 {{venue}}\n🎫 {{reference_no}}\n\nTiket & QR kehadiran anda:\n{{qr_link}}\n\nSila tunjukkan QR ini di kaunter pendaftaran.".$sign,
                    ],
                    'inapp' => [
                        'subject' => 'Pendaftaran disahkan',
                        'body' => "Anda telah berdaftar untuk {{event_name}} pada {{event_date}}.",
                    ],
                ],
            ],
            [
                'key' => 'pendaftaran_menunggu_pengesahan',
                'name' => 'Pendaftaran menunggu pengesahan/bayaran',
                'channels' => [
                    'email' => [
                        'subject' => 'Pendaftaran diterima — menunggu pengesahan',
                        'body' => "Assalamualaikum {{participant_name}},\n\nPendaftaran anda untuk {{event_name}} telah kami terima dan sedang menunggu pengesahan.\n\nRujukan: {{reference_no}}\nTarikh program: {{event_date}}\n\nSila lengkapkan pembayaran (jika berkaitan) melalui pautan tiket anda:\n{{qr_link}}",
                    ],
                    'whatsapp' => [
                        'subject' => 'Menunggu Pengesahan',
                        'body' => "Assalamualaikum {{participant_name}} ⏳\n\nPendaftaran anda untuk *{{event_name}}* telah diterima dan sedang menunggu pengesahan.\n\n🎫 {{reference_no}}\n🔗 {{qr_link}}".$sign,
                    ],
                    'inapp' => [
                        'subject' => 'Pendaftaran menunggu pengesahan',
                        'body' => "Pendaftaran {{reference_no}} untuk {{event_name}} sedang menunggu pengesahan.",
                    ],
                ],
            ],
            [
                'key' => 'pendaftaran_senarai_menunggu',
                'name' => 'Pendaftaran masuk senarai menunggu',
                'channels' => [
                    'email' => [
                        'subject' => 'Anda dalam senarai menunggu — {{event_name}}',
                        'body' => "Assalamualaikum {{participant_name}},\n\nTempat untuk {{event_name}} telah penuh, jadi anda kini berada dalam senarai menunggu.\n\nKami akan memaklumkan anda dengan segera jika ada tempat kosong.\n\nRujukan: {{reference_no}}",
                    ],
                    'whatsapp' => [
                        'subject' => 'Senarai Menunggu',
                        'body' => "Assalamualaikum {{participant_name}} 📋\n\nTempat untuk *{{event_name}}* telah penuh. Anda kini dalam *senarai menunggu*.\n\nKami akan maklumkan segera jika ada tempat kosong.\n\n🎫 {{reference_no}}".$sign,
                    ],
                    'inapp' => [
                        'subject' => 'Anda dalam senarai menunggu',
                        'body' => "Tempat {{event_name}} telah penuh. Kami akan maklumkan jika ada kekosongan.",
                    ],
                ],
            ],
            [
                'key' => 'naik_dari_senarai_menunggu',
                'name' => 'Naik daripada senarai menunggu',
                'channels' => [
                    'email' => [
                        'subject' => 'Tempat anda telah disahkan — {{event_name}}',
                        'body' => "Assalamualaikum {{participant_name}},\n\nKhabar baik! Tempat kosong telah tersedia dan pendaftaran anda kini DISAHKAN.\n\nProgram: {{event_name}}\nTarikh: {{event_date}}\nMasa: {{event_time}}\nLokasi: {{venue}}\n\nTiket & QR anda: {{qr_link}}",
                    ],
                    'whatsapp' => [
                        'subject' => 'Tempat Anda Disahkan',
                        'body' => "Assalamualaikum {{participant_name}} 🎉\n\nKhabar baik! Tempat kosong telah tersedia — pendaftaran anda kini *DISAHKAN*.\n\n📌 {{event_name}}\n🗓️ {{event_date}}\n📍 {{venue}}\n\n🎫 {{qr_link}}".$sign,
                    ],
                    'inapp' => [
                        'subject' => 'Tempat anda telah disahkan',
                        'body' => "Anda kini disahkan untuk {{event_name}}.",
                    ],
                ],
            ],
            [
                'key' => 'peringatan_program',
                'name' => 'Peringatan sebelum program',
                'channels' => [
                    'whatsapp' => [
                        'subject' => 'Peringatan Program',
                        'body' => "Assalamualaikum {{participant_name}} 🔔\n\nPeringatan: *{{event_name}}*\n\n🗓️ {{event_date}}\n🕐 {{event_time}}\n📍 {{venue}}\n\nJangan lupa bawa QR kehadiran anda:\n{{qr_link}}".$sign,
                    ],
                    'email' => [
                        'subject' => 'Peringatan: {{event_name}} — {{event_date}}',
                        'body' => "Assalamualaikum {{participant_name}},\n\nIni peringatan bahawa {{event_name}} akan berlangsung pada {{event_date}}, {{event_time}} di {{venue}}.\n\nSila bawa QR kehadiran anda: {{qr_link}}",
                    ],
                    'inapp' => [
                        'subject' => 'Peringatan program',
                        'body' => "{{event_name}} akan berlangsung pada {{event_date}}, {{event_time}}.",
                    ],
                ],
            ],
            [
                'key' => 'program_ditangguhkan',
                'name' => 'Program ditangguhkan',
                'channels' => [
                    'whatsapp' => [
                        'subject' => 'Program Ditangguhkan',
                        'body' => "Assalamualaikum {{participant_name}},\n\nMohon maaf, *{{event_name}}* pada {{event_date}} telah *DITANGGUHKAN*.\n\n{{status_note}}\n\nTarikh baharu akan dimaklumkan.".$sign,
                    ],
                    'email' => [
                        'subject' => 'Program ditangguhkan — {{event_name}}',
                        'body' => "Assalamualaikum {{participant_name}},\n\nMohon maaf, {{event_name}} pada {{event_date}} di {{venue}} telah ditangguhkan.\n\n{{status_note}}\n\nTarikh baharu akan dimaklumkan kepada anda.",
                    ],
                    'inapp' => [
                        'subject' => 'Program ditangguhkan',
                        'body' => "{{event_name}} telah ditangguhkan. {{status_note}}",
                    ],
                ],
            ],
            [
                'key' => 'program_dibatalkan',
                'name' => 'Program dibatalkan',
                'channels' => [
                    'whatsapp' => [
                        'subject' => 'Program Dibatalkan',
                        'body' => "Assalamualaikum {{participant_name}},\n\nMohon maaf, *{{event_name}}* pada {{event_date}} telah *DIBATALKAN*.\n\n{{status_note}}".$sign,
                    ],
                    'email' => [
                        'subject' => 'Program dibatalkan — {{event_name}}',
                        'body' => "Assalamualaikum {{participant_name}},\n\nMohon maaf, {{event_name}} pada {{event_date}} telah dibatalkan.\n\n{{status_note}}",
                    ],
                    'inapp' => [
                        'subject' => 'Program dibatalkan',
                        'body' => "{{event_name}} telah dibatalkan. {{status_note}}",
                    ],
                ],
            ],
            [
                'key' => 'pembayaran_berjaya',
                'name' => 'Pembayaran berjaya',
                'channels' => [
                    'whatsapp' => [
                        'subject' => 'Pembayaran Diterima',
                        'body' => "Assalamualaikum {{participant_name}} ✅\n\nPembayaran anda untuk *{{event_name}}* telah diterima dan pendaftaran anda kini disahkan.\n\n🎫 {{reference_no}}\n🔗 {{qr_link}}".$sign,
                    ],
                    'email' => [
                        'subject' => 'Pembayaran diterima — {{event_name}}',
                        'body' => "Assalamualaikum {{participant_name}},\n\nPembayaran anda telah diterima dan pendaftaran {{reference_no}} untuk {{event_name}} kini disahkan.\n\nTiket & QR: {{qr_link}}",
                    ],
                    'inapp' => [
                        'subject' => 'Pembayaran diterima',
                        'body' => "Pembayaran untuk {{event_name}} telah disahkan.",
                    ],
                ],
            ],
            [
                'key' => 'maklum_balas_program',
                'name' => 'Permintaan maklum balas selepas program',
                'channels' => [
                    'whatsapp' => [
                        'subject' => 'Maklum Balas Program',
                        'body' => "Assalamualaikum {{participant_name}} 🌿\n\nJazakumullah khairan kerana hadir ke *{{event_name}}*.\n\nBoleh kongsikan maklum balas anda? Ia hanya mengambil masa 1 minit dan membantu kami menambah baik program akan datang:\n\n{{registration_link}}".$sign,
                    ],
                    'email' => [
                        'subject' => 'Bagaimana pengalaman anda di {{event_name}}?',
                        'body' => "Assalamualaikum {{participant_name}},\n\nJazakumullah khairan kerana hadir ke {{event_name}}.\n\nSudi kongsikan maklum balas anda? Ia hanya mengambil masa seminit:\n{{registration_link}}",
                    ],
                    'inapp' => [
                        'subject' => 'Kongsikan maklum balas anda',
                        'body' => "Bagaimana pengalaman anda di {{event_name}}?",
                    ],
                ],
            ],
            [
                'key' => 'sijil_tersedia',
                'name' => 'Sijil tersedia',
                'channels' => [
                    'whatsapp' => [
                        'subject' => 'Sijil Anda Telah Sedia',
                        'body' => "Assalamualaikum {{participant_name}} 🎓\n\nSijil penyertaan anda bagi *{{event_name}}* telah sedia untuk dimuat turun.\n\n🔗 {{certificate_link}}\n\nSemoga ilmu yang dipelajari menjadi amal jariah.".$sign,
                    ],
                    'email' => [
                        'subject' => 'Sijil anda telah sedia — {{event_name}}',
                        'body' => "Assalamualaikum {{participant_name}},\n\nSijil penyertaan anda bagi {{event_name}} pada {{event_date}} telah sedia.\n\nMuat turun atau sahkan sijil di sini:\n{{certificate_link}}\n\nSemoga ilmu yang dipelajari menjadi amal jariah yang berkekalan.",
                    ],
                    'inapp' => [
                        'subject' => 'Sijil anda telah sedia',
                        'body' => "Sijil untuk {{event_name}} boleh dimuat turun sekarang.",
                    ],
                ],
            ],
        ];
    }
}
