@php
    $steps = [
        [
            'icon' => 'clipboard',
            'title' => 'Hantar permohonan',
            'body' => 'Isi borang "Jemput BeDaie" — empat langkah, kira-kira tiga minit. Anda perlukan nama lokasi, alamat ringkas, dan satu cadangan tarikh. Itu sahaja.',
            'note' => 'Tidak mengapa jika pihak masjid belum bersetuju. Pilih "Sedang berbincang" atau "Saya perlukan bantuan BeDaie" — kami akan bantu.',
        ],
        [
            'icon' => 'chat',
            'title' => 'Kami hubungi anda',
            'body' => 'Pasukan BeDaie akan menghubungi anda melalui WhatsApp dalam beberapa hari bekerja untuk berbincang tarikh, penceramah dan pengisian.',
            'note' => 'Anda boleh menjejak status permohonan pada bila-bila masa di halaman "Permohonan Saya".',
        ],
        [
            'icon' => 'check-circle',
            'title' => 'Program disahkan',
            'body' => 'Sebaik tarikh dipersetujui, sistem menjana semuanya secara automatik: halaman program rasmi, pautan pendek, kod QR, dan poster.',
            'note' => 'Anda tidak perlu mereka apa-apa. Semua program BeDaie menggunakan templat yang sama supaya kelihatan konsisten.',
        ],
        [
            'icon' => 'whatsapp',
            'title' => 'Sebarkan',
            'body' => 'Buka dashboard anda dan tekan "Kongsi di WhatsApp". Mesej jemputan sudah siap ditulis — anda hanya perlu pilih kumpulan.',
            'note' => 'Cara paling berkesan: kumpulan WhatsApp kariah, kumpulan keluarga, dan cetak poster untuk papan kenyataan masjid.',
        ],
        [
            'icon' => 'qr',
            'title' => 'Hari program',
            'body' => 'Peserta menunjukkan QR pada telefon mereka di pintu masuk. Imbas dengan telefon anda. Kehadiran direkod serta-merta.',
            'note' => 'Kamera tidak berfungsi? Ada carian nama dan pendaftaran walk-in dalam skrin yang sama.',
        ],
        [
            'icon' => 'certificate',
            'title' => 'Selepas program',
            'body' => 'Sijil dijana automatik untuk setiap peserta yang hadir. Anda menerima sijil penghargaan Penggerak dan laporan ringkas impak program.',
            'note' => 'Peserta boleh muat turun sijil sendiri melalui tiket mereka — anda tidak perlu menghantar satu per satu.',
        ],
    ];

    $faqs = [
        ['Saya tidak ingat kata laluan saya. Macam mana?',
         'Akaun anda dicipta automatik apabila permohonan diterima, jadi kemungkinan besar anda memang tidak pernah menetapkan kata laluan. Pergi ke halaman log masuk dan tekan "Log Masuk Melalui WhatsApp". Masukkan nombor yang sama seperti dalam permohonan, dan kami hantar pautan yang terus membuka akaun anda. Pautan itu sah 30 minit dan hanya boleh diguna sekali. Selepas masuk, anda boleh menetapkan kata laluan sendiri di halaman Profil jika mahu.'],
        ['Saya tidak pernah menganjurkan program. Boleh ke?',
         'Boleh. Itulah sebabnya sistem ini dibina. Anda hanya perlu isi satu borang dan sebarkan satu pautan. Tarikh, penceramah, pendaftaran, kehadiran dan sijil semuanya diuruskan oleh pasukan BeDaie dan sistem ini.'],
        ['Bolehkah saya pilih penceramah?',
         'Tidak. BeDaie menentukan penceramah berdasarkan keperluan komuniti anda, kesesuaian tarikh dan lokasi. Ini bukan platform tempahan penceramah — ia gerakan jelajah yang diselaraskan.'],
        ['Berapa lama proses dari permohonan ke program?',
         'Bergantung kepada jadual dan lokasi. Kebanyakan program disahkan beberapa minggu selepas perbincangan. Cadangkan tarikh sekurang-kurangnya sebulan ke hadapan.'],
        ['Adakah program ini berbayar?',
         'Kebanyakan program adalah percuma. Sesetengah seminar atau bengkel mungkin berbayar, dan ada juga yang ditaja sepenuhnya. Ini ditentukan semasa perbincangan.'],
        ['Bolehkah saya ubah tarikh atau lokasi selepas disahkan?',
         'Hubungi pasukan BeDaie melalui butang "Minta Perubahan Maklumat" pada halaman program anda. Kami akan uruskan dan peserta yang telah mendaftar dimaklumkan secara automatik.'],
        ['Saya tidak nampak senarai penuh nombor telefon peserta. Kenapa?',
         'Nombor peserta disamarkan untuk melindungi privasi mereka. Anda tetap boleh melihat nama, kawasan dan status kehadiran. Jika anda perlukan senarai penuh atas sebab operasi, hubungi pasukan BeDaie.'],
        ['Apa jadi jika tempat penuh?',
         'Pendaftar seterusnya masuk ke senarai menunggu secara automatik. Jika ada pembatalan, mereka dinaikkan dan dimaklumkan.'],
        ['Bolehkah peserta daftar untuk ahli keluarga?',
         'Boleh. Semasa mendaftar, mereka boleh menambah nama ahli keluarga. Setiap orang dikira sebagai satu tempat.'],
    ];
