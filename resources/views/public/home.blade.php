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

                    <h1 class="mt-7 font-display text-[2.75rem] leading-[1.02] text-ink sm:text-6xl lg:text-[4.25rem]">
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
                        <dd class="font-display text-4xl leading-none text-ink sm:text-5xl"
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

            <div class="mt-9 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                @foreach ($categories as $category)
                    {{-- Seluruh kad ialah sasaran ketuk; pautan teks sahaja hanya 20px tinggi. --}}
                    <div class="group relative flex h-full flex-col rounded-card border border-hairline
                                bg-surface p-5 transition hover:border-clay-200 hover:shadow-soft
                                focus-within:border-clay-400 focus-within:ring-4 focus-within:ring-clay-400/15">
                        <div class="grid h-11 w-11 place-items-center rounded-2xl bg-clay-50
                                    transition group-hover:bg-clay-600">
                            <x-ui.icon :name="$category->icon ?? 'book'"
                                       class="h-5 w-5 text-clay-700 transition group-hover:text-white" />
                        </div>
                        <h3 class="mt-4 font-semibold text-ink">
                            <a href="{{ route('jemput', ['kategori' => $category->slug]) }}"
                               class="after:absolute after:inset-0 focus-visible:outline-none">
                                {{ $category->name }}
                                <span class="sr-only">— jemput BeDaie untuk program ini</span>
                            </a>
                        </h3>
                        <p class="mt-1.5 flex-1 text-sm leading-relaxed text-ink-soft text-pretty">
                            {{ $category->description }}
                        </p>
                        <span aria-hidden="true"
                              class="mt-4 inline-flex items-center gap-1.5 text-sm font-medium text-clay-700">
                            Jemput BeDaie
                            <x-ui.icon name="arrow-right" class="h-4 w-4 transition group-hover:translate-x-0.5" />
                        </span>
                    </div>
                @endforeach
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

                <div class="mt-9 grid gap-6 lg:grid-cols-3">
                    @foreach ($recent as $event)
                        <article class="overflow-hidden rounded-card border border-hairline bg-surface">
                            <a href="{{ $event->publicUrl() }}"
                               class="relative block aspect-[16/9] border-b border-hairline bg-cream">
                                @if ($event->heroUrl())
                                    <img src="{{ $event->heroUrl() }}" alt="{{ $event->title }}" loading="lazy"
                                         class="h-full w-full object-cover" />
                                @else
                                    <div class="motif-girih absolute inset-0 opacity-60" aria-hidden="true"></div>
                                @endif
                            </a>

                            <div class="border-b border-hairline px-5 py-3">
                                <p class="text-sm text-ink-soft text-pretty">{{ $event->locationLabel() }}</p>
                            </div>

                            <div class="p-5">
                                <p class="text-xs text-ink-muted">{{ $event->dateLabel() }}</p>
                                <h3 class="mt-1.5 font-display text-lg leading-snug text-ink text-pretty">
                                    <a href="{{ $event->publicUrl() }}" class="hover:text-clay-700">{{ $event->title }}</a>
                                </h3>

                                <div class="mt-4 flex flex-wrap gap-2">
                                    <x-ui.badge color="purple" icon="users">
                                        {{ number_format($event->attended_count) }} hadir
                                    </x-ui.badge>
                                    @if ($event->averageRating())
                                        <x-ui.badge color="success" icon="star">
                                            {{ number_format($event->averageRating(), 1) }} / 5
                                        </x-ui.badge>
                                    @endif
                                    @if ($event->gallery_count)
                                        <x-ui.badge color="grey" icon="image">{{ $event->gallery_count }} gambar</x-ui.badge>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
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
                        <x-ui.button :href="route('minat')" variant="navy" size="lg" icon="pin">
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

                <div class="mt-9 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($testimonials as $testimonial)
                        <figure class="flex h-full flex-col rounded-card border border-hairline bg-surface p-6 shadow-soft">
                            @if ($testimonial->rating)
                                <div class="flex gap-0.5" aria-label="{{ $testimonial->rating }} daripada 5 bintang">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <x-ui.icon name="star"
                                            :fill="$i <= $testimonial->rating ? 'currentColor' : 'none'"
                                            class="h-4 w-4 {{ $i <= $testimonial->rating ? 'text-warning' : 'text-hairline' }}" />
                                    @endfor
                                </div>
                            @endif

                            <blockquote class="mt-4 flex-1 leading-relaxed text-ink-soft text-pretty">
                                &ldquo;{{ $testimonial->quote }}&rdquo;
                            </blockquote>

                            <figcaption class="mt-5 border-t border-hairline pt-4">
                                <p class="font-medium text-ink">{{ $testimonial->name }}</p>
                                <p class="mt-0.5 text-sm text-ink-muted">{{ $testimonial->role_label }}</p>
                            </figcaption>
                        </figure>
                    @endforeach
                </div>
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
