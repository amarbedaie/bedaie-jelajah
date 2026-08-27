<x-layouts.public
    title="BeDaie Jelajah — Membawa Ilmu, Menghidupkan Ummah"
    description="Gerakan ilmu yang menghubungkan BeDaie dengan masjid, surau, sekolah, tahfiz dan komuniti di seluruh Malaysia.">

    {{-- ══ A. HERO ═══════════════════════════════════════════════
         Kertas, bukan slab gelap. Tajuk serif memikul beban; motif
         khatam hanya tekstur yang hampir tidak kelihatan. --}}
    <section class="relative overflow-hidden border-b border-hairline">
        <div class="motif-girih pointer-events-none absolute inset-0 opacity-40" aria-hidden="true"></div>

        <div class="jelajah-container relative py-14 sm:py-20 lg:py-28">
            <div class="grid items-start gap-14 lg:grid-cols-[minmax(0,1.25fr)_minmax(0,1fr)] lg:gap-20">
                <div class="max-w-2xl">
                    <p class="flex items-center gap-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-clay-700">
                        <span class="h-px w-8 bg-clay-400"></span>
                        {{ config('jelajah.slogan') }}
                    </p>

                    <h1 class="mt-8 font-display text-[2.75rem] leading-[0.98] text-ink sm:text-[4.5rem] lg:text-[6rem]">
                        Bawa BeDaie<br>ke kawasan anda.
                    </h1>

                    <p class="mt-7 max-w-xl text-lg leading-relaxed text-ink-soft text-pretty sm:text-xl">
                        Gerakan ilmu yang menghubungkan BeDaie dengan masjid, surau, sekolah,
                        tahfiz dan komuniti di seluruh Malaysia.
                        <span class="text-ink">Anda tidak perlu menjadi penganjur profesional</span> —
                        pasukan kami uruskan tarikh, penceramah, pendaftaran, kehadiran dan sijil.
                    </p>

                    <div class="mt-9 flex flex-col gap-3 sm:flex-row sm:items-center">
                        <x-ui.button :href="route('jemput')" variant="primary" size="lg">
                            Jemput BeDaie
                        </x-ui.button>
                        <x-ui.button :href="route('program.index')" variant="outline" size="lg">
                            Lihat Program Akan Datang
                        </x-ui.button>
                    </div>

                    <p class="mt-7 text-sm text-ink-muted">
                        Lebih suka bertanya dahulu?
                        <a href="https://wa.me/{{ config('jelajah.support.phone') }}?text={{ rawurlencode('Assalamualaikum. Saya ingin bertanya tentang BeDaie Jelajah untuk kawasan saya.') }}"
                           target="_blank" rel="noopener"
                           class="font-medium text-clay-700 underline decoration-clay-300 underline-offset-4 hover:decoration-clay-700"
                        >WhatsApp kami terus</a>.
                    </p>
                </div>

                {{-- Kad program terdekat — satu-satunya kad pada halaman ini,
                     supaya ia benar-benar bermakna. --}}
                @if ($upcoming->isNotEmpty())
                    @php $next = $upcoming->first(); @endphp
                    <div class="rounded-card-lg border border-hairline bg-surface p-6 shadow-soft sm:p-7">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-clay-700">
                            Program Terdekat
                        </p>

                        <h2 class="mt-3 font-display text-2xl leading-snug text-ink text-pretty">
                            {{ $next->title }}
                        </h2>

                        <dl class="mt-5 space-y-2.5 text-sm text-ink-soft">
                            <div class="flex items-start gap-3">
                                <dt class="sr-only">Tarikh</dt>
                                <x-ui.icon name="calendar" class="mt-0.5 h-4 w-4 shrink-0 text-clay-500" />
                                <dd>{{ $next->dateLabel() }} &middot; {{ $next->timeLabel() }}</dd>
                            </div>
                            <div class="flex items-start gap-3">
                                <dt class="sr-only">Lokasi</dt>
                                <x-ui.icon name="pin" class="mt-0.5 h-4 w-4 shrink-0 text-clay-500" />
                                <dd class="text-pretty">{{ $next->locationLabel() }}</dd>
                            </div>
                            @if ($next->speaker)
                                <div class="flex items-start gap-3">
                                    <dt class="sr-only">Penceramah</dt>
                                    <x-ui.icon name="user" class="mt-0.5 h-4 w-4 shrink-0 text-clay-500" />
                                    <dd>{{ $next->speaker->name }}</dd>
                                </div>
                            @endif
                        </dl>

                        @if ($next->countdownTarget())
                            <div x-data="countdown('{{ $next->countdownTarget()->toIso8601String() }}')"
                                 class="mt-6 flex items-baseline gap-5 border-t border-hairline pt-5" x-cloak>
                                @foreach (['hari' => 'hari', 'jam' => 'jam', 'minit' => 'minit'] as $key => $label)
                                    <div>
                                        <span class="font-display text-3xl text-ink" x-text="{{ $key }}">—</span>
                                        <span class="ml-1 text-sm text-ink-muted">{{ $label }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <x-ui.button :href="$next->publicUrl()" variant="primary" block class="mt-6">
                            {{ $next->isFull() ? 'Sertai Senarai Menunggu' : 'Daftar Sekarang' }}
                        </x-ui.button>
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- ══ B. KAUNTER IMPAK ══════════════════════════════════════ --}}
    <section class="border-b border-hairline bg-surface" aria-labelledby="tajuk-impak">
        <div class="jelajah-container py-10 sm:py-12">
            <h2 id="tajuk-impak" class="sr-only">Impak BeDaie Jelajah setakat ini</h2>

            {{-- Nombor sebagai teks penerbitan, bukan jubin ikon. Pembahagi
                 rambut menggantikan kad, sejajar dengan seluruh sistem. --}}
            <dl class="grid grid-cols-2 gap-y-9 sm:grid-cols-3 lg:grid-cols-5">
                @foreach ([
                    ['negeri', 'Negeri dijelajahi'],
                    ['daerah', 'Daerah dikunjungi'],
                    ['program', 'Program dilaksanakan'],
                    ['peserta', 'Peserta disantuni'],
                    ['rakan', 'Rakan masjid & organisasi'],
                ] as [$key, $label])
                    <div class="border-l border-hairline pl-5 first:border-l-0 first:pl-0
                                sm:[&:nth-child(4)]:border-l-0 sm:[&:nth-child(4)]:pl-0
                                lg:[&:nth-child(4)]:border-l lg:[&:nth-child(4)]:pl-5">
                        <dd class="font-display text-5xl leading-none text-ink tabular-nums sm:text-[4.5rem]"
                            x-data="counter({{ (int) $headline[$key] }})"
                            x-text="display.toLocaleString('ms-MY')">{{ number_format($headline[$key]) }}</dd>
                        <dt class="mt-2.5 text-sm leading-snug text-ink-muted text-pretty">{{ $label }}</dt>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    {{-- ══ C. PETA JELAJAH ═══════════════════════════════════════ --}}
    <section class="bg-cream py-16 sm:py-20" aria-labelledby="tajuk-peta">
        <div class="jelajah-container">
            <x-ui.section-heading
                eyebrow="Peta Jelajah"
                id="tajuk-peta"
                title="Ke Mana BeDaie Telah Sampai"
                description="Setiap warna menceritakan perjalanan kami — dan kawasan yang masih menanti."
                :action="route('peta')"
                actionLabel="Buka Peta Penuh" />

            <div class="mt-9">
                <x-jelajah.map :states="$states" />
            </div>
        </div>
    </section>

    {{-- ══ D. PROGRAM AKAN DATANG ════════════════════════════════ --}}
    <section class="bg-surface py-16 sm:py-20" aria-labelledby="tajuk-program">
        <div class="jelajah-container">
            <x-ui.section-heading
                eyebrow="Jangan Terlepas"
                id="tajuk-program"
                title="Program Akan Datang"
                description="Tempat adalah terhad. Daftar awal untuk memastikan tempat anda."
                :action="route('program.index')"
                actionLabel="Semua Program" />

            @if ($upcoming->isEmpty())
                <x-ui.empty-state class="mt-9" icon="calendar"
                    title="Belum ada program yang dibuka"
                    description="Program baharu diumumkan setiap bulan. Anda boleh menjemput BeDaie ke kawasan anda.">
                    <x-ui.button :href="route('jemput')" variant="primary" class="mt-5">Jemput BeDaie</x-ui.button>
                </x-ui.empty-state>
            @else
                <div class="mt-9 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($upcoming as $event)
                        <x-jelajah.event-card :event="$event" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- ══ E. CARA MEMBAWA BEDAIE ════════════════════════════════ --}}
    <section class="relative overflow-hidden border-y border-hairline bg-cream py-16 sm:py-24"
             aria-labelledby="tajuk-cara">
        <div class="motif-girih pointer-events-none absolute inset-0 opacity-35" aria-hidden="true"></div>

        <div class="jelajah-container relative">
            <div class="max-w-2xl">
                <p class="flex items-center gap-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-clay-700">
                    <span class="h-px w-8 bg-clay-400"></span>
                    Mudah Sahaja
                </p>
                <h2 id="tajuk-cara" class="mt-6 font-display text-3xl leading-tight text-ink sm:text-[2.75rem]">
                    Cara membawa BeDaie ke kawasan anda
                </h2>
                <p class="mt-5 text-lg leading-relaxed text-ink-soft text-pretty">
                    Anda tidak perlu menjadi penganjur profesional. Isi satu borang ringkas —
                    pasukan BeDaie akan uruskan selebihnya.
                </p>
            </div>

            {{-- Senarai bernombor, bukan lima kad serupa. Nombor serif dan
                 garis rambut membawa urutan; urutan itu memang maklumat. --}}
            <ol class="mt-12 grid gap-x-10 gap-y-9 sm:grid-cols-2 lg:grid-cols-5 lg:gap-x-6">
                @foreach ([
                    ['Mohon', 'Isi borang ringkas — empat langkah sahaja.'],
                    ['Kami hubungi', 'Pasukan BeDaie menghubungi anda melalui WhatsApp.'],
                    ['Program disahkan', 'Tarikh, penceramah dan lokasi ditetapkan.'],
                    ['Sebarkan link', 'Anda terima link, QR dan poster rasmi.'],
                    ['BeDaie hadir', 'Kami datang. Peserta imbas QR. Sijil automatik.'],
                ] as $i => [$title, $body])
                    <li class="border-t border-clay-300 pt-5">
                        <span class="font-display text-2xl text-clay-700">{{ $i + 1 }}</span>
                        <h3 class="mt-2 font-semibold text-ink">{{ $title }}</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-ink-soft text-pretty">{{ $body }}</p>
                    </li>
                @endforeach
            </ol>

            <div class="mt-12">
                <x-ui.button :href="route('jemput')" variant="primary" size="lg">
                    Mula Permohonan Sekarang
                </x-ui.button>
            </div>
        </div>
    </section>

    {{-- ══ F. PILIHAN PROGRAM ════════════════════════════════════ --}}
    <section class="bg-surface py-16 sm:py-20" aria-labelledby="tajuk-kategori">
        <div class="jelajah-container">
            <x-ui.section-heading
                eyebrow="Pilihan Program"
                id="tajuk-kategori"
                title="Program Yang Boleh Anda Mohon"
                description="Pilih yang paling sesuai dengan keperluan komuniti anda. Pasukan BeDaie akan menentukan penceramah dan pengisian." />

            {{-- Ini taksonomi, bukan sepuluh produk. Senarai takrif dua
                 lajur dengan satu CTA dikongsi — bukan sepuluh CTA serupa. --}}
            <dl class="mt-10 grid gap-x-14 border-t border-hairline sm:grid-cols-2">
                @foreach ($categories as $category)
                    <div class="group border-b border-hairline py-5 sm:py-6">
                        <dt>
                            <a href="{{ route('jemput', ['kategori' => $category->slug]) }}"
                               class="flex items-baseline justify-between gap-4 font-display text-xl text-ink
                                      transition-colors hover:text-clay-700">
                                <span class="text-pretty">{{ $category->name }}</span>
                                <x-ui.icon name="arrow-right"
                                           class="h-4 w-4 shrink-0 translate-y-0.5 text-clay-500 opacity-0
                                                  transition group-hover:opacity-100" />
                                <span class="sr-only">— jemput BeDaie untuk program ini</span>
                            </a>
                        </dt>
                        <dd class="mt-2 max-w-prose text-sm leading-relaxed text-ink-soft text-pretty">
                            {{ $category->description }}
                        </dd>
                    </div>
                @endforeach
            </dl>

            <div class="mt-9">
                <x-ui.button :href="route('jemput')" variant="primary">
                    Jemput BeDaie ke Kawasan Anda
                </x-ui.button>
            </div>
        </div>
    </section>

    {{-- ══ G. JEJAK JELAJAH TERKINI ══════════════════════════════ --}}
    @if ($recent->isNotEmpty())
        <section class="bg-cream py-16 sm:py-20" aria-labelledby="tajuk-jejak">
            <div class="jelajah-container">
                <x-ui.section-heading
                    eyebrow="Jejak Jelajah"
                    id="tajuk-jejak"
                    title="Program Yang Telah Kami Laksanakan"
                    description="Setiap program meninggalkan kesan — pada ilmu, pada hati, pada komuniti."
                    :action="route('jejak')"
                    actionLabel="Semua Jejak" />

                {{-- Rekod lepas ialah senarai, bukan grid kad. Tarikh,
                     tempat, kehadiran — satu garis rambut setiap baris.
                     Ini juga menamatkan tajuk yang berulang dua kali. --}}
                <ul class="mt-10 divide-y divide-hairline border-t border-hairline">
                    @foreach ($recent as $event)
                        <li>
                            <a href="{{ $event->publicUrl() }}"
                               class="group grid gap-2 py-6 sm:grid-cols-[9rem_minmax(0,1fr)_auto] sm:items-baseline sm:gap-6">
                                <span class="text-sm text-ink-muted tabular-nums">{{ $event->dateLabel() }}</span>

                                <span class="min-w-0">
                                    <span class="block font-display text-xl leading-snug text-ink text-pretty
                                                 group-hover:text-clay-700">{{ $event->title }}</span>
                                    <span class="mt-1 block text-sm text-ink-soft text-pretty">
                                        {{ $event->locationLabel() }}
                                    </span>
                                </span>

                                <span class="flex items-baseline gap-1.5 sm:justify-self-end">
                                    <span class="font-display text-2xl text-ink tabular-nums">
                                        {{ number_format($event->attended_count) }}
                                    </span>
                                    <span class="text-sm text-ink-muted">hadir</span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- ══ H. PERMINTAAN KOMUNITI ════════════════════════════════ --}}
    <section class="bg-surface py-16 sm:py-20">
        <div class="jelajah-container">
            <div class="relative overflow-hidden rounded-card-lg bg-clay-50 px-6 py-12 sm:px-12 sm:py-16">
                <div class="motif-girih absolute inset-0 opacity-70" aria-hidden="true"></div>

                <div class="relative mx-auto max-w-2xl text-center">
                    <h2 class="font-display text-3xl leading-tight text-ink sm:text-4xl text-pretty">
                        BeDaie Belum Sampai ke Kawasan Anda?
                    </h2>
                    <p class="mt-4 text-ink-soft text-pretty">
                        Beritahu kami. Setiap permintaan direkodkan dan membantu kami merancang
                        jelajah seterusnya — kawasan yang paling banyak diminta akan diutamakan.
                    </p>
                    <p class="mt-3 text-sm text-ink-muted text-pretty">
                        Belum ada lokasi tertentu? Ini cukup — dua medan sahaja, bukan borang penuh.
                    </p>
                    <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                        <x-ui.button :href="route('minat')" variant="primary" size="lg">
                            Beritahu Kami Kawasan Anda
                        </x-ui.button>
                        <x-ui.button :href="route('peta')" variant="outline" size="lg">
                            Lihat Peta Jelajah
                        </x-ui.button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ══ I. TESTIMONI ══════════════════════════════════════════ --}}
    @if ($testimonials->isNotEmpty())
        <section class="bg-cream py-16 sm:py-20" aria-labelledby="tajuk-testimoni">
            <div class="jelajah-container">
                <x-ui.section-heading
                    eyebrow="Suara Mereka"
                    id="tajuk-testimoni"
                    title="Apa Kata Penggerak, Masjid dan Peserta" />

                {{-- Testimoni bercakap dengan kuat apabila ia satu suara,
                     bukan empat kad dalam grid tiga lajur yang tinggal
                     baris kosong. --}}
                @php $lead = $testimonials->first(); $rest = $testimonials->skip(1)->take(2); @endphp

                <figure class="mt-12 border-t border-clay-300 pt-9">
                    <blockquote class="max-w-4xl font-display text-[1.75rem] leading-[1.32] text-ink text-pretty sm:text-[2.25rem]">
                        &ldquo;{{ $lead->quote }}&rdquo;
                    </blockquote>
                    <figcaption class="mt-7 flex flex-wrap items-baseline gap-x-3 gap-y-1">
                        <span class="font-medium text-ink">{{ $lead->name }}</span>
                        <span class="text-sm text-ink-muted">{{ $lead->role_label }}</span>
                    </figcaption>
                </figure>

                @if ($rest->isNotEmpty())
                    <div class="mt-12 grid gap-10 border-t border-hairline pt-9 sm:grid-cols-2">
                        @foreach ($rest as $testimonial)
                            <figure>
                                <blockquote class="leading-relaxed text-ink-soft text-pretty">
                                    &ldquo;{{ $testimonial->quote }}&rdquo;
                                </blockquote>
                                <figcaption class="mt-4 flex flex-wrap items-baseline gap-x-3 gap-y-1">
                                    <span class="text-sm font-medium text-ink">{{ $testimonial->name }}</span>
                                    <span class="text-sm text-ink-muted">{{ $testimonial->role_label }}</span>
                                </figcaption>
                            </figure>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- ══ J. RAKAN & PENAJA ═════════════════════════════════════ --}}
    <section class="border-t border-hairline bg-surface py-14">
        <div class="jelajah-container">
            <div class="flex flex-col items-center gap-8 text-center">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-clay-600">Rakan Jelajah</p>
                    <h2 class="mt-2.5 font-display text-2xl text-ink sm:text-3xl">
                        Bersama Menghidupkan Ummah
                    </h2>
                </div>

                @if ($partners->isNotEmpty())
                    <ul class="flex flex-wrap items-center justify-center gap-x-10 gap-y-6">
                        @foreach ($partners as $partner)
                            <li>
                                @if ($partner->logo_path)
                                    <img src="{{ Storage::url($partner->logo_path) }}" alt="{{ $partner->name }}"
                                         loading="lazy" class="h-10 w-auto opacity-60 grayscale transition
                                                hover:opacity-100 hover:grayscale-0" />
                                @else
                                    <span class="rounded-xl bg-mist px-4 py-2 text-sm font-medium text-ink-soft">
                                        {{ $partner->name }}
                                    </span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif

                <x-ui.button :href="route('rakan')" variant="outline" icon="handshake">
                    Jadi Rakan Jelajah BeDaie
                </x-ui.button>
            </div>
        </div>
    </section>
</x-layouts.public>
