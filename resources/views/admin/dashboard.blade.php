<x-layouts.admin title="Dashboard" heading="Dashboard">
    {{-- ── Tindakan yang perlu dibuat ─────────────────────── --}}
    @if (! empty($actions))
        <section class="mb-7">
            <h2 class="mb-3 text-sm font-semibold uppercase tracking-wider text-ink-muted">
                Tindakan Yang Perlu Dibuat
            </h2>
            <ul class="grid gap-3 sm:grid-cols-2">
                @foreach ($actions as $action)
                    @php
                        $tones = [
                            'warning' => 'border-clay-400/30 bg-mist text-[#7A4E06]',
                            'success' => 'border-clay-600/30 bg-clay-50 text-[#0A5537]',
                            'danger'  => 'border-alert/30 bg-alert-soft text-[#8C1A1E]',
                            'neutral' => 'border-hairline bg-surface text-ink',
                        ];
                    @endphp
                    <li>
                        <a href="{{ $action['url'] }}"
                           class="flex items-center justify-between gap-3 rounded-card border p-4
                                  transition hover:shadow-soft {{ $tones[$action['tone']] ?? $tones['neutral'] }}">
                            <span class="text-sm font-medium text-pretty">{{ $action['label'] }}</span>
                            <x-ui.icon name="arrow-right" class="h-4 w-4 shrink-0" />
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- ── Statistik ──────────────────────────────────────── --}}
    <section>
        <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['Permohonan baharu', number_format($newApplications), 'inbox', route('admin.permohonan', ['status' => 'diterima'])],
                ['Permohonan aktif', number_format($openApplications), 'clipboard', route('admin.permohonan')],
                ['Program akan datang', number_format($upcomingEvents), 'calendar', route('admin.program')],
                ['Menunggu pengesahan', number_format($awaitingConfirmation), 'clock', route('admin.permohonan', ['status' => 'diluluskan'])],
                ['Pendaftaran bulan ini', number_format($registrationsThisMonth), 'users', route('admin.peserta')],
                ['Jumlah hadir', number_format($attendedTotal), 'check-circle', route('admin.kehadiran')],
                ['Sijil dikeluarkan', number_format($certificatesIssued), 'certificate', route('admin.sijil')],
                ['Negeri dijelajahi', number_format($headline['negeri']), 'map', route('admin.negeri')],
            ] as [$label, $value, $icon, $url])
                {{-- Angka, bukan kad: garis rambut dan ruang membawa
                     pengasingan, dan keseluruhan blok ialah pautannya. --}}
                <a href="{{ $url }}"
                   class="group block border-t border-hairline pt-4 transition-colors hover:border-clay-400">
                    <dd class="font-display text-[2.5rem] leading-none text-ink tabular-nums">{{ $value }}</dd>
                    <dt class="mt-2.5 text-sm leading-snug text-ink-muted text-pretty
                               group-hover:text-clay-700">{{ $label }}</dt>
                </a>
            @endforeach
        </dl>
    </section>

    {{-- min-w-0: tanpa ini item grid tidak boleh mengecut bawah lebar
         min-content kandungannya, dan dashboard menatal mendatar pada telefon. --}}
    <div class="mt-7 grid gap-6 lg:grid-cols-2">
        {{-- ── Program hari ini ───────────────────────────── --}}
        <section class="min-w-0">
            <h2 class="mb-3 text-lg font-semibold text-ink">Berlangsung Hari Ini</h2>
            @if ($todayEvents->isEmpty())
                <x-ui.empty-state compact icon="calendar" title="Tiada program hari ini"
                    description="Program yang dijadualkan hari ini akan muncul di sini." />
            @else
                <ul class="space-y-3">
                    @foreach ($todayEvents as $event)
                        <li class="rounded-card border border-hairline bg-surface p-5">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-medium text-ink text-pretty">{{ $event->title }}</p>
                                    <p class="mt-0.5 text-sm text-ink-muted text-pretty">
                                        {{ $event->timeLabel() }} &middot; {{ $event->venue?->name ?? $event->locationLabel() }}
                                    </p>
                                </div>
                                <x-ui.badge color="success" dot>Hari ini</x-ui.badge>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <x-ui.button :href="route('checkin.scanner', $event)" variant="primary" size="sm" icon="qr">
                                    Buka Scanner
                                </x-ui.button>
                                <x-ui.button :href="route('admin.kehadiran.show', $event)" variant="outline" size="sm">
                                    Papan Kehadiran
                                </x-ui.button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        {{-- ── Permohonan terkini ─────────────────────────── --}}
        <section class="min-w-0">
            <div class="mb-3 flex items-end justify-between gap-3">
                <h2 class="text-lg font-semibold text-ink">Permohonan Terkini</h2>
                <a href="{{ route('admin.permohonan') }}" class="text-sm font-medium text-clay-600 hover:underline">
                    Semua
                </a>
            </div>

            @if ($recentApplications->isEmpty())
                <x-ui.empty-state compact icon="inbox" title="Tiada permohonan" />
            @else
                <ul class="divide-y divide-hairline overflow-hidden rounded-card border border-hairline bg-surface">
                    @foreach ($recentApplications as $application)
                        <li>
                            <a href="{{ route('admin.permohonan.show', $application) }}"
                               class="flex items-start justify-between gap-3 p-4 hover:bg-mist/60">
                                <div class="min-w-0">
                                    <p class="truncate font-medium text-ink">{{ $application->venue_name }}</p>
                                    <p class="mt-0.5 truncate text-[0.8125rem] text-ink-muted">
                                        {{ $application->reference_no }} &middot; {{ $application->state?->name }}
                                        &middot; {{ $application->submitted_at?->diffForHumans() }}
                                    </p>
                                </div>
                                <x-ui.badge :color="$application->status->color()">
                                    {{ $application->status->label() }}
                                </x-ui.badge>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>

    {{-- ── Permintaan kawasan ─────────────────────────────── --}}
    <section class="mt-7">
        <div class="mb-3 flex items-end justify-between gap-3">
            <h2 class="text-lg font-semibold text-ink">Kawasan Permintaan Tertinggi</h2>
            <a href="{{ route('admin.permintaan') }}" class="text-sm font-medium text-clay-600 hover:underline">
                Semua permintaan
            </a>
        </div>

        @if ($demand->isEmpty())
            <x-ui.empty-state compact icon="heart" title="Belum ada permintaan kawasan" />
        @else
            <ul class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($demand as $area)
                    <li class="flex items-center justify-between gap-3 rounded-card border
                               border-hairline bg-surface p-4">
                        <div class="min-w-0">
                            <p class="truncate font-medium text-ink">
                                {{ $area->district?->name ?? $area->state?->name }}
                            </p>
                            <p class="truncate text-[0.8125rem] text-ink-muted">{{ $area->state?->name }}</p>
                        </div>
                        <x-ui.badge color="purple">{{ $area->individuals }} orang</x-ui.badge>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</x-layouts.admin>
