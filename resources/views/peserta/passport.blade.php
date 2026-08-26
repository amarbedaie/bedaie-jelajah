<x-layouts.app title="Pasport Ilmu" nav="peserta">
    {{-- ── Kad pasport ────────────────────────────────────── --}}
    <section class="overflow-hidden rounded-[--radius-card-lg] bg-navy-900 shadow-lift">
        <div class="relative p-7 sm:p-9">
            <div class="motif-girih-dark absolute inset-0 opacity-60" aria-hidden="true"></div>
            <div class="absolute -right-16 -top-20 h-64 w-64 rounded-full bg-brand-500/25 blur-3xl" aria-hidden="true"></div>

            <div class="relative">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-brand-300">
                    Pasport Ilmu BeDaie
                </p>
                <h1 class="mt-3 font-display text-2xl text-white sm:text-3xl text-pretty">{{ $user->name }}</h1>
                <p class="mt-1.5 text-sm text-white/55">
                    Ahli sejak {{ $user->created_at->translatedFormat('F Y') }}
                </p>

                <dl class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach ([
                        ['Program disertai', number_format($joined), 'ticket'],
                        ['Program selesai', number_format($completed), 'check-circle'],
                        ['Jam pembelajaran', rtrim(rtrim(number_format($learningHours, 1), '0'), '.'), 'clock'],
                        ['Sijil diperoleh', number_format($certificates->count()), 'certificate'],
                    ] as [$label, $value, $icon])
                        <div class="rounded-xl bg-white/8 p-4 ring-1 ring-white/10">
                            <x-ui.icon :name="$icon" class="h-4 w-4 text-brand-300" />
                            <dd class="mt-2 font-display text-2xl text-white">{{ $value }}</dd>
                            <dt class="mt-0.5 text-xs text-white/50 text-pretty">{{ $label }}</dt>
                        </div>
                    @endforeach
                </dl>
            </div>
        </div>
    </section>

    {{-- ── Program akan datang ────────────────────────────── --}}
    <section class="mt-8">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <h2 class="text-lg font-semibold text-navy-900">Program Akan Datang</h2>
            <a href="{{ route('peserta.program') }}" class="text-sm font-medium text-brand-600 hover:underline">
                Semua program saya
            </a>
        </div>

        @if ($upcoming->isEmpty())
            <x-ui.empty-state class="mt-4" compact icon="calendar"
                title="Tiada program akan datang"
                description="Jelajah program yang dibuka dan daftar untuk menambah rekod pembelajaran anda.">
                <x-ui.button :href="route('program.index')" variant="primary" size="sm" class="mt-4">
                    Lihat Program
                </x-ui.button>
            </x-ui.empty-state>
        @else
            <ul class="mt-4 space-y-3">
                @foreach ($upcoming as $registration)
                    @php $event = $registration->event; @endphp
                    <li class="rounded-[--radius-card] border border-hairline bg-surface p-5">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-medium text-navy-900 text-pretty">{{ $event->title }}</p>
                                <p class="mt-0.5 text-sm text-ink-muted text-pretty">
                                    {{ $event->dateLabel() }} &middot; {{ $event->timeLabel() }}<br>
                                    {{ $event->venue?->name ?? $event->locationLabel() }}
                                </p>
                            </div>
                            <x-ui.badge :color="$registration->status->color()" dot>
                                {{ $registration->status->label() }}
                            </x-ui.badge>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2.5">
                            <x-ui.button :href="route('tiket.show', $registration->public_token)"
                                         variant="navy" size="sm" icon="qr">
                                Tiket & QR
                            </x-ui.button>
                            <x-ui.button :href="route('tiket.kalendar', $registration->public_token)"
                                         variant="outline" size="sm" icon="calendar">
                                Tambah ke Kalendar
                            </x-ui.button>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    {{-- ── Sijil ──────────────────────────────────────────── --}}
    <section class="mt-8">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <h2 class="text-lg font-semibold text-navy-900">Sijil Saya</h2>
            <a href="{{ route('peserta.sijil') }}" class="text-sm font-medium text-brand-600 hover:underline">
                Semua sijil
            </a>
        </div>

        @if ($certificates->isEmpty())
            <x-ui.empty-state class="mt-4" compact icon="certificate"
                title="Belum ada sijil"
                description="Sijil dijana automatik selepas kehadiran anda direkodkan melalui QR." />
        @else
            <ul class="mt-4 grid gap-4 sm:grid-cols-2">
                @foreach ($certificates->take(4) as $certificate)
                    <li class="rounded-[--radius-card] border border-hairline bg-surface p-5">
                        <div class="flex items-start gap-3">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-50">
                                <x-ui.icon name="certificate" class="h-5 w-5 text-brand-600" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="font-medium text-navy-900 text-pretty">{{ $certificate->event_title }}</p>
                                <p class="mt-0.5 font-mono text-xs text-ink-muted">
                                    {{ $certificate->certificate_number }}
                                </p>
                                <x-ui.button :href="$certificate->downloadUrl()" target="_blank"
                                             variant="outline" size="sm" class="mt-3" icon="download">
                                    Muat Turun
                                </x-ui.button>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    {{-- ── Sejarah ────────────────────────────────────────── --}}
    @if ($history->isNotEmpty())
        <section class="mt-8">
            <h2 class="text-lg font-semibold text-navy-900">Sejarah Pembelajaran</h2>
            <ul class="mt-4 divide-y divide-hairline overflow-hidden rounded-[--radius-card] border border-hairline bg-surface">
                @foreach ($history as $registration)
                    @php $event = $registration->event; @endphp
                    <li class="flex flex-wrap items-center justify-between gap-3 p-5">
                        <div class="min-w-0">
                            <p class="font-medium text-navy-900 text-pretty">{{ $event->title }}</p>
                            <p class="mt-0.5 text-sm text-ink-muted">
                                {{ $event->dateLabel() }} &middot; {{ $event->state?->name }}
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            @if ($registration->hasAttended())
                                <x-ui.badge color="success" icon="check-circle">Hadir</x-ui.badge>
                            @else
                                <x-ui.badge color="grey">Tidak hadir</x-ui.badge>
                            @endif
                            @if ($registration->certificate)
                                <x-ui.button :href="$registration->certificate->downloadUrl()" target="_blank"
                                             variant="ghost" size="sm" icon="certificate">
                                    Sijil
                                </x-ui.button>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- ── Cadangan ───────────────────────────────────────── --}}
    @if ($suggested->isNotEmpty())
        <section class="mt-8">
            <h2 class="text-lg font-semibold text-navy-900">Cadangan Untuk Anda</h2>
            <p class="mt-1 text-sm text-ink-soft">Berdasarkan program yang pernah anda sertai.</p>
            <div class="mt-4 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($suggested as $event)
                    <x-jelajah.event-card :event="$event" compact />
                @endforeach
            </div>
        </section>
    @endif
</x-layouts.app>
