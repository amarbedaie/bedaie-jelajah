<x-layouts.admin title="Permohonan Jelajah" heading="Permohonan Jelajah">
    {{-- Penapis pantas mengikut status --}}
    <div class="no-scrollbar -mx-1 mb-5 flex gap-2 overflow-x-auto pb-1">
        <a href="{{ route('admin.permohonan') }}"
           class="tap-target flex shrink-0 items-center gap-2 rounded-full px-4 text-sm font-medium transition
                  {{ ! request('status') ? 'bg-clay-600 text-white' : 'bg-surface text-ink-soft ring-1 ring-hairline hover:bg-mist' }}">
            Semua <span class="text-[0.8125rem] opacity-70">{{ $counts->sum() }}</span>
        </a>
        @foreach ($statuses as $value => $label)
            @php $count = (int) ($counts[$value] ?? 0); @endphp
            @if ($count > 0 || request('status') === $value)
                <a href="{{ route('admin.permohonan', ['status' => $value]) }}"
                   class="tap-target flex shrink-0 items-center gap-2 rounded-full px-4 text-sm font-medium transition
                          {{ request('status') === $value ? 'bg-clay-600 text-white' : 'bg-surface text-ink-soft ring-1 ring-hairline hover:bg-mist' }}">
                    {{ $label }} <span class="text-[0.8125rem] opacity-70">{{ $count }}</span>
                </a>
            @endif
        @endforeach
    </div>

    {{-- Carian --}}
    <form method="GET" class="mb-5 grid gap-3 sm:grid-cols-[minmax(0,1fr)_14rem_auto]">
        <input type="hidden" name="status" value="{{ request('status') }}">
        <x-ui.input name="q" :value="request('q')" icon="search"
                    placeholder="Cari nama, lokasi, rujukan atau telefon…" aria-label="Carian permohonan" />
        <x-ui.select name="negeri" aria-label="Tapis mengikut negeri">
            <option value="">Semua negeri</option>
            @foreach ($states as $state)
                <option value="{{ $state->slug }}" @selected(request('negeri') === $state->slug)>{{ $state->name }}</option>
            @endforeach
        </x-ui.select>
        <x-ui.button type="submit" variant="outline" icon="filter">Tapis</x-ui.button>
    </form>

    @if ($applications->isEmpty())
        <x-ui.empty-state icon="inbox" title="Tiada permohonan sepadan"
            description="Cuba ubah penapis atau kata kunci carian." />
    @else
        <x-jelajah.admin-table caption="Senarai permohonan jelajah"
            :headers="['Rujukan', 'Lokasi & Pemohon', 'Kawasan', 'Program', 'Status', 'Dihantar', '']">
            @foreach ($applications as $application)
                <tr class="hover:bg-mist/40">
                    <td class="px-4 py-3 font-mono text-[0.8125rem] text-ink-soft">{{ $application->reference_no }}</td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-ink">{{ $application->venue_name }}</p>
                        <p class="text-[0.8125rem] text-ink-muted">{{ $application->applicant_name }}</p>
                    </td>
                    <td class="px-4 py-3 text-ink-soft">
                        {{ $application->district?->name ?? '—' }}
                        <span class="block text-[0.8125rem] text-ink-muted">{{ $application->state?->name }}</span>
                    </td>
                    <td class="px-4 py-3 text-ink-soft">{{ $application->category?->name ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <x-ui.badge :color="$application->status->color()" dot>
                            {{ $application->status->label() }}
                        </x-ui.badge>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-[0.8125rem] text-ink-muted">
                        {{ $application->submitted_at?->translatedFormat('j M Y') }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <x-ui.button :href="route('admin.permohonan.show', $application)" variant="ghost" size="sm">
                            Buka
                        </x-ui.button>
                    </td>
                </tr>
            @endforeach
        </x-jelajah.admin-table>

        <div class="mt-6">{{ $applications->links() }}</div>
    @endif
</x-layouts.admin>