@endphp

<x-layouts.app title="Panduan Penggerak" nav="penggerak"
               heading="Panduan Penggerak Jelajah"
               subheading="Semua yang anda perlu tahu untuk membawa BeDaie ke kawasan anda.">

    {{-- ── Jaminan pembuka ─────────────────────────────────── --}}
    <div class="rounded-card-lg bg-navy-900 p-6 sm:p-8">
        <div class="relative">
            <div class="motif-girih-dark absolute inset-0 opacity-50" aria-hidden="true"></div>
            <div class="relative max-w-2xl">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-300">Mulakan di sini</p>
                <h2 class="mt-3 font-display text-2xl text-white sm:text-3xl text-pretty">
                    Anda tidak perlu menjadi penganjur profesional.
                </h2>
                <p class="mt-4 leading-relaxed text-white/75 text-pretty">
                    Ramai Penggerak kami adalah ahli kariah biasa yang menganjurkan program
                    pertama dalam hidup mereka. Isi satu borang, sebarkan satu pautan —
                    selebihnya kami uruskan.
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <x-ui.button :href="route('jemput')" variant="primary" icon="heart">
                        Hantar Permohonan
                    </x-ui.button>
                    <x-ui.button :href="'https://wa.me/'.config('jelajah.support.phone')"
                                 target="_blank" rel="noopener" variant="glass" icon="whatsapp">
                        Tanya Kami di WhatsApp
                    </x-ui.button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Enam langkah ────────────────────────────────────── --}}
    <section class="mt-10">
        <h2 class="text-lg font-semibold text-navy-900">Bagaimana Ia Berfungsi</h2>

        <ol class="mt-5 space-y-4">
            @foreach ($steps as $i => $step)
                <li class="rounded-card border border-hairline bg-surface p-5">
                    <div class="flex items-start gap-4">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-brand-50">
                            <x-ui.icon :name="$step['icon']" class="h-5 w-5 text-brand-700" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-baseline gap-2.5">
                                <span class="text-sm font-semibold text-brand-700">{{ $i + 1 }}</span>
                                <h3 class="font-semibold text-navy-900">{{ $step['title'] }}</h3>
                            </div>
                            <p class="mt-2 leading-relaxed text-ink-soft text-pretty">{{ $step['body'] }}</p>
                            <p class="mt-3 rounded-xl bg-mist px-3.5 py-2.5 text-sm text-ink-soft text-pretty">
                                {{ $step['note'] }}
                            </p>
                        </div>
                    </div>
                </li>
            @endforeach
        </ol>
    </section>

    {{-- ── Soalan lazim ────────────────────────────────────── --}}
    <section class="mt-10">
        <h2 class="text-lg font-semibold text-navy-900">Soalan Lazim</h2>

        <div class="mt-5 divide-y divide-hairline overflow-hidden rounded-card border border-hairline bg-surface">
            @foreach ($faqs as [$question, $answer])
                <details class="group">
                    <summary class="tap-target flex cursor-pointer items-center justify-between gap-4 px-5
                                    text-sm font-medium text-navy-900">
                        {{ $question }}
                        <x-ui.icon name="chevron-down"
                                   class="h-4 w-4 shrink-0 text-ink-muted transition group-open:rotate-180" />
                    </summary>
                    <p class="px-5 pb-4 leading-relaxed text-ink-soft text-pretty">{{ $answer }}</p>
                </details>
            @endforeach
        </div>
    </section>

    {{-- ── Bantuan ─────────────────────────────────────────── --}}
    <div class="mt-10 rounded-card border border-brand-200 bg-brand-50 p-6">
        <h2 class="font-semibold text-navy-900">Masih ada soalan?</h2>
        <p class="mt-2 text-ink-soft text-pretty">
            Kami lebih suka anda bertanya daripada tersekat. Hubungi terus — tiada soalan yang bodoh.
        </p>
        <x-ui.button :href="'https://wa.me/'.config('jelajah.support.phone')"
                     target="_blank" rel="noopener" variant="whatsapp" class="mt-4" icon="whatsapp">
            WhatsApp Pasukan BeDaie
        </x-ui.button>
    </div>
</x-layouts.app>
