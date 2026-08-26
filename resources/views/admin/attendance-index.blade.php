<x-layouts.admin title="Kehadiran" heading="Kehadiran">
    @if ($today->isNotEmpty())
        <section class="mb-8">
            <h2 class="mb-3 text-lg font-semibold text-navy-900">Hari Ini</h2>
            <ul class="grid gap-4 sm:grid-cols-2">
                @foreach ($today as $event)
                    <li class="rounded-card border border-success/30 bg-success-soft p-5">
                        <p class="font-medium text-navy-900 text-pretty">{{ $event->title }}</p>
                        <p class="mt-0.5 text-sm text-ink-soft text-pretty">
                            {{ $event->timeLabel() }} &middot; {{ $event->venue?->name ?? $event->locationLabel() }}
                        </p>
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
        </section>
    @endif

    <section class="mb-8">
        <h2 class="mb-3 text-lg font-semibold text-navy-900">Akan Datang</h2>
        @if ($upcoming->isEmpty())
            <x-ui.empty-state compact icon="calendar" title="Tiada program akan datang" />
        @else
            <x-jelajah.admin-table :headers="['Program', 'Tarikh', 'Lokasi', 'Berdaftar', '']">
                @foreach ($upcoming as $event)
                    <tr class="hover:bg-mist/40">
                        <td class="px-4 py-3 font-medium text-navy-900">{{ $event->title }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-ink-soft">
                            {{ $event->starts_at->translatedFormat('j M Y') }}
                        </td>
                        <td class="px-4 py-3 text-ink-soft">{{ $event->venue?->name ?? $event->state?->name }}</td>
                        <td class="px-4 py-3 text-navy-900">{{ number_format($event->seatsTaken()) }}</td>
                        <td class="px-4 py-3 text-right">
                            <x-ui.button :href="route('admin.kehadiran.show', $event)" variant="ghost" size="sm">
                                Buka
                            </x-ui.button>
                        </td>
                    </tr>
                @endforeach
            </x-jelajah.admin-table>
        @endif
    </section>

    <section>
        <h2 class="mb-3 text-lg font-semibold text-navy-900">Program Selesai</h2>
        @if ($recent->isEmpty())
            <x-ui.empty-state compact icon="check-circle" title="Belum ada program selesai" />
        @else
            <x-jelajah.admin-table :headers="['Program', 'Tarikh', 'Berdaftar', 'Hadir', 'Kadar', '']">
                @foreach ($recent as $event)
                    <tr class="hover:bg-mist/40">
                        <td class="px-4 py-3 font-medium text-navy-900">{{ $event->title }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-ink-soft">
                            {{ $event->starts_at->translatedFormat('j M Y') }}
                        </td>
                        <td class="px-4 py-3 text-navy-900">{{ number_format($event->registered_count) }}</td>
                        <td class="px-4 py-3 text-navy-900">{{ number_format($event->attended_count) }}</td>
                        <td class="px-4 py-3">
                            <x-ui.badge color="grey">{{ number_format($event->attendanceRate(), 0) }}%</x-ui.badge>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <x-ui.button :href="route('admin.kehadiran.show', $event)" variant="ghost" size="sm">
                                Buka
                            </x-ui.button>
                        </td>
                    </tr>
                @endforeach
            </x-jelajah.admin-table>
        @endif
    </section>
</x-layouts.admin>
