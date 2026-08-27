@php
    $greeting = now()->hour < 12 ? 'Selamat pagi' : (now()->hour < 19 ? 'Selamat petang' : 'Selamat malam');
@endphp

<x-layouts.app title="Ringkasan" nav="penggerak">
    {{-- ── Salam ──────────────────────────────────────────── --}}
    <div class="mb-7">
        <p class="text-sm text-ink-muted">{{ $greeting }},</p>
        <h1 class="mt-0.5 font-display text-2xl text-ink sm:text-3xl">{{ $user->name }}</h1>
        <p class="mt-1.5 text-ink-soft text-pretty">
            Terima kasih kerana menggerakkan ilmu di kawasan anda.
        </p>
    </div>

    @if ($active)
        {{-- ══ PROGRAM AKTIF ═══════════════════════════════ --}}
        <section class="overflow-hidden rounded-card-lg border border-hairline bg-surface shadow-soft">
            <div class="relative border-b border-hairline bg-cream p-6">
                <div class="motif-girih absolute inset-0 opacity-45" aria-hidden="true"></div>
                <div class="relative flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <x-ui.badge color="purple">{{ $active->status->label() }}</x-ui.badge>
                            <x-ui.badge color="grey">{{ $active->priceLabel() }}</x-ui.badge>
                        </div>
                        <h2 class="mt-3 font-display text-2xl leading-snug text-ink text-pretty">
                            {{ $active->title }}
                        </h2>
                        <p class="mt-2.5 text-sm text-ink-soft text-pretty">
                            {{ $active->dateLabel() }} &middot; {{ $active->timeLabel() }}<br>
                            {{ $active->venue?->name ?? $active->locationLabel() }}
                        </p>
                    </div>

                    @if ($active->countdownTarget())
                        <div x-data="countdown('{{ $active->countdownTarget()->toIso8601String() }}')"
                             class="shrink-0 rounded-lg border border-hairline bg-surface px-4 py-3 text-center" x-cloak>
                            <p class="font-display text-2xl text-ink" x-text="hari">—</p>
                            <p class="text-xs uppercase tracking-wider text-ink-muted">Hari lagi</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Kemajuan pendaftaran --}}
            <div class="border-b border-hairline p-6">
                <div class="flex flex-wrap items-baseline justify-between gap-2">
                    <h3 class="font-semibold text-ink">Kemajuan Pendaftaran</h3>
                    <p class="text-sm text-ink-muted">Sasaran {{ number_format($active->capacity) }} peserta</p>
                </div>

                <div class="mt-4">
                    <x-ui.progress :value="$active->fillPercent()"
                                   :tone="$active->fillPercent() >= 90 ? 'success' : 'brand'" />
                </div>

                <dl class="mt-5 grid grid-cols-3 gap-3">
                    <div class="rounded-xl bg-clay-50 p-3.5">
                        <dd class="font-display text-2xl text-clay-700">{{ number_format($active->seatsTaken()) }}</dd>
                        <dt class="mt-0.5 text-xs text-ink-soft">Telah mendaftar</dt>
                    </div>
                    <div class="rounded-xl bg-mist p-3.5">
                        <dd class="font-display text-2xl text-ink">
                            {{ $active->seatsLeft() !== null ? number_format($active->seatsLeft()) : '∞' }}
                        </dd>
                        <dt class="mt-0.5 text-xs text-ink-soft">Tempat tersedia</dt>
                    </div>
                    <div class="rounded-xl bg-mist p-3.5">
                        <dd class="font-display text-2xl text-ink">{{ number_format($active->attended_count) }}</dd>
                        <dt class="mt-0.5 text-xs text-ink-soft">Telah hadir</dt>
                    </div>
                </dl>

                <p class="mt-4 text-sm text-ink-soft text-pretty">
                    <strong class="text-ink">{{ number_format($active->seatsTaken()) }} telah mendaftar.</strong>
                    @if ($active->seatsLeft() !== null && $active->seatsLeft() > 0)
                        {{ number_format($active->seatsLeft()) }} tempat masih tersedia.
                    @elseif ($active->isFull())
                        Tempat telah penuh — pendaftar baharu masuk ke senarai menunggu.
                    @endif
                </p>
            </div>

            {{-- Kongsi --}}
            <div class="p-6">
                <h3 class="font-semibold text-ink">Sebarkan Program Ini</h3>
                <p class="mt-1.5 text-sm text-ink-soft text-pretty">
                    Cara paling berkesan: kongsi di WhatsApp kumpulan kariah dan keluarga.
                </p>

                <div class="mt-4 grid gap-2.5 sm:grid-cols-2">
                    <x-ui.button :href="$active->whatsappShareUrl()" target="_blank" rel="noopener"
                                 variant="whatsapp" block icon="whatsapp" class="sm:col-span-2">
                        Kongsi di WhatsApp
                    </x-ui.button>

                    <x-ui.copy-button :text="$active->shortUrl()" label="Salin Link" variant="outline" block />

                    <x-ui.button :href="route('penggerak.program.qr', $active)" variant="outline" block icon="download">
                        Muat Turun QR
                    </x-ui.button>

                    <x-ui.button :href="route('penggerak.program.poster', $active)" target="_blank"
                                 variant="outline" block icon="image">
                        Muat Turun Poster
                    </x-ui.button>

                    <x-ui.button :href="route('penggerak.peserta', ['program' => $active->short_code])"
                                 variant="outline" block icon="users">
                        Lihat Peserta
                    </x-ui.button>
                </div>

                {{-- QR + link --}}
                <div class="mt-6 flex flex-col items-center gap-5 rounded-xl bg-mist p-5 sm:flex-row">
                    @if ($qrSvg)
                        <div class="shrink-0 rounded-xl bg-white p-2.5 ring-1 ring-hairline">{!! $qrSvg !!}</div>
                    @endif
                    <div class="min-w-0 flex-1 text-center sm:text-left">
                        <p class="text-sm font-medium text-ink">Link pendaftaran anda</p>
                        <p class="mt-1 break-all font-mono text-sm text-clay-700">{{ $active->shortUrl() }}</p>
                        <p class="mt-2.5 text-xs text-ink-muted text-pretty">
                            Cetak QR ini pada poster atau paparkan di skrin masjid.
                        </p>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap gap-2.5 border-t border-hairline pt-5">
                    <x-ui.button :href="route('penggerak.program.show', $active)" variant="navy" size="sm">
                        Butiran Penuh Program
                    </x-ui.button>
                    <x-ui.button :href="'https://wa.me/'.config('jelajah.support.phone')" target="_blank"
                                 rel="noopener" variant="ghost" size="sm" icon="chat">
                        Hubungi BeDaie
                    </x-ui.button>
                </div>
            </div>
        </section>
    @else
        {{-- ══ TIADA PROGRAM AKTIF ═════════════════════════ --}}
        <x-ui.empty-state icon="calendar"
            title="Belum ada program aktif"
            description="Setelah permohonan anda disahkan, halaman program, link pendaftaran, QR dan poster akan dijana automatik di sini.">
            <div class="mt-5 flex flex-col justify-center gap-3 sm:flex-row">
                <x-ui.button :href="route('jemput')" variant="primary" icon="heart">
                    Hantar Permohonan Baharu
                </x-ui.button>
                <x-ui.button :href="route('penggerak.panduan')" variant="outline" icon="info">
                    Baca Panduan Dahulu
                </x-ui.button>
            </div>
        </x-ui.empty-state>
    @endif

    {{-- ══ PERMOHONAN SAYA ═════════════════════════════════ --}}
    <section class="mt-8">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <h2 class="text-lg font-semibold text-ink">Permohonan Saya</h2>
            <a href="{{ route('penggerak.permohonan') }}"
               class="text-sm font-medium text-clay-600 hover:underline">Lihat semua</a>
        </div>

        @if ($applications->isEmpty())
            <x-ui.empty-state class="mt-4" compact icon="clipboard"
                title="Belum ada permohonan"
                description="Mula dengan menghantar permohonan untuk membawa BeDaie ke kawasan anda.">
                <x-ui.button :href="route('jemput')" variant="primary" size="sm" class="mt-4">
                    Jemput BeDaie
                </x-ui.button>
            </x-ui.empty-state>
        @else
            <ul class="mt-4 space-y-3">
                @foreach ($applications->take(4) as $application)
                    <li>
                        <a href="{{ route('penggerak.permohonan.show', $application) }}"
                           class="block rounded-card border border-hairline bg-surface p-5
                                  transition hover:border-clay-200 hover:shadow-soft">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-medium text-ink text-pretty">{{ $application->venue_name }}</p>
                                    <p class="mt-0.5 text-sm text-ink-muted">
                                        {{ $application->reference_no }} &middot;
                                        {{ $application->district?->name ? $application->district->name.', ' : '' }}{{ $application->state?->name }}
                                    </p>
                                </div>
                                <x-ui.badge :color="$application->status->color()" dot>
                                    {{ $application->status->label() }}
                                </x-ui.badge>
                            </div>

                            <div class="mt-4">
                                <x-ui.progress :value="$application->status->progress()" :showValue="false"
                                               :tone="$application->status->isClosed() ? 'navy' : 'brand'" />
                                <p class="mt-2 text-sm text-ink-soft text-pretty">
                                    {{ $application->status->description() }}
                                </p>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    {{-- ══ PROGRAM LEPAS ═══════════════════════════════════ --}}
    @if ($pastEvents->isNotEmpty())
        <section class="mt-8">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <h2 class="text-lg font-semibold text-ink">Program Yang Telah Selesai</h2>
                <a href="{{ route('penggerak.sijil') }}"
                   class="text-sm font-medium text-clay-600 hover:underline">Sijil & laporan</a>
            </div>

            <ul class="mt-4 grid gap-4 sm:grid-cols-2">
                @foreach ($pastEvents as $event)
                    <li>
                        <a href="{{ route('penggerak.program.show', $event) }}"
                           class="flex h-full flex-col rounded-card border border-hairline bg-surface p-5
                                  transition hover:border-clay-200 hover:shadow-soft">
                            <p class="font-medium text-ink text-pretty">{{ $event->title }}</p>
                            <p class="mt-0.5 text-sm text-ink-muted">{{ $event->dateLabel() }}</p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <x-ui.badge color="purple" icon="users">
                                    {{ number_format($event->attended_count) }} hadir
                                </x-ui.badge>
                                <x-ui.badge color="grey">
                                    {{ number_format($event->attendanceRate(), 0) }}% kadar kehadiran
                                </x-ui.badge>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</x-layouts.app>
