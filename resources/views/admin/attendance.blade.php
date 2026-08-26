<x-layouts.admin :title="'Kehadiran — '.$event->title" heading="Papan Kehadiran">
    <a href="{{ route('admin.kehadiran') }}"
       class="inline-flex items-center gap-1.5 text-sm font-medium text-ink-soft hover:text-navy-900">
        <x-ui.icon name="arrow-left" class="h-4 w-4" /> Semua kehadiran
    </a>

    <div class="mt-4">
        <h2 class="font-display text-xl text-navy-900 text-pretty">{{ $event->title }}</h2>
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
            <div class="rounded-[--radius-card] border border-hairline bg-surface p-5">
                <span class="grid h-9 w-9 place-items-center rounded-xl bg-brand-50">
                    <x-ui.icon :name="$icon" class="h-4 w-4 text-brand-600" />
                </span>
                <dd class="mt-3 font-display text-3xl text-navy-900">{{ number_format($value) }}</dd>
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
</x-layouts.admin>
