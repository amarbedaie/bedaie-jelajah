{{--
    Poster rasmi dijana daripada template BeDaie.
    Penggerak tidak boleh mengubah reka bentuk — hanya cetak atau simpan.
--}}
<!DOCTYPE html>
<html lang="ms">
<head>
    @include('partials.head', ['title' => 'Poster — '.$event->title])
    <style>
        @media print {
            .no-print { display: none !important; }
            @page { size: A4 portrait; margin: 0; }
            body { background: #fff !important; }
            .poster { box-shadow: none !important; margin: 0 !important; }
        }
    </style>
</head>
<body class="bg-mist antialiased">
    {{-- Bar tindakan (tidak dicetak) --}}
    <div class="no-print sticky top-0 z-10 border-b border-hairline bg-surface">
        <div class="jelajah-container flex h-16 items-center justify-between gap-3">
            <a href="{{ route('penggerak.program.show', $event) }}"
               class="inline-flex items-center gap-1.5 text-sm font-medium text-ink-soft hover:text-ink">
                <x-ui.icon name="arrow-left" class="h-4 w-4" /> Kembali
            </a>
            <div class="flex items-center gap-2">
                <x-ui.button onclick="window.print()" variant="primary" size="sm" icon="download">
                    Cetak / Simpan PDF
                </x-ui.button>
            </div>
        </div>
    </div>

    <div class="py-8">
        {{-- A4 nisbah 1:1.414 --}}
        <div class="poster mx-auto flex aspect-[1/1.414] w-full max-w-[46rem] flex-col overflow-hidden
                    border border-hairline bg-cream">
            <div class="relative flex flex-1 flex-col">
                <div class="motif-girih absolute inset-0 opacity-70" aria-hidden="true"></div>
                <div class="absolute -right-20 -top-24 h-80 w-80 rounded-full bg-brand-400/30 blur-3xl" aria-hidden="true"></div>
                <div class="absolute -bottom-28 -left-16 h-72 w-72 rounded-full bg-brand-700/25 blur-3xl" aria-hidden="true"></div>

                <div class="relative flex flex-1 flex-col p-10">
                    {{-- Kepala --}}
                    <div class="flex items-start justify-between gap-4">
                        <x-brand.logo :light="true" />
                        <span class="rounded-full bg-surface px-3.5 py-1.5 text-[0.65rem] font-semibold
                                     uppercase tracking-[0.18em] text-brand-700 ring-1 ring-hairline">
                            {{ $event->category?->name ?? 'Jelajah' }}
                        </span>
                    </div>

                    {{-- Badan --}}
                    <div class="mt-12 flex-1">
                        <p class="text-sm font-semibold uppercase tracking-[0.22em] text-brand-700">
                            {{ config('jelajah.slogan') }}
                        </p>

                        <h1 class="mt-5 font-display text-[2.6rem] leading-[1.08] text-ink text-pretty">
                            {{ $event->title }}
                        </h1>

                        @if ($event->theme)
                            <p class="mt-4 text-xl text-brand-700 text-pretty">{{ $event->theme }}</p>
                        @endif

                        @if ($event->speaker)
                            <div class="mt-8 inline-flex items-center gap-3 rounded-2xl bg-surface px-5 py-3
                                        ring-1 ring-hairline">
                                <x-ui.icon name="user" class="h-5 w-5 text-brand-700" />
                                <div>
                                    <p class="font-medium text-ink">{{ $event->speaker->name }}</p>
                                    @if ($event->speaker->title)
                                        <p class="text-xs text-ink/55">{{ $event->speaker->title }}</p>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <dl class="mt-10 space-y-4">
                            <div class="flex items-start gap-4">
                                <dt class="sr-only">Tarikh</dt>
                                <x-ui.icon name="calendar" class="mt-1 h-6 w-6 shrink-0 text-brand-700" />
                                <dd>
                                    <p class="text-xl font-semibold text-ink">{{ $event->dateLabel() }}</p>
                                    <p class="text-ink/65">{{ $event->timeLabel() }}</p>
                                </dd>
                            </div>

                            <div class="flex items-start gap-4">
                                <dt class="sr-only">Lokasi</dt>
                                <x-ui.icon name="pin" class="mt-1 h-6 w-6 shrink-0 text-brand-700" />
                                <dd>
                                    <p class="text-xl font-semibold text-ink text-pretty">
                                        {{ $event->venue?->name ?? $event->locationLabel() }}
                                    </p>
                                    <p class="text-ink/65 text-pretty">
                                        {{ $event->district?->name ? $event->district->name.', ' : '' }}{{ $event->state?->name }}
                                    </p>
                                </dd>
                            </div>

                            <div class="flex items-start gap-4">
                                <dt class="sr-only">Penyertaan</dt>
                                <x-ui.icon name="ticket" class="mt-1 h-6 w-6 shrink-0 text-brand-700" />
                                <dd>
                                    <p class="text-xl font-semibold text-ink">{{ $event->priceLabel() }}</p>
                                    <p class="text-ink/65">
                                        Terbuka kepada {{ mb_strtolower($event->target_audience?->label() ?? 'umum') }}
                                        &middot; Tempat terhad
                                    </p>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Kaki: QR + link --}}
                    <div class="mt-8 flex items-end justify-between gap-6 border-t border-hairline pt-7">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-700">
                                Daftar Sekarang
                            </p>
                            <p class="mt-2 break-all font-mono text-lg text-ink">{{ $event->shortUrl() }}</p>
                            <p class="mt-3 text-xs text-ink-muted">
                                Anjuran BeDaie &middot; {{ config('jelajah.org') }} &middot; {{ config('jelajah.motto') }}
                            </p>
                        </div>

                        <div class="shrink-0 rounded-2xl bg-white p-3">
                            {!! $qrSvg !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <p class="no-print mx-auto mt-6 max-w-[46rem] px-4 text-center text-sm text-ink-muted text-pretty">
            Gunakan "Cetak / Simpan PDF" untuk memuat turun poster ini. Untuk poster reka bentuk khas,
            hubungi pasukan BeDaie.
        </p>
    </div>
</body>
</html>
