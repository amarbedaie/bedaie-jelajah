<x-layouts.admin title="Laporan" heading="Laporan Impak">
    <dl class="grid gap-4 sm:grid-cols-3 lg:grid-cols-5">
        @foreach ([
            ['negeri', 'Negeri Dijelajahi'], ['daerah', 'Daerah Dikunjungi'],
            ['program', 'Program Dilaksanakan'], ['peserta', 'Peserta Disantuni'],
            ['rakan', 'Rakan Masjid & Organisasi'],
        ] as [$key, $label])
            <div class="rounded-card border border-hairline bg-surface p-5">
                <dd class="font-display text-3xl text-ink">{{ number_format($headline[$key]) }}</dd>
                <dt class="mt-1 text-sm text-ink-soft text-pretty">{{ $label }}</dt>
            </div>
        @endforeach
    </dl>

    <div class="mt-8 grid gap-6 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)]">
        <section class="min-w-0">
            <h2 class="mb-3 text-lg font-semibold text-ink">Laporan Mengikut Program</h2>
            @if ($events->isEmpty())
                <x-ui.empty-state compact icon="chart" title="Belum ada program selesai" />
            @else
                <x-jelajah.admin-table :headers="['Program', 'Tarikh', 'Berdaftar', 'Hadir', 'Kadar', 'Penilaian', '']">
                    @foreach ($events as $event)
                        <tr class="hover:bg-mist/40">
                            <td class="px-4 py-3">
                                <p class="font-medium text-ink">{{ $event->title }}</p>
                                <p class="text-xs text-ink-muted">
                                    {{ $event->venue?->name ?? $event->state?->name }}
                                </p>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-ink-soft">
                                {{ $event->starts_at->translatedFormat('j M Y') }}
                            </td>
                            <td class="px-4 py-3 text-ink">{{ number_format($event->registered_count) }}</td>
                            <td class="px-4 py-3 text-ink">{{ number_format($event->attended_count) }}</td>
                            <td class="px-4 py-3">
                                <x-ui.badge color="grey">{{ number_format($event->attendanceRate(), 0) }}%</x-ui.badge>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-ink-soft">
                                {{ $event->averageRating() ? number_format($event->averageRating(), 1).' / 5' : '—' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <x-ui.button :href="route('admin.laporan.program', $event)" variant="ghost" size="sm">
                                    Laporan
                                </x-ui.button>
                            </td>
                        </tr>
                    @endforeach
                </x-jelajah.admin-table>

                <div class="mt-6">{{ $events->links() }}</div>
            @endif
        </section>

        <aside class="min-w-0 space-y-5">
            <x-ui.card>
                <h2 class="font-semibold text-ink">Topik Paling Diminta</h2>
                @if ($topics->isEmpty())
                    <p class="mt-3 text-sm text-ink-muted">Belum ada data.</p>
                @else
                    <ul class="mt-4 space-y-3">
                        @foreach ($topics as $topic)
                            <li class="flex items-center justify-between gap-3 text-sm">
                                <span class="text-ink-soft text-pretty">{{ $topic->category?->name ?? '—' }}</span>
                                <x-ui.badge color="purple">{{ number_format($topic->total) }}</x-ui.badge>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>

            <x-ui.card>
                <h2 class="font-semibold text-ink">Kawasan Permintaan Tertinggi</h2>
                @if ($demand->isEmpty())
                    <p class="mt-3 text-sm text-ink-muted">Belum ada permintaan.</p>
                @else
                    <ul class="mt-4 space-y-3">
                        @foreach ($demand as $area)
                            <li class="flex items-center justify-between gap-3 text-sm">
                                <span class="min-w-0 text-ink-soft">
                                    {{ $area->district?->name ?? $area->state?->name }}
                                    <span class="block text-xs text-ink-muted">{{ $area->state?->name }}</span>
                                </span>
                                <x-ui.badge color="warning">{{ number_format($area->individuals) }}</x-ui.badge>
                            </li>
                        @endforeach
                    </ul>
                @endif
                <x-ui.button :href="route('admin.permintaan')" variant="outline" size="sm" block class="mt-4">
                    Semua Permintaan
                </x-ui.button>
            </x-ui.card>
        </aside>
    </div>
</x-layouts.admin>
