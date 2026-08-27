<x-layouts.admin title="Peserta" heading="Peserta">
    <form method="GET" class="mb-5 grid gap-3 sm:grid-cols-[minmax(0,1fr)_12rem_12rem_auto]">
        <x-ui.input name="q" :value="request('q')" icon="search"
                    placeholder="Cari nama, telefon, e-mel atau rujukan…" aria-label="Carian peserta" />
        <x-ui.select name="negeri" aria-label="Tapis mengikut negeri">
            <option value="">Semua negeri</option>
            @foreach ($states as $state)
                <option value="{{ $state->slug }}" @selected(request('negeri') === $state->slug)>{{ $state->name }}</option>
            @endforeach
        </x-ui.select>
        <x-ui.select name="status" aria-label="Tapis mengikut status">
            <option value="">Semua status</option>
            @foreach (\App\Enums\RegistrationStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>
                    {{ $status->label() }}
                </option>
            @endforeach
        </x-ui.select>
        <x-ui.button type="submit" variant="outline" icon="filter">Tapis</x-ui.button>
    </form>

    @if ($registrations->isEmpty())
        <x-ui.empty-state icon="users" title="Tiada peserta sepadan"
            description="Cuba ubah penapis atau kata kunci carian." />
    @else
        <x-jelajah.admin-table caption="Direktori peserta"
            :headers="['Peserta', 'Program', 'Kawasan', 'Tempat', 'Status', 'Kehadiran', 'Bayaran']">
            @foreach ($registrations as $registration)
                <tr class="hover:bg-mist/40">
                    <td class="px-4 py-3">
                        <p class="font-medium text-ink">{{ $registration->name }}</p>
                        <p class="font-mono text-xs text-ink-muted">{{ $registration->reference_no }}</p>
                        {{-- Nombor penuh sengaja tidak dipaparkan pada direktori --}}
                        <p class="font-mono text-xs text-ink-muted">{{ $registration->maskedPhone() }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.program.show', $registration->event) }}"
                           class="text-ink hover:text-clay-700">{{ $registration->event->title }}</a>
                        <span class="block text-xs text-ink-muted">
                            {{ $registration->event->starts_at->translatedFormat('j M Y') }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-ink-soft">{{ $registration->state?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-ink">{{ $registration->seats() }}</td>
                    <td class="px-4 py-3">
                        <x-ui.badge :color="$registration->status->color()">{{ $registration->status->label() }}</x-ui.badge>
                    </td>
                    <td class="px-4 py-3">
                        @if ($registration->hasAttended())
                            <x-ui.badge color="success" icon="check">
                                {{ $registration->attendance->checked_in_at->format('g:ia') }}
                            </x-ui.badge>
                        @else
                            <span class="text-xs text-ink-muted">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if ($registration->payment && $registration->payment->amount > 0)
                            <x-ui.badge :color="$registration->payment->status->color()">
                                {{ $registration->payment->status->label() }}
                            </x-ui.badge>
                        @else
                            <span class="text-xs text-ink-muted">—</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </x-jelajah.admin-table>

        <div class="mt-6">{{ $registrations->links() }}</div>

        <p class="mt-5 text-sm text-ink-muted text-pretty">
            Nombor telefon dipaparkan separa pada direktori ini. Eksport penuh tersedia pada
            halaman program dan direkodkan dalam log aktiviti.
        </p>
    @endif
</x-layouts.admin>
