@php
    $isPast = $event->hasEnded();
    $seatsLeft = $event->seatsLeft();
    $closedReason = $event->registrationClosedReason();
@endphp

<x-layouts.public
    :title="$event->title.' — BeDaie Jelajah'"
    :description="\Illuminate\Support\Str::limit(strip_tags($event->description), 155)"
    :ogImage="$event->posterUrl()">

    {{-- ══ Hero program ══════════════════════════════════════ --}}
    <section class="relative overflow-hidden border-b border-hairline">
        <div class="motif-girih pointer-events-none absolute inset-0 opacity-40" aria-hidden="true"></div>

        <div class="jelajah-container relative py-12 sm:py-16 lg:py-20">
            <nav aria-label="Laluan" class="mb-8 flex flex-wrap items-center gap-2 text-sm text-ink-muted">
                <a href="{{ route('program.index') }}" class="hover:text-brand-700">Program</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('peta.negeri', $event->state->slug) }}" class="hover:text-brand-700">{{ $event->state->name }}</a>
            </nav>

            <div class="grid items-start gap-10 lg:grid-cols-[minmax(0,1.35fr)_minmax(0,1fr)]">
                <div>
                    <div class="flex flex-wrap gap-2">
                        <x-ui.badge color="purple">{{ $event->category?->name }}</x-ui.badge>
                        <x-ui.badge color="grey">{{ $event->priceLabel() }}</x-ui.badge>
                        @if ($isPast)
                            <x-ui.badge color="grey">Program Selesai</x-ui.badge>
                        @elseif ($event->status === \App\Enums\EventStatus::Berlangsung)
                            <x-ui.badge color="success" dot>Sedang Berlangsung</x-ui.badge>
                        @endif
                    </div>

                    <h1 class="mt-6 font-display text-[2.25rem] leading-[1.06] text-ink text-pretty
                               sm:text-5xl lg:text-[3.5rem]">
                        {{ $event->title }}
                    </h1>

                    @if ($event->theme)
                        <p class="mt-5 text-xl text-brand-700 text-pretty sm:text-2xl">{{ $event->theme }}</p>
                    @endif

                    <dl class="mt-8 grid gap-5 sm:grid-cols-2">
                        <div class="flex gap-3">
                            <x-ui.icon name="calendar" class="mt-0.5 h-5 w-5 shrink-0 text-brand-500" />
                            <div>
                                <dt class="text-xs uppercase tracking-wider text-ink-muted">Tarikh & Masa</dt>
                                <dd class="mt-1 text-ink">{{ $event->dateLabel() }}</dd>
                                <dd class="text-sm text-ink-soft">{{ $event->timeLabel() }}</dd>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <x-ui.icon name="pin" class="mt-0.5 h-5 w-5 shrink-0 text-brand-500" />
                            <div>
                                <dt class="text-xs uppercase tracking-wider text-ink-muted">Lokasi</dt>
                                <dd class="mt-1 text-ink text-pretty">{{ $event->venue?->name ?? $event->locationLabel() }}</dd>
                                <dd class="text-sm text-ink-soft text-pretty">
                                    {{ $event->district?->name ? $event->district->name.', ' : '' }}{{ $event->state->name }}
                                </dd>
                            </div>
                        </div>

                        @if ($event->speaker)
                            <div class="flex gap-3">
                                <x-ui.icon name="user" class="mt-0.5 h-5 w-5 shrink-0 text-brand-500" />
                                <div>
                                    <dt class="text-xs uppercase tracking-wider text-ink-muted">Penceramah</dt>
                                    <dd class="mt-1 text-ink">{{ $event->speaker->name }}</dd>
                                    @if ($event->speaker->title)
                                        <dd class="text-sm text-ink-soft">{{ $event->speaker->title }}</dd>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <div class="flex gap-3">
                            <x-ui.icon name="users" class="mt-0.5 h-5 w-5 shrink-0 text-brand-500" />
                            <div>
                                <dt class="text-xs uppercase tracking-wider text-ink-muted">Sasaran Peserta</dt>
                                <dd class="mt-1 text-ink">{{ $event->target_audience?->label() ?? 'Umum' }}</dd>
                            </div>
                        </div>
                    </dl>

                    @if (! $isPast && $event->countdownTarget())
                        <div x-data="countdown('{{ $event->countdownTarget()->toIso8601String() }}')"
                             class="mt-8 flex flex-wrap gap-2.5" x-cloak>
                            @foreach (['hari' => 'Hari', 'jam' => 'Jam', 'minit' => 'Minit', 'saat' => 'Saat'] as $key => $label)
                                <div class="min-w-[4.5rem] rounded-lg border border-hairline bg-surface px-4 py-3 text-center">
                                    <p class="font-display text-2xl text-ink" x-text="{{ $key }}">—</p>
                                    <p class="text-xs uppercase tracking-wider text-ink-muted">{{ $label }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Kad tindakan --}}
                <div class="lg:sticky lg:top-24">
                    <div class="overflow-hidden rounded-card-lg bg-surface shadow-lift">
                        @if ($event->posterUrl())
                            <img src="{{ $event->posterUrl() }}" alt="Poster {{ $event->title }}"
                                 class="aspect-[4/5] w-full object-cover" />
                        @endif

                        <div class="p-6">
                            @if ($isPast)
                                <p class="text-sm text-ink-soft text-pretty">
                                    Program ini telah selesai pada {{ $event->dateLabel() }}.
                                </p>
                                <div class="mt-5 grid gap-2.5">
                                    <x-ui.button :href="route('program.index')" variant="primary" block>
                                        Lihat Program BeDaie Seterusnya
                                    </x-ui.button>
                                    <x-ui.button :href="route('sijil.semak')" variant="outline" block icon="certificate">
                                        Semak Sijil Saya
                                    </x-ui.button>
                                </div>
                            @else
                                <div class="flex items-baseline justify-between gap-3">
                                    <p class="font-display text-2xl text-ink">{{ $event->priceLabel() }}</p>
                                    @if ($event->capacity)
                                        <p class="text-sm text-ink-muted">
                                            {{ number_format($event->seatsTaken()) }}/{{ number_format($event->capacity) }}
                                        </p>
                                    @endif
                                </div>

                                @if ($event->capacity)
                                    <div class="mt-3">
                                        <x-ui.progress :value="$event->fillPercent()"
                                                       :tone="$event->fillPercent() >= 90 ? 'warning' : 'brand'"
                                                       :showValue="false" />
                                        <p class="mt-2 text-sm {{ $seatsLeft && $seatsLeft <= 20 ? 'font-medium text-brand-700' : 'text-ink-soft' }}">
                                            @if ($event->isFull())
                                                Tempat telah penuh — senarai menunggu dibuka.
                                            @else
                                                {{ number_format($seatsLeft) }} tempat masih tersedia.
                                            @endif
                                        </p>
                                    </div>
                                @endif

                                <div class="mt-5 grid gap-2.5">
                                    @if ($closedReason)
                                        <x-ui.alert variant="warning" icon="alert">{{ $closedReason }}</x-ui.alert>
                                    @else
                                        <x-ui.button :href="route('jelajah.daftar', [$event->state->slug, $event->slug])"
                                                     variant="primary" size="lg" block>
                                            {{ $event->isFull() ? 'Sertai Senarai Menunggu' : 'Daftar Sekarang' }}
                                        </x-ui.button>
                                    @endif

                                    <x-ui.button :href="$event->whatsappShareUrl()" target="_blank" rel="noopener"
                                                 variant="whatsapp" block icon="whatsapp">
                                        Kongsi di WhatsApp
                                    </x-ui.button>

                                    <x-ui.copy-button :text="$event->shortUrl()" label="Salin Link Program"
                                                      variant="outline" block />
                                </div>
                            @endif

                            {{-- QR pendaftaran --}}
                            <div class="mt-6 flex items-center gap-4 border-t border-hairline pt-5">
                                <div class="shrink-0 rounded-xl bg-white p-1.5 ring-1 ring-hairline">
                                    {!! app(\App\Services\QrCodeService::class)->svg($event->shortUrl(), 84) !!}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-ink">Imbas untuk mendaftar</p>
                                    <p class="mt-0.5 break-all font-mono text-xs text-ink-muted">{{ $event->shortUrl() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ══ Kandungan ═════════════════════════════════════════ --}}
    <section class="jelajah-container py-12 sm:py-16">
        <div class="grid gap-10 lg:grid-cols-[minmax(0,1.35fr)_minmax(0,1fr)]">
            <div class="space-y-10">
                {{-- Tentang program --}}
                <div>
                    <h2 class="text-xl font-semibold text-ink">Tentang Program Ini</h2>
                    <div class="mt-4 space-y-3 leading-relaxed text-ink-soft">
                        @foreach (preg_split('/\n\s*\n/', trim($event->description ?? '')) as $paragraph)
                            <p class="text-pretty">{{ $paragraph }}</p>
                        @endforeach
                    </div>
                </div>

                {{-- Tentatif --}}
                @if (! empty($event->tentative))
                    <div>
                        <h2 class="text-xl font-semibold text-ink">Tentatif Program</h2>
                        <ol class="mt-4 space-y-0">
                            @foreach ($event->tentative as $i => $row)
                                <li class="flex gap-4 border-l-2 border-brand-200 pb-5 pl-5 last:pb-0
                                           {{ $loop->last ? 'border-transparent' : '' }} relative">
                                    <span class="absolute -left-[7px] top-1 h-3 w-3 rounded-full bg-brand-400 ring-4 ring-cream"></span>
                                    <span class="w-20 shrink-0 text-sm font-semibold text-brand-700">{{ $row['masa'] ?? '' }}</span>
                                    <span class="text-sm text-ink-soft text-pretty">{{ $row['aktiviti'] ?? '' }}</span>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                @endif

                {{-- Galeri (program selesai) --}}
                @if ($event->gallery->isNotEmpty())
                    <div>
                        <h2 class="text-xl font-semibold text-ink">Galeri Program</h2>
                        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                            @foreach ($event->gallery as $photo)
                                <figure class="overflow-hidden rounded-xl bg-mist">
                                    <img src="{{ Storage::url($photo->image_path) }}"
                                         alt="{{ $photo->caption ?? $event->title }}" loading="lazy"
                                         class="aspect-square w-full object-cover transition hover:scale-105" />
                                </figure>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Testimoni --}}
                @if ($event->testimonials->isNotEmpty())
                    <div>
                        <h2 class="text-xl font-semibold text-ink">Apa Kata Peserta</h2>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            @foreach ($event->testimonials as $testimonial)
                                <figure class="rounded-card border border-hairline bg-surface p-5">
                                    <blockquote class="text-sm leading-relaxed text-ink-soft text-pretty">
                                        &ldquo;{{ $testimonial->quote }}&rdquo;
                                    </blockquote>
                                    <figcaption class="mt-3 text-sm font-medium text-ink">
                                        {{ $testimonial->name }}
                                        <span class="block text-xs font-normal text-ink-muted">{{ $testimonial->role_label }}</span>
                                    </figcaption>
                                </figure>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Soalan lazim --}}
                @if (! empty($event->faqs))
                    <div>
                        <h2 class="text-xl font-semibold text-ink">Soalan Lazim</h2>
                        <div class="mt-4 divide-y divide-hairline overflow-hidden rounded-card border border-hairline bg-surface">
                            @foreach ($event->faqs as $faq)
                                <details class="group">
                                    <summary class="tap-target flex cursor-pointer items-center justify-between gap-4 px-5 text-sm font-medium text-ink">
                                        {{ $faq['soalan'] ?? '' }}
                                        <x-ui.icon name="chevron-down" class="h-4 w-4 shrink-0 text-ink-muted transition group-open:rotate-180" />
                                    </summary>
                                    <p class="px-5 pb-4 text-sm leading-relaxed text-ink-soft text-pretty">
                                        {{ $faq['jawapan'] ?? '' }}
                                    </p>
                                </details>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Sisi --}}
            <aside class="space-y-5">
                {{-- Lokasi & peta --}}
                <x-ui.card>
                    <h3 class="font-semibold text-ink">Lokasi</h3>
                    <p class="mt-2 text-sm text-ink-soft text-pretty">
                        {{ $event->venue?->address ?? $event->locationLabel() }}
                    </p>

                    @if ($event->venue?->google_maps_url)
                        <x-ui.button :href="$event->venue->google_maps_url" target="_blank" rel="noopener"
                                     variant="outline" size="sm" block class="mt-4" icon="pin">
                            Buka di Google Maps
                        </x-ui.button>
                    @endif

                    @if ($event->parking_info)
                        <div class="mt-4 rounded-xl bg-mist p-3.5">
                            <p class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Parkir & Arahan</p>
                            <p class="mt-1.5 text-sm text-ink-soft text-pretty">{{ $event->parking_info }}</p>
                        </div>
                    @endif
                </x-ui.card>

                {{-- Penganjur --}}
                <x-ui.card>
                    <h3 class="font-semibold text-ink">Penganjur & Rakan</h3>
                    <dl class="mt-3 space-y-3 text-sm">
                        @if ($event->organizer_name)
                            <div>
                                <dt class="text-xs text-ink-muted">Rakan Lokasi</dt>
                                <dd class="mt-0.5 text-ink text-pretty">{{ $event->organizer_name }}</dd>
                            </div>
                        @endif

                        @if ($event->mobilizers->isNotEmpty())
                            <div>
                                <dt class="text-xs text-ink-muted">Penggerak Jelajah</dt>
                                <dd class="mt-0.5 text-ink">
                                    {{ $event->mobilizers->pluck('name')->join(', ') }}
                                </dd>
                            </div>
                        @endif

                        <div>
                            <dt class="text-xs text-ink-muted">Dianjurkan oleh</dt>
                            <dd class="mt-0.5 text-ink">BeDaie &middot; {{ config('jelajah.org') }}</dd>
                        </div>
                    </dl>

                    @if ($event->contact_phone)
                        <x-ui.button :href="'https://wa.me/'.$event->contact_phone" target="_blank" rel="noopener"
                                     variant="whatsapp" size="sm" block class="mt-4" icon="whatsapp">
                            Hubungi Penganjur
                        </x-ui.button>
                    @endif
                </x-ui.card>

                {{-- Sijil --}}
                @if ($event->certificate_enabled)
                    <div class="rounded-card border border-brand-200 bg-brand-50 p-5">
                        <div class="flex items-start gap-3">
                            <x-ui.icon name="certificate" class="mt-0.5 h-5 w-5 shrink-0 text-brand-600" />
                            <div>
                                <h3 class="font-semibold text-ink">Sijil Digital Automatik</h3>
                                <p class="mt-1.5 text-sm text-ink-soft text-pretty">
                                    Imbas QR kehadiran di pintu masuk, dan sijil anda dijana automatik
                                    selepas program tamat.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </section>

    {{-- ══ Program berkaitan ═════════════════════════════════ --}}
    @if ($related->isNotEmpty())
        <section class="border-t border-hairline bg-surface py-12 sm:py-16">
            <div class="jelajah-container">
                <x-ui.section-heading eyebrow="Seterusnya" title="Program Lain Yang Mungkin Sesuai" />
                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $other)
                        <x-jelajah.event-card :event="$other" compact />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.public>
