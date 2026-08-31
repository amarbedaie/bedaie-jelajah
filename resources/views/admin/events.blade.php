<x-layouts.admin title="Program" heading="Program">
    @if ($needsClosing > 0)
        <x-ui.alert variant="warning" icon="alert" class="mb-5" title="Program menunggu penutupan">
            {{ $needsClosing }} program telah tamat tetapi belum ditutup. Menutup program akan
            melepaskan sijil kepada peserta yang hadir.
            <a href="{{ route('admin.program', ['status' => 'perlu_ditutup']) }}" class="font-medium underline">
                Lihat senarai
            </a>
        </x-ui.alert>
    @endif

    <form method="GET" class="mb-5 grid gap-3 sm:grid-cols-[minmax(0,1fr)_12rem_12rem_auto]">
        <x-ui.input name="q" :value="request('q')" icon="search"
                    placeholder="Cari tajuk atau kod program…" aria-label="Carian program" />
        <x-ui.select name="status" aria-label="Tapis mengikut status">
            <option value="">Semua status</option>
            <option value="perlu_ditutup" @selected(request('status') === 'perlu_ditutup')>Perlu ditutup</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>
                    {{ $status->label() }}
                </option>
            @endforeach
        </x-ui.select>
        <x-ui.select name="negeri" aria-label="Tapis mengikut negeri">
            <option value="">Semua negeri</option>
            @foreach ($states as $state)
                <option value="{{ $state->slug }}" @selected(request('negeri') === $state->slug)>{{ $state->name }}</option>
            @endforeach
        </x-ui.select>
        <x-ui.button type="submit" variant="outline" icon="filter">Tapis</x-ui.button>
    </form>

    @if ($events->isEmpty())
        <x-ui.empty-state icon="calendar" title="Tiada program sepadan"
            description="Program dijana automatik apabila permohonan disahkan." />
    @else
        <x-jelajah.admin-table caption="Senarai program"
            :headers="['Kod', 'Program', 'Tarikh', 'Lokasi', 'Pendaftaran', 'Status', '']">
            @foreach ($events as $event)
                <tr class="hover:bg-mist/40">
                    <td class="px-4 py-3 font-mono text-[0.8125rem] text-ink-soft">{{ $event->short_code }}</td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-ink">{{ $event->title }}</p>
                        <p class="text-[0.8125rem] text-ink-muted">
                            {{ $event->category?->name }}
                            @if ($event->speaker) &middot; {{ $event->speaker->name }} @endif
                        </p>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-ink-soft">
                        {{ $event->starts_at->translatedFormat('j M Y') }}
                        <span class="block text-[0.8125rem] text-ink-muted">{{ $event->timeLabel() }}</span>
                    </td>
                    <td class="px-4 py-3 text-ink-soft">
                        {{ $event->venue?->name ?? '—' }}
                        <span class="block text-[0.8125rem] text-ink-muted">{{ $event->state?->name }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <p class="text-ink">
                            {{ number_format($event->seatsTaken()) }}/{{ number_format($event->capacity ?? 0) }}
                        </p>
                        @if ($event->hasEnded())
                            <p class="text-[0.8125rem] text-ink-muted">{{ number_format($event->attended_count) }} hadir</p>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <x-ui.badge :color="$event->status->color()" dot>{{ $event->status->label() }}</x-ui.badge>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <x-ui.button :href="route('admin.program.show', $event)" variant="ghost" size="sm">Buka</x-ui.button>
                    </td>
                </tr>
            @endforeach
        </x-jelajah.admin-table>

        <div class="mt-6">{{ $events->links() }}</div>
    @endif
</x-layouts.admin>
