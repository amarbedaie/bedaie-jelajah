<x-layouts.public title="Tentang BeDaie Jelajah"
                  description="Gerakan ilmu yang menghubungkan BeDaie dengan masjid, surau, sekolah, tahfiz dan komuniti di seluruh Malaysia.">

    <x-jelajah.page-hero
        eyebrow="Tentang Kami"
        title="Membawa Ilmu, Menghidupkan Ummah"
        lead="BeDaie Jelajah ialah gerakan ilmu yang membawa pengajian BeDaie keluar dari skrin, terus ke masjid, surau, sekolah, tahfiz dan dewan komuniti di seluruh Malaysia." />

    <section class="border-b border-hairline bg-surface">
        <div class="jelajah-container py-10">
            <dl class="grid grid-cols-2 gap-x-4 gap-y-8 sm:grid-cols-3 lg:grid-cols-5">
                @foreach ([
                    ['negeri', 'Negeri Dijelajahi'], ['daerah', 'Daerah Dikunjungi'],
                    ['program', 'Program Dilaksanakan'], ['peserta', 'Peserta Disantuni'],
                    ['rakan', 'Rakan Masjid & Organisasi'],
                ] as [$key, $label])
                    <div>
                        <dd class="font-display text-3xl text-navy-900 sm:text-4xl">{{ number_format($headline[$key]) }}</dd>
                        <dt class="mt-1 text-sm text-ink-soft text-pretty">{{ $label }}</dt>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    <section class="jelajah-container py-14 sm:py-20">
        <div class="grid gap-12 lg:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)]">
            <div class="prose-jelajah">
                <h2>Kenapa Jelajah?</h2>
                <p>
                    Ramai yang mahu belajar, tetapi tidak semua mampu datang ke kelas di bandar besar.
                    Ada yang jauh, ada yang sibuk, ada yang tidak tahu ke mana hendak bermula.
                    BeDaie Jelajah membalikkan arah itu — <strong>kami yang datang kepada mereka</strong>.
                </p>

                <h2>Bukan marketplace penceramah</h2>
                <p>
                    Platform ini bukan tempat memilih penceramah seperti sebuah pasar. Setiap
                    permohonan dinilai oleh pasukan BeDaie berdasarkan keperluan sebenar komuniti,
                    kesesuaian lokasi dan keutamaan jelajah. Kami yang menentukan penceramah dan
                    pengisian yang paling sesuai.
                </p>

                <h2>Penggerak Jelajah</h2>
                <p>
                    Setiap program bermula dengan seorang <em>Penggerak</em> — bekas pelajar BeDaie,
                    ahli kariah, wakil masjid, guru, atau sesiapa sahaja yang mahu membawa ilmu ke
                    kawasannya. Anda tidak perlu menjadi penganjur profesional. Isi satu borang,
                    dan kami uruskan selebihnya: halaman program, pendaftaran, QR kehadiran,
                    laporan dan sijil.
                </p>

                <h2>Apa yang kami sediakan</h2>
                <ul>
                    <li>Halaman program rasmi yang dijana automatik</li>
                    <li>Pautan dan kod QR pendaftaran untuk disebarkan</li>
                    <li>Poster rasmi berjenama BeDaie</li>
                    <li>Sistem kehadiran QR pada hari program</li>
                    <li>Sijil digital automatik untuk peserta yang hadir</li>
                    <li>Laporan impak selepas program</li>
                </ul>
            </div>

            <aside class="space-y-5">
                <div class="rounded-[--radius-card-lg] bg-navy-900 p-7 text-white">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-300">Slogan Kami</p>
                    <p class="mt-4 font-display text-2xl leading-tight">
                        {{ config('jelajah.slogan') }}
                    </p>
                    <p class="mt-5 text-sm leading-relaxed text-white/65 text-pretty">
                        Setiap masjid yang membuka pintu, setiap hati yang terbuka menerima —
                        itulah ukuran sebenar jelajah ini.
                    </p>
                    <p class="mt-6 inline-flex items-center gap-2 rounded-full bg-white/8 px-4 py-2
                              text-sm text-brand-300 ring-1 ring-white/10">
                        <x-ui.icon name="home" class="h-4 w-4" /> {{ config('jelajah.motto') }}
                    </p>
                </div>

                <x-ui.card>
                    <h3 class="font-semibold text-navy-900">Dianjurkan oleh</h3>
                    <p class="mt-2 text-sm text-ink-soft">
                        <strong class="text-navy-900">BeDaie</strong><br>
                        {{ config('jelajah.org') }}
                    </p>
                    <x-ui.button :href="config('jelajah.website')" target="_blank" rel="noopener"
                                 variant="outline" size="sm" block class="mt-4" icon="external">
                        Laman Rasmi BeDaie
                    </x-ui.button>
                </x-ui.card>

                <x-ui.card>
                    <h3 class="font-semibold text-navy-900">Mula Sekarang</h3>
                    <div class="mt-4 grid gap-2.5">
                        <x-ui.button :href="route('jemput')" variant="primary" block icon="heart">
                            Jemput BeDaie
                        </x-ui.button>
                        <x-ui.button :href="route('peta')" variant="outline" block icon="map">
                            Lihat Peta Jelajah
                        </x-ui.button>
                    </div>
                </x-ui.card>
            </aside>
        </div>
    </section>

    @if ($recent->isNotEmpty())
        <section class="border-t border-hairline bg-surface py-14">
            <div class="jelajah-container">
                <x-ui.section-heading eyebrow="Jejak Terkini" title="Program Terbaharu Kami"
                                      :action="route('jejak')" actionLabel="Semua Jejak" />
                <ul class="mt-8 grid gap-4 sm:grid-cols-2">
                    @foreach ($recent as $event)
                        <li>
                            <a href="{{ $event->publicUrl() }}"
                               class="flex items-center justify-between gap-4 rounded-[--radius-card] border
                                      border-hairline bg-surface p-5 transition hover:border-brand-200 hover:shadow-soft">
                                <div class="min-w-0">
                                    <p class="font-medium text-navy-900 text-pretty">{{ $event->title }}</p>
                                    <p class="mt-0.5 text-sm text-ink-muted">
                                        {{ $event->dateLabel() }} &middot; {{ $event->state?->name }}
                                    </p>
                                </div>
                                <x-ui.icon name="arrow-right" class="h-5 w-5 shrink-0 text-ink-muted" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    @if ($testimonials->isNotEmpty())
        <section class="bg-cream py-14">
            <div class="jelajah-container">
                <x-ui.section-heading eyebrow="Suara Mereka" title="Apa Kata Komuniti" />
                <div class="mt-8 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($testimonials as $testimonial)
                        <figure class="flex h-full flex-col rounded-[--radius-card] border border-hairline bg-surface p-6">
                            <blockquote class="flex-1 leading-relaxed text-ink-soft text-pretty">
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
</x-layouts.public>
