<x-layouts.public
    title="BeDaie Jelajah — Membawa Ilmu, Menghidupkan Ummah"
    description="Gerakan ilmu yang menghubungkan BeDaie dengan masjid, surau, sekolah, tahfiz dan komuniti di seluruh Malaysia.">

    {{-- ══ A. HERO ═══════════════════════════════════════════════ --}}
    <section class="relative overflow-hidden bg-navy-900">
        <div class="motif-girih-dark absolute inset-0 opacity-70" aria-hidden="true"></div>
        <div class="absolute -right-32 -top-40 h-[34rem] w-[34rem] rounded-full bg-brand-500/25 blur-3xl" aria-hidden="true"></div>
        <div class="absolute -bottom-40 -left-24 h-96 w-96 rounded-full bg-brand-700/20 blur-3xl" aria-hidden="true"></div>

        <div class="jelajah-container relative py-16 sm:py-24 lg:py-28">
            <div class="grid items-center gap-12 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,1fr)]">
                <div>
                    <p class="inline-flex items-center gap-2 rounded-full bg-white/8 px-4 py-1.5 text-xs
                              font-semibold uppercase tracking-[0.18em] text-brand-300 ring-1 ring-white/10">
                        <span class="h-1.5 w-1.5 rounded-full bg-success"></span>
                        {{ config('jelajah.slogan') }}
                    </p>

                    <h1 class="mt-6 font-display text-5xl leading-[1.05] tracking-tight text-white sm:text-6xl lg:text-7xl">
                        BeDaie Jelajah
                    </h1>

                    <p class="mt-4 text-2xl font-light text-brand-200 sm:text-3xl">
                        {{ config('jelajah.tagline') }}
                    </p>

                    <p class="mt-6 max-w-xl text-lg leading-relaxed text-white/70 text-pretty">
                        Gerakan ilmu yang menghubungkan BeDaie dengan masjid, surau, sekolah,
                        tahfiz dan komuniti di seluruh Malaysia.
                    </p>

                    <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                        <x-ui.button :href="route('jemput')" variant="primary" size="lg" icon="heart">
                            Jemput BeDaie ke Kawasan Anda
                        </x-ui.button>
                        <x-ui.button :href="route('program.index')" variant="glass" size="lg" icon="calendar">
                            Lihat Program Akan Datang
                        </x-ui.button>
                    </div>

                    {{-- Jaminan pada saat keputusan, bukan 4,000px ke bawah. --}}
                    <p class="mt-5 max-w-md text-sm leading-relaxed text-white/70 text-pretty">
                        Anda tidak perlu menjadi penganjur profesional. Empat langkah ringkas —
                        pasukan BeDaie uruskan tarikh, penceramah, pendaftaran dan sijil.
                    </p>

                    <p class="mt-4 text-sm text-white/60">
                        Lebih suka bertanya dahulu?
                        <a href="https://wa.me/{{ config('jelajah.support.phone') }}?text={{ rawurlencode('Assalamualaikum. Saya ingin bertanya tentang BeDaie Jelajah untuk kawasan saya.') }}"
                           target="_blank" rel="noopener"
                           class="font-medium text-brand-200 underline underline-offset-4 hover:text-white"
                        >WhatsApp kami terus</a>.
                    </p>
                </div>

                {{-- Kad program terdekat --}}
                @if ($upcoming->isNotEmpty())
                    @php $next = $upcoming->first(); @endphp
                    <div class="relative">
                        <div class="rounded-card-lg border border-white/12 bg-white/8 p-5 backdrop-blur-md sm:p-6">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-brand-300">
                                Program Terdekat
                            </p>
                            <h2 class="mt-2.5 text-xl font-semibold leading-snug text-white text-pretty">
                                {{ $next->title }}
                            </h2>

                            <dl class="mt-4 space-y-2 text-sm text-white/75">
                                <div class="flex items-center gap-2.5">
                                    <x-ui.icon name="calendar" class="h-4 w-4 shrink-0 text-brand-300" />
                                    <dd>{{ $next->dateLabel() }} &middot; {{ $next->timeLabel() }}</dd>
                                </div>
                                <div class="flex items-start gap-2.5">
                                    <x-ui.icon name="pin" class="mt-0.5 h-4 w-4 shrink-0 text-brand-300" />
                                    <dd class="text-pretty">{{ $next->locationLabel() }}</dd>
                                </div>
                                @if ($next->speaker)
                                    <div class="flex items-center gap-2.5">
                                        <x-ui.icon name="user" class="h-4 w-4 shrink-0 text-brand-300" />
                                        <dd>{{ $next->speaker->name }}</dd>
                                    </div>
                                @endif
                            </dl>

                            @if ($next->countdownTarget())
                                <div x-data="countdown('{{ $next->countdownTarget()->toIso8601String() }}')"
                                     class="mt-5 grid grid-cols-4 gap-2" x-cloak>
                                    @foreach (['hari' => 'Hari', 'jam' => 'Jam', 'minit' => 'Minit', 'saat' => 'Saat'] as $key => $label)
                                        <div class="rounded-xl bg-navy-900/50 p-2.5 text-center ring-1 ring-white/10">
                                            <p class="font-display text-2xl text-white" x-text="{{ $key }}">—</p>
                                            <p class="text-xs uppercase tracking-wider text-white/50">{{ $label }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <x-ui.button :href="$next->publicUrl()" variant="primary" block class="mt-5">
                                {{ $next->isFull() ? 'Sertai Senarai Menunggu' : 'Daftar Sekarang' }}
                            </x-ui.button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- ══ B. KAUNTER IMPAK ══════════════════════════════════════ --}}
    <section class="border-b border-hairline bg-surface" aria-labelledby="tajuk-impak">
        <div class="jelajah-container py-10 sm:py-12">
            <h2 id="tajuk-impak" class="sr-only">Impak BeDaie Jelajah setakat ini</h2>

            <dl class="grid grid-cols-2 gap-x-4 gap-y-8 sm:grid-cols-3 lg:grid-cols-5">
                @foreach ([
                    ['negeri', 'Negeri Dijelajahi', 'map'],
                    ['daerah', 'Daerah Dikunjungi', 'pin'],
                    ['program', 'Program Dilaksanakan', 'calendar'],
                    ['peserta', 'Peserta Disantuni', 'users'],
                    ['rakan', 'Rakan Masjid & Organisasi', 'handshake'],
                ] as [$key, $label, $icon])
                    <div class="text-center">
                        <div class="mx-auto grid h-11 w-11 place-items-center rounded-2xl bg-brand-50">
                            <x-ui.icon :name="$icon" class="h-5 w-5 text-brand-600" />
                        </div>
                        <dd class="mt-3 font-display text-3xl text-navy-900 sm:text-4xl"
                            x-data="counter({{ (int) $headline[$key] }})"
                            x-text="display.toLocaleString('ms-MY')">{{ number_format($headline[$key]) }}</dd>
                        <dt class="mt-1 text-sm text-ink-soft text-pretty">{{ $label }}</dt>
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
    <section class="relative overflow-hidden bg-navy-900 py-16 sm:py-20" aria-labelledby="tajuk-cara">
        <div class="motif-girih-dark absolute inset-0 opacity-60" aria-hidden="true"></div>

        <div class="jelajah-container relative">
            <div class="max-w-2xl">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-300">Mudah Sahaja</p>
                <h2 id="tajuk-cara" class="mt-3 font-display text-3xl leading-tight text-white sm:text-4xl">
                    Cara Membawa BeDaie ke Kawasan Anda
                </h2>
                <p class="mt-4 text-white/70 text-pretty">
                    Anda tidak perlu menjadi penganjur profesional. Isi satu borang ringkas —
                    pasukan BeDaie akan uruskan selebihnya.
                </p>
            </div>

            <ol class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-5">
                @foreach ([
                    ['Mohon', 'Isi borang ringkas — empat langkah sahaja.', 'clipboard'],
                    ['Kami Hubungi', 'Pasukan BeDaie menghubungi anda melalui WhatsApp.', 'chat'],
                    ['Program Disahkan', 'Tarikh, penceramah dan lokasi ditetapkan.', 'check-circle'],
                    ['Sebarkan Link', 'Anda terima link, QR dan poster rasmi.', 'share'],
                    ['BeDaie Hadir', 'Kami datang. Peserta imbas QR. Sijil automatik.', 'heart'],
                ] as $i => [$title, $body, $icon])
                    <li class="relative rounded-card border border-white/10 bg-white/6 p-5 backdrop-blur-sm">
                        <div class="flex items-center gap-3">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-brand-500
                                         font-display text-base text-white">{{ $i + 1 }}</span>
                            <x-ui.icon :name="$icon" class="h-5 w-5 text-brand-300" />
                        </div>
                        <h3 class="mt-3.5 font-semibold text-white">{{ $title }}</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-white/60 text-pretty">{{ $body }}</p>
                    </li>
                @endforeach
            </ol>

            <div class="mt-9">
                <x-ui.button :href="route('jemput')" variant="primary" size="lg" icon="heart">
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
                                bg-surface p-5 transition hover:border-brand-200 hover:shadow-soft
                                focus-within:border-brand-400 focus-within:ring-4 focus-within:ring-brand-500/15">
                        <div class="grid h-11 w-11 place-items-center rounded-2xl bg-brand-50
                                    transition group-hover:bg-brand-action">
                            <x-ui.icon :name="$category->icon ?? 'book'"
                                       class="h-5 w-5 text-brand-700 transition group-hover:text-white" />
                        </div>
                        <h3 class="mt-4 font-semibold text-navy-900">
                            <a href="{{ route('jemput', ['kategori' => $category->slug]) }}"
                               class="after:absolute after:inset-0 focus:outline-none">
                                {{ $category->name }}
                                <span class="sr-only">— jemput BeDaie untuk program ini</span>
                            </a>
                        </h3>
                        <p class="mt-1.5 flex-1 text-sm leading-relaxed text-ink-soft text-pretty">
                            {{ $category->description }}
                        </p>
                        <span aria-hidden="true"
                              class="mt-4 inline-flex items-center gap-1.5 text-sm font-medium text-brand-700">
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
                        <article class="overflow-hidden rounded-card border border-hairline bg-surface shadow-soft">
                            <a href="{{ $event->publicUrl() }}" class="relative block aspect-[16/9] bg-navy-900">
                                @if ($event->heroUrl())
                                    <img src="{{ $event->heroUrl() }}" alt="{{ $event->title }}" loading="lazy"
                                         class="h-full w-full object-cover" />
                                @else
                                    <div class="motif-girih-dark absolute inset-0 opacity-60" aria-hidden="true"></div>
                                    <div class="absolute inset-0 bg-gradient-to-tr from-navy-900 to-brand-700/60"></div>
                                @endif
                                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-navy-900/90 to-transparent p-4">
                                    <p class="text-sm font-medium text-white text-pretty">{{ $event->locationLabel() }}</p>
                                </div>
                            </a>

                            <div class="p-5">
                                <p class="text-xs text-ink-muted">{{ $event->dateLabel() }}</p>
                                <h3 class="mt-1.5 font-semibold leading-snug text-navy-900 text-pretty">
                                    <a href="{{ $event->publicUrl() }}" class="hover:text-brand-700">{{ $event->title }}</a>
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
            <div class="relative overflow-hidden rounded-card-lg bg-brand-50 px-6 py-12 sm:px-12 sm:py-16">
                <div class="motif-girih absolute inset-0 opacity-70" aria-hidden="true"></div>

                <div class="relative mx-auto max-w-2xl text-center">
                    <h2 class="font-display text-3xl leading-tight text-navy-900 sm:text-4xl text-pretty">
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
                                <p class="font-medium text-navy-900">{{ $testimonial->name }}</p>
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
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-600">Rakan Jelajah</p>
                    <h2 class="mt-2.5 font-display text-2xl text-navy-900 sm:text-3xl">
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
