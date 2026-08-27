<x-layouts.admin :title="'Laporan — '.$event->title" heading="Laporan Impak Program">
    <a href="{{ route('admin.laporan') }}"
       class="inline-flex items-center gap-1.5 text-sm font-medium text-ink-soft hover:text-ink">
        <x-ui.icon name="arrow-left" class="h-4 w-4" /> Semua laporan
    </a>

    <div class="mt-4">
        <h2 class="font-display text-xl text-ink text-pretty">{{ $event->title }}</h2>
        <p class="mt-1 text-sm text-ink-muted text-pretty">
            {{ $event->dateLabel() }} &middot; {{ $event->venue?->name ?? $event->locationLabel() }}
            @if ($event->speaker) &middot; {{ $event->speaker->name }} @endif
        </p>
    </div>

    <dl class="mt-6 grid gap-4 sm:grid-cols-3 lg:grid-cols-4">
        @foreach ([
            ['Berdaftar', number_format($report['registered'])],
            ['Hadir', number_format($report['attended'])],
            ['Kadar kehadiran', number_format($report['attendance_rate'], 1).'%'],
            ['Walk-in', number_format($report['walk_in'])],
            ['Senarai menunggu', number_format($report['waitlist'])],
            ['Ahli keluarga', number_format($report['guests'])],
            ['Sijil dikeluarkan', number_format($report['certificates'])],
            ['Gambar diluluskan', number_format($report['gallery_count'])],
        ] as [$label, $value])
            <div class="rounded-card border border-hairline bg-surface p-5">
                <dd class="font-display text-2xl text-ink">{{ $value }}</dd>
                <dt class="mt-1 text-sm text-ink-soft text-pretty">{{ $label }}</dt>
            </div>
        @endforeach
    </dl>

    <div class="mt-7 grid gap-6 lg:grid-cols-2">
        <x-ui.card>
            <h3 class="font-semibold text-ink">Kewangan</h3>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between gap-3">
                    <dt class="text-ink-muted">Kutipan berjaya</dt>
                    <dd class="font-medium text-ink">RM {{ number_format($report['revenue'], 2) }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-ink-muted">Menunggu pengesahan</dt>
                    <dd class="text-ink">{{ number_format($report['pending_payments']) }}</dd>
                </div>
            </dl>
        </x-ui.card>

        <x-ui.card>
            <h3 class="font-semibold text-ink">Demografi</h3>
            <dl class="mt-4 space-y-2.5 text-sm">
                @forelse ($report['gender'] as $gender => $count)
                    <div class="flex justify-between gap-3">
                        <dt class="text-ink-muted">{{ $gender ? ucfirst($gender) : 'Tidak dinyatakan' }}</dt>
                        <dd class="text-ink">{{ number_format($count) }}</dd>
                    </div>
                @empty
                    <p class="text-sm text-ink-muted">Tiada data.</p>
                @endforelse
            </dl>
        </x-ui.card>

        <x-ui.card>
            <h3 class="font-semibold text-ink">Peserta Mengikut Negeri</h3>
            @if ($report['by_state']->isEmpty())
                <p class="mt-3 text-sm text-ink-muted">Tiada data.</p>
            @else
                <ul class="mt-4 space-y-2.5">
                    @foreach ($report['by_state']->take(10) as $stateName => $count)
                        <li class="flex items-center justify-between gap-3 text-sm">
                            <span class="text-ink-soft">{{ $stateName }}</span>
                            <span class="font-medium text-ink">{{ number_format($count) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>

        <x-ui.card>
            <h3 class="font-semibold text-ink">Maklum Balas</h3>
            @if ($report['rating_count'] > 0)
                <p class="mt-3 font-display text-3xl text-ink">
                    {{ number_format((float) $report['rating'], 1) }}
                    <span class="text-base font-normal text-ink-muted">/ 5</span>
                </p>
                <p class="mt-0.5 text-xs text-ink-muted">
                    daripada {{ $report['rating_count'] }} maklum balas &middot;
                    {{ $report['wants_advanced'] }} mahu kelas lanjutan
                </p>

                <ul class="mt-4 space-y-1.5">
                    @foreach ($report['rating_breakdown'] as $star => $count)
                        <li class="flex items-center gap-2.5 text-xs">
                            <span class="w-8 shrink-0 text-ink-muted">{{ $star }} ★</span>
                            <span class="h-1.5 flex-1 overflow-hidden rounded-full bg-mist">
                                <span class="block h-full rounded-full bg-clay-400"
                                      style="width: {{ $report['rating_count'] ? round($count / $report['rating_count'] * 100) : 0 }}%"></span>
                            </span>
                            <span class="w-6 shrink-0 text-right text-ink-muted">{{ $count }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="mt-3 text-sm text-ink-muted">Belum ada maklum balas.</p>
            @endif
        </x-ui.card>
    </div>

    @if ($report['next_topics']->isNotEmpty())
        <x-ui.card class="mt-6">
            <h3 class="font-semibold text-ink">Topik Yang Diminta Peserta</h3>
            <ul class="mt-4 flex flex-wrap gap-2">
                @foreach ($report['next_topics'] as $topic)
                    <li><x-ui.badge color="purple">{{ $topic }}</x-ui.badge></li>
                @endforeach
            </ul>
        </x-ui.card>
    @endif

    <div class="mt-7 flex flex-wrap gap-2.5">
        <x-ui.button :href="route('admin.program.show', $event)" variant="outline">Butiran Program</x-ui.button>
        <x-ui.button :href="route('admin.sijil', ['program' => $event->short_code])" variant="outline" icon="certificate">
            Sijil Program
        </x-ui.button>
        @can('export-participants')
            <x-ui.button :href="route('admin.laporan.eksport', $event)" variant="outline" icon="download">
                Eksport Peserta (CSV)
            </x-ui.button>
        @endcan
    </div>
</x-layouts.admin>
