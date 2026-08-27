<x-layouts.admin title="Sijil" heading="Sijil">
    <form method="GET" class="mb-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-[minmax(0,1fr)_12rem_12rem_12rem_auto]">
        <x-ui.input name="q" :value="request('q')" icon="search"
                    placeholder="Cari nama atau nombor sijil…" aria-label="Carian sijil" />
        <x-ui.select name="program" aria-label="Tapis mengikut program">
            <option value="">Semua program</option>
            @foreach ($events as $event)
                <option value="{{ $event->short_code }}" @selected(request('program') === $event->short_code)>
                    {{ \Illuminate\Support\Str::limit($event->title, 40) }}
                </option>
            @endforeach
        </x-ui.select>
        <x-ui.select name="jenis" aria-label="Tapis mengikut jenis">
            <option value="">Semua jenis</option>
            @foreach ($types as $type)
                <option value="{{ $type->value }}" @selected(request('jenis') === $type->value)>{{ $type->label() }}</option>
            @endforeach
        </x-ui.select>
        <x-ui.select name="status" aria-label="Tapis mengikut status">
            <option value="">Semua status</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </x-ui.select>
        <x-ui.button type="submit" variant="navy" icon="filter">Tapis</x-ui.button>
    </form>

    @if ($certificates->isEmpty())
        <x-ui.empty-state icon="certificate" title="Tiada sijil sepadan"
            description="Sijil dijana automatik apabila program ditutup." />
    @else
        <x-jelajah.admin-table caption="Senarai sijil"
            :headers="['Nombor', 'Penerima', 'Program', 'Jenis', 'Status', 'Dikeluarkan', '']">
            @foreach ($certificates as $certificate)
                <tr class="hover:bg-mist/40">
                    <td class="px-4 py-3 font-mono text-xs text-ink-soft">{{ $certificate->certificate_number }}</td>
                    <td class="px-4 py-3 font-medium text-ink">{{ $certificate->recipient_name }}</td>
                    <td class="px-4 py-3 text-ink-soft">{{ $certificate->event_title }}</td>
                    <td class="px-4 py-3">
                        <x-ui.badge color="grey">{{ $certificate->type->label() }}</x-ui.badge>
                    </td>
                    <td class="px-4 py-3">
                        <x-ui.badge :color="$certificate->isValid() ? 'success' : 'danger'">
                            {{ $certificate->status->label() }}
                        </x-ui.badge>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-xs text-ink-muted">
                        {{ $certificate->issued_at?->translatedFormat('j M Y') }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <livewire:admin.certificate-actions :certificate="$certificate"
                                                            :key="'cert-'.$certificate->id" />
                    </td>
                </tr>
            @endforeach
        </x-jelajah.admin-table>

        <div class="mt-6">{{ $certificates->links() }}</div>
    @endif
</x-layouts.admin>
