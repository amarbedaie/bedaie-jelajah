<x-layouts.admin title="Penggerak" heading="Penggerak Jelajah">
    <form method="GET" class="mb-5 grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto]">
        <x-ui.input name="q" :value="request('q')" icon="search"
                    placeholder="Cari nama, e-mel atau telefon…" aria-label="Carian penggerak" />
        <x-ui.button type="submit" variant="outline" icon="search">Cari</x-ui.button>
    </form>

    @if ($users->isEmpty())
        <x-ui.empty-state icon="map" title="Tiada penggerak sepadan"
            description="Akaun Penggerak dicipta automatik apabila permohonan dihantar." />
    @else
        <x-jelajah.admin-table caption="Senarai penggerak jelajah"
            :headers="['Nama', 'Hubungi', 'Kawasan', 'Organisasi', 'Permohonan', 'Program', '']">
            @foreach ($users as $user)
                <tr class="hover:bg-mist/40">
                    <td class="px-4 py-3">
                        <p class="font-medium text-ink">{{ $user->name }}</p>
                        <p class="text-[0.8125rem] text-ink-muted">{{ $user->mobilizerProfile?->background?->label() }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <a href="https://wa.me/{{ $user->phone }}" target="_blank" rel="noopener"
                           class="text-sm font-medium text-brand-600 hover:underline">{{ $user->phone }}</a>
                        <span class="block text-[0.8125rem] text-ink-muted">{{ $user->email }}</span>
                    </td>
                    <td class="px-4 py-3 text-ink-soft">
                        {{ $user->district?->name ?? '—' }}
                        <span class="block text-[0.8125rem] text-ink-muted">{{ $user->state?->name }}</span>
                    </td>
                    <td class="px-4 py-3 text-ink-soft">{{ $user->mobilizerProfile?->organization_name ?? '—' }}</td>
                    <td class="px-4 py-3 text-ink">{{ $user->applications_count }}</td>
                    <td class="px-4 py-3 text-ink">{{ $user->mobilized_events_count }}</td>
                    <td class="px-4 py-3 text-right">
                        <x-ui.button :href="route('admin.penggerak.show', $user)" variant="ghost" size="sm">Buka</x-ui.button>
                    </td>
                </tr>
            @endforeach
        </x-jelajah.admin-table>

        <div class="mt-6">{{ $users->links() }}</div>
    @endif
</x-layouts.admin>
