<x-layouts.admin title="Negeri & Daerah" heading="Negeri & Daerah">
    <p class="mb-6 text-ink-soft text-pretty">
        Status jelajah setiap negeri dikira automatik daripada program yang telah diterbitkan.
    </p>

    <div class="mb-8">
        <x-jelajah.map :states="$states" />
    </div>

    <x-jelajah.admin-table caption="Statistik negeri"
        :headers="['Negeri', 'Status', 'Program', 'Selesai', 'Akan datang', 'Peserta', 'Daerah', 'Permintaan']">
        @foreach ($states as $state)
            @php $model = $raw[$state['id']] ?? null; @endphp
            <tr class="hover:bg-mist/40">
                <td class="px-4 py-3">
                    <p class="font-medium text-ink">{{ $state['name'] }}</p>
                    <p class="text-xs text-ink-muted">{{ $state['code'] }} &middot; {{ ucfirst($state['region']) }}</p>
                </td>
                <td class="px-4 py-3">
                    @php
                        $labels = [
                            'dijelajahi' => ['purple', 'Sudah dijelajahi'],
                            'akan_datang' => ['warning', 'Akan datang'],
                            'berlangsung' => ['success', 'Berlangsung'],
                            'belum' => ['grey', 'Belum dijelajahi'],
                        ];
                        [$colour, $label] = $labels[$state['status']] ?? $labels['belum'];
                    @endphp
                    <x-ui.badge :color="$colour" dot>{{ $label }}</x-ui.badge>
                    @if ($state['high_demand'])
                        <x-ui.badge color="danger" class="ml-1">Permintaan tinggi</x-ui.badge>
                    @endif
                </td>
                <td class="px-4 py-3 text-ink">{{ number_format($state['events']) }}</td>
                <td class="px-4 py-3 text-ink-soft">{{ number_format($state['completed']) }}</td>
                <td class="px-4 py-3 text-ink-soft">{{ number_format($state['upcoming']) }}</td>
                <td class="px-4 py-3 text-ink">{{ number_format($state['participants']) }}</td>
                <td class="px-4 py-3 text-ink-soft">
                    {{ number_format($state['districts']) }} / {{ number_format($model?->districts_count ?? 0) }}
                </td>
                <td class="px-4 py-3">
                    @if ($state['interest'] > 0)
                        <x-ui.badge color="purple">{{ number_format($state['interest']) }}</x-ui.badge>
                    @else
                        <span class="text-xs text-ink-muted">—</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </x-jelajah.admin-table>
</x-layouts.admin>
