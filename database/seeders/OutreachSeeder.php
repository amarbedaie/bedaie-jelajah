<?php

namespace Database\Seeders;

use App\Enums\OutreachActivityType;
use App\Enums\OutreachPriority;
use App\Enums\OutreachSource;
use App\Enums\OutreachStage;
use App\Enums\OutreachTargetType;
use App\Models\OutreachActivity;
use App\Models\OutreachTarget;
use App\Models\Partner;
use App\Models\State;
use App\Models\User;
use App\Services\ReferenceGenerator;
use Illuminate\Database\Seeder;

/**
 * Sasaran jelajah demo — meliputi setiap peringkat dan setiap sumber
 * supaya papan, penapis dan laporan rakan boleh diuji sepenuhnya.
 */
class OutreachSeeder extends Seeder
{
    public function run(): void
    {
        $staff = User::where('role', 'admin')->orderBy('id')->get();
        $penggerak = User::where('role', 'penggerak')->orderBy('id')->get();
        $partners = Partner::where('is_active', true)->orderBy('id')->get();
        $states = State::with('districts')->get()->keyBy('code');
        $references = app(ReferenceGenerator::class);

        if ($staff->isEmpty() || $states->isEmpty()) {
            return;
        }

        $rows = [
            // ── Baru dikenal pasti ──
            ['Masjid Al-Falah Taman Melawati', OutreachTargetType::Masjid, 'SGR', 'Gombak',
                OutreachStage::Sasaran, OutreachSource::StafTerus, OutreachPriority::Sederhana, null, 3],
            ['Surau Nurul Iman Setia Alam', OutreachTargetType::Surau, 'SGR', 'Shah Alam',
                OutreachStage::Sasaran, OutreachSource::PermintaanKawasan, OutreachPriority::Tinggi, null, 5],
            ['SMK Agama Kuala Selangor', OutreachTargetType::Sekolah, 'SGR', 'Kuala Selangor',
                OutreachStage::CariKontak, OutreachSource::StafTerus, OutreachPriority::Rendah, null, 8],

            // ── Kontak dijumpai / dihubungi ──
            ['Masjid Jamek Sungai Buloh', OutreachTargetType::Masjid, 'SGR', 'Petaling',
                OutreachStage::KontakDijumpai, OutreachSource::Rakan, OutreachPriority::Tinggi, 0, 2],
            ['Maahad Tahfiz Al-Amin Bangi', OutreachTargetType::Tahfiz, 'SGR', 'Kajang',
                OutreachStage::Dihubungi, OutreachSource::Penggerak, OutreachPriority::Sederhana, null, 6],
            ['Masjid Negeri Alor Setar', OutreachTargetType::Masjid, 'KDH', 'Alor Setar',
                OutreachStage::Dihubungi, OutreachSource::Rakan, OutreachPriority::Tinggi, 1, 4],

            // ── Berbincang ──
            ['Masjid Al-Hidayah Seremban 2', OutreachTargetType::Masjid, 'NSN', 'Seremban',
                OutreachStage::Berbincang, OutreachSource::PermintaanKawasan, OutreachPriority::Tinggi, null, 1],
            ['Persatuan Belia Islam Melaka', OutreachTargetType::Persatuan, 'MLK', 'Melaka Tengah',
                OutreachStage::Berbincang, OutreachSource::Rujukan, OutreachPriority::Sederhana, null, 7],

            // ── Setuju ──
            ['Masjid Sultan Abu Bakar Muar', OutreachTargetType::Masjid, 'JHR', 'Muar',
                OutreachStage::Setuju, OutreachSource::Rakan, OutreachPriority::Tinggi, 0, 2],

            // ── Berjaya ──
            ['Masjid Al-Muttaqin Kuching', OutreachTargetType::Masjid, 'SWK', 'Kuching',
                OutreachStage::Berjaya, OutreachSource::StafTerus, OutreachPriority::Sederhana, null, 40],
            ['Menara Ilmu Kota Kinabalu', OutreachTargetType::Korporat, 'SBH', 'Kota Kinabalu',
                OutreachStage::Berjaya, OutreachSource::Rakan, OutreachPriority::Tinggi, 1, 55],

            // ── Ditutup ──
            ['Dewan Komuniti Taiping', OutreachTargetType::Komuniti, 'PRK', 'Taiping',
                OutreachStage::TidakBerminat, OutreachSource::StafTerus, OutreachPriority::Rendah, null, 30],
            ['Surau Kampung Baru Kangar', OutreachTargetType::Surau, 'PLS', 'Kangar',
                OutreachStage::Tangguh, OutreachSource::Rujukan, OutreachPriority::Rendah, null, 20],
        ];

        foreach ($rows as $i => [$name, $type, $stateCode, $districtName, $stage, $source, $priority, $partnerIndex, $daysAgo]) {
            if (OutreachTarget::where('name', $name)->exists()) {
                continue;
            }

            $state = $states[$stateCode] ?? $states->first();
            $district = $state->districts->firstWhere('name', $districtName) ?? $state->districts->first();
            $assignee = $staff[$i % $staff->count()];
            $createdAt = now()->subDays($daysAgo);

            $hasContact = ! in_array($stage, [OutreachStage::Sasaran, OutreachStage::CariKontak], true);

            $target = OutreachTarget::create([
                'reference_no' => $references->outreachTarget(),
                'name' => $name,
                'type' => $type,
                'state_id' => $state->id,
                'district_id' => $district?->id,
                'address' => $districtName.', '.$state->name,
                'source' => $source,
                'partner_id' => $partnerIndex !== null && $partners->count() > $partnerIndex
                    ? $partners[$partnerIndex]->id : null,
                'referrer_user_id' => $source === OutreachSource::Penggerak && $penggerak->isNotEmpty()
                    ? $penggerak->first()->id : null,
                'referrer_name' => $source === OutreachSource::Rujukan ? 'Ustaz Hakim (Demo)' : null,
                'referrer_phone' => $source === OutreachSource::Rujukan ? '60127778899' : null,
                'assigned_to' => $assignee->id,
                'stage' => $stage,
                'stage_changed_at' => $createdAt->copy()->addDays(1),
                'priority' => $priority,
                'contact_name' => $hasContact ? 'Ustaz Nazir (Demo)' : null,
                'contact_role' => $hasContact ? 'Nazir Masjid' : null,
                'contact_phone' => $hasContact ? '601'.str_pad((string) (30000000 + $i * 977), 8, '0', STR_PAD_LEFT) : null,
                'contact_found_at' => $hasContact ? $createdAt->copy()->addDay() : null,
                'next_action_at' => $stage->isOpen()
                    ? now()->addDays([-3, 2, 5, 9, -1][$i % 5])->toDateString()
                    : null,
                'next_action_note' => $stage->isOpen() ? 'Hubungi semula untuk sahkan tarikh.' : null,
                'notes' => 'Rekod demo untuk menguji papan sasaran.',
                'closed_reason' => $stage === OutreachStage::TidakBerminat
                    ? 'Dewan sedang diubah suai sepanjang tahun ini.' : null,
                'won_at' => $stage->isWon() ? $createdAt->copy()->addDays(14) : null,
                'created_by' => $staff->first()->id,
                'created_at' => $createdAt,
            ]);

            // Garis masa ringkas supaya halaman butiran tidak kosong.
            OutreachActivity::create([
                'outreach_target_id' => $target->id,
                'user_id' => $assignee->id,
                'type' => OutreachActivityType::Nota,
                'body' => 'Sasaran ditambah ke senarai.',
                'occurred_at' => $createdAt,
            ]);

            if ($hasContact) {
                OutreachActivity::create([
                    'outreach_target_id' => $target->id,
                    'user_id' => $assignee->id,
                    'type' => OutreachActivityType::Panggilan,
                    'body' => 'Menghubungi nazir untuk memperkenalkan program BeDaie Jelajah.',
                    'outcome' => 'Minta hantar butiran melalui WhatsApp',
                    'occurred_at' => $createdAt->copy()->addDays(2),
                ]);
            }

            if (in_array($stage, [OutreachStage::Berbincang, OutreachStage::Setuju, OutreachStage::Berjaya], true)) {
                OutreachActivity::create([
                    'outreach_target_id' => $target->id,
                    'user_id' => $assignee->id,
                    'type' => OutreachActivityType::Lawatan,
                    'body' => 'Lawatan ke lokasi bersama jawatankuasa. Ruang solat muat lebih 200 orang.',
                    'outcome' => 'Positif',
                    'occurred_at' => $createdAt->copy()->addDays(6),
                ]);
            }

            OutreachActivity::create([
                'outreach_target_id' => $target->id,
                'user_id' => $assignee->id,
                'type' => OutreachActivityType::Peringkat,
                'from_stage' => OutreachStage::Sasaran->value,
                'to_stage' => $stage->value,
                'occurred_at' => $createdAt->copy()->addDays(7),
            ]);
        }
    }
}
