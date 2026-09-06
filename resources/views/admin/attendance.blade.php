<x-layouts.admin :title="'Kehadiran — '.$event->title" heading="Papan Kehadiran">
    <a href="{{ route('admin.kehadiran') }}"
       class="inline-flex items-center gap-1.5 text-sm font-medium text-ink-soft hover:text-ink">
        <x-ui.icon name="arrow-left" class="h-4 w-4" /> Semua kehadiran
    </a>

    <div class="mt-4">
        <h2 class="font-display text-xl text-ink text-pretty">{{ $event->title }}</h2>
        <p class="mt-1 text-sm text-ink-muted text-pretty">
            {{ $event->dateLabel() }} &middot; {{ $event->timeLabel() }} &middot;
            {{ $event->venue?->name ?? $event->locationLabel() }}
        </p>
    </div>

    <dl class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        @foreach ([
            ['Berdaftar', $stats['berdaftar'], 'users'],
            ['Hadir', $stats['hadir'], 'check-circle'],
            ['Belum hadir', $stats['belum_hadir'], 'clock'],
            ['Walk-in', $stats['walk_in'], 'plus'],
            ['Senarai menunggu', $stats['senarai_menunggu'], 'list'],
        ] as [$label, $value, $icon])
            <div class="rounded-card border border-hairline bg-surface p-5">
                <span class="grid h-9 w-9 place-items-center rounded-xl bg-brand-50">
                    <x-ui.icon :name="$icon" class="h-4 w-4 text-brand-600" />
                </span>
                <dd class="mt-3 font-display text-3xl text-ink">{{ number_format($value) }}</dd>
                <dt class="mt-0.5 text-sm text-ink-soft text-pretty">{{ $label }}</dt>
            </div>
        @endforeach
    </dl>

    <div class="mt-6 flex flex-wrap gap-2.5">
        <x-ui.button :href="route('checkin.scanner', $event)" variant="primary" icon="qr">
            Buka Pengimbas Check-in
        </x-ui.button>
        <x-ui.button :href="route('admin.program.show', $event)" variant="outline">Butiran Program</x-ui.button>
        <x-ui.button :href="route('admin.laporan.program', $event)" variant="outline" icon="chart">
            Laporan Impak
        </x-ui.button>
        @can('export-participants')
            <x-ui.button :href="route('admin.laporan.eksport', $event)" variant="outline" icon="download">
                Eksport CSV
            </x-ui.button>
        @endcan
    </div>

    <div class="mt-7">
        <x-ui.progress :value="$stats['berdaftar'] > 0 ? round($stats['hadir'] / $stats['berdaftar'] * 100) : 0"
                       label="Kadar kehadiran" tone="success" />
    </div>

    {{-- ── Senarai nama ──────────────────────────────────────────
         Pada malam program, soalan sebenar ialah "siapa belum sampai".
         Statistik sahaja memaksa staf keluar ke fail CSV. --}}
    <section class="mt-10" aria-labelledby="tajuk-senarai">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 id="tajuk-senarai" class="font-display text-2xl text-ink">Senarai Peserta</h2>
                <p class="mt-1.5 text-sm text-ink-soft">
                    {{ $registrations->count() }} dipaparkan
                    @if ($filter === 'belum') &middot; belum hadir sahaja
                    @elseif ($filter === 'sudah') &middot; sudah hadir sahaja
                    @endif
                </p>
            </div>

            <form method="GET" class="flex flex-wrap items-end gap-2.5">
                <div class="w-full sm:w-64">
                    <label for="cari" class="sr-only">Cari nama, nombor atau rujukan</label>
                    <x-ui.input id="cari" name="q" :value="request('q')" icon="search"
                                placeholder="Cari nama atau nombor…" />
                </div>
                <input type="hidden" name="hadir" value="{{ $filter }}">
                <x-ui.button type="submit" variant="outline" icon="search">Cari</x-ui.button>
            </form>
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
            @foreach ([['', 'Semua'], ['belum', 'Belum hadir'], ['sudah', 'Sudah hadir']] as [$value, $label])
                <a href="{{ route('admin.kehadiran.show', ['event' => $event, 'hadir' => $value, 'q' => request('q')]) }}"
                   class="tap-target inline-flex items-center rounded-lg px-3.5 text-sm font-medium transition-colors
                          {{ $filter === $value
                             ? 'bg-brand-600 text-white'
                             : 'border border-hairline bg-surface text-ink-soft hover:border-brand-300 hover:text-ink' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @if ($registrations->isEmpty())
            <x-ui.empty-state icon="users" title="Tiada peserta sepadan"
                              description="Cuba tukar carian atau tapisan di atas." class="mt-6" />
        @else
            <ul class="mt-5 divide-y divide-hairline border-t border-hairline">
                @foreach ($registrations as $r)
                    <li class="flex flex-wrap items-center justify-between gap-3 py-4">
                        <div class="min-w-0">
                            <a href="{{ route('admin.peserta.show', $r->public_id) }}"
                               class="font-medium text-ink hover:text-brand-700">{{ $r->name }}</a>
                            <p class="mt-0.5 flex flex-wrap items-center gap-x-3 text-sm text-ink-muted">
                                <span class="font-mono text-[0.8125rem]">{{ $r->maskedPhone() }}</span>
                                @if ($r->seats() > 1)
                                    <span>{{ $r->seats() }} tempat</span>
                                @endif
                            </p>
                        </div>

                        <div class="flex items-center gap-2.5">
                            @if ($r->hasAttended())
                                <span class="text-sm text-ink-muted tabular-nums">
                                    {{ $r->attendance?->checked_in_at?->format('g:ia') }}
                                </span>
                                <x-ui.badge color="solid" icon="check-circle">Hadir</x-ui.badge>
                            @else
                                <x-ui.badge color="quiet">Belum hadir</x-ui.badge>
                                <x-ui.button :href="'https://wa.me/'.$r->phone" target="_blank" rel="noopener"
                                             variant="outline" size="sm" icon="whatsapp">
                                    <span class="sr-only">Hubungi {{ $r->name }} di WhatsApp</span>
                                </x-ui.button>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</x-layouts.admin>
