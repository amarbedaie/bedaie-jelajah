<x-layouts.admin title="Permintaan Kawasan" heading="Permintaan Kawasan">
    <p class="mb-6 text-ink-soft text-pretty">
        Permintaan komuniti digabungkan mengikut negeri dan daerah. Gunakan senarai ini
        untuk merancang jelajah seterusnya. Maklumat individu tidak dipaparkan kepada umum.
    </p>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)]">
        <section>
            <h2 class="mb-3 text-lg font-semibold text-navy-900">Kawasan Permintaan Tertinggi</h2>
            @if ($areas->isEmpty())
                <x-ui.empty-state compact icon="heart" title="Belum ada permintaan" />
            @else
                <x-jelajah.admin-table :headers="['Daerah', 'Negeri', 'Jumlah permintaan', 'Individu unik', '']">
                    @foreach ($areas as $area)
                        <tr class="hover:bg-mist/40">
                            <td class="px-4 py-3 font-medium text-navy-900">
                                {{ $area->district?->name ?? 'Tidak dinyatakan' }}
                            </td>
                            <td class="px-4 py-3 text-ink-soft">{{ $area->state?->name }}</td>
                            <td class="px-4 py-3 text-navy-900">{{ number_format($area->total) }}</td>
                            <td class="px-4 py-3 text-navy-900">{{ number_format($area->individuals) }}</td>
                            <td class="px-4 py-3 text-right">
                                @if ($area->state)
                                    <x-ui.button :href="route('peta.negeri', $area->state->slug)" target="_blank"
                                                 variant="ghost" size="sm">Lihat negeri</x-ui.button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </x-jelajah.admin-table>
            @endif
        </section>

        <aside class="space-y-5">
            <x-ui.card>
                <h2 class="font-semibold text-navy-900">Topik Paling Diminta</h2>
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
                <h2 class="font-semibold text-navy-900">Negeri Belum Dijelajahi</h2>
                @php $untouched = $states->where('status', 'belum')->sortByDesc('interest'); @endphp
                @if ($untouched->isEmpty())
                    <p class="mt-3 text-sm text-ink-muted">Semua negeri telah dijelajahi. Alhamdulillah.</p>
                @else
                    <ul class="mt-4 space-y-2.5">
                        @foreach ($untouched as $state)
                            <li class="flex items-center justify-between gap-3 text-sm">
                                <span class="text-ink-soft">{{ $state['name'] }}</span>
                                @if ($state['interest'] > 0)
                                    <x-ui.badge color="warning">{{ $state['interest'] }} permintaan</x-ui.badge>
                                @else
                                    <span class="text-xs text-ink-muted">Tiada permintaan</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>
        </aside>
    </div>
</x-layouts.admin>
