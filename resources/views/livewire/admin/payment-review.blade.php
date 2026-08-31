<div>
    <div class="mb-5 grid gap-3 sm:grid-cols-[minmax(0,1fr)_14rem]">
        <div>
            <label for="p-search" class="sr-only">Cari peserta</label>
            <x-ui.input id="p-search" wire:model.live.debounce.400ms="search" icon="search"
                        placeholder="Cari nama, rujukan atau telefon…" />
        </div>
        <div>
            <label for="p-status" class="sr-only">Tapis status</label>
            <x-ui.select id="p-status" wire:model.live="status">
                <option value="">Semua status</option>
                @foreach ($statuses as $option)
                    <option value="{{ $option->value }}">{{ $option->label() }}</option>
                @endforeach
            </x-ui.select>
        </div>
    </div>

    @if ($pendingTotal > 0 && $status !== 'belum_bayar')
        <x-ui.alert variant="warning" icon="alert" class="mb-5">
            {{ $pendingTotal }} bayaran masih menunggu pengesahan.
            <button type="button" wire:click="$set('status', 'belum_bayar')" class="font-medium underline">
                Papar sekarang
            </button>
        </x-ui.alert>
    @endif

    @if ($payments->isEmpty())
        <x-ui.empty-state icon="ticket" title="Tiada bayaran sepadan"
            description="Bayaran hanya wujud untuk program berbayar." />
    @else
        <x-jelajah.admin-table caption="Senarai bayaran"
            :headers="['Peserta', 'Program', 'Jumlah', 'Gateway', 'Status', 'Tarikh', '']">
            @foreach ($payments as $payment)
                <tr wire:key="payment-{{ $payment->id }}" class="hover:bg-mist/40">
                    <td class="px-4 py-3">
                        <p class="font-medium text-ink">{{ $payment->registration?->name ?? '—' }}</p>
                        <p class="font-mono text-[0.8125rem] text-ink-muted">{{ $payment->registration?->reference_no }}</p>
                    </td>
                    <td class="px-4 py-3 text-ink-soft">
                        {{ \Illuminate\Support\Str::limit($payment->event?->title ?? '—', 36) }}
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 font-medium text-ink">
                        RM {{ number_format((float) $payment->amount, 2) }}
                    </td>
                    <td class="px-4 py-3 text-[0.8125rem] text-ink-muted">
                        {{ $payment->gateway }}
                        @if ($payment->gateway_reference)
                            <span class="block font-mono">{{ $payment->gateway_reference }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <x-ui.badge :color="$payment->status->color()">{{ $payment->status->label() }}</x-ui.badge>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-[0.8125rem] text-ink-muted">
                        {{ $payment->paid_at?->translatedFormat('j M Y') ?? $payment->created_at->translatedFormat('j M Y') }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex flex-wrap justify-end gap-1">
                            @if ($payment->status !== \App\Enums\PaymentStatus::Berjaya)
                                <x-ui.button wire:click="confirm({{ $payment->id }})"
                                             wire:confirm="Sahkan bayaran ini? Pendaftaran peserta akan diaktifkan."
                                             variant="success" size="sm" icon="check">
                                    Sahkan
                                </x-ui.button>
                                <x-ui.button wire:click="exempt({{ $payment->id }})"
                                             wire:confirm="Kecualikan peserta ini daripada bayaran?"
                                             variant="ghost" size="sm">
                                    Kecualikan
                                </x-ui.button>
                                <x-ui.button wire:click="markFailed({{ $payment->id }})"
                                             variant="ghost" size="sm">Gagal</x-ui.button>
                            @else
                                <x-ui.button wire:click="markRefunded({{ $payment->id }})"
                                             wire:confirm="Tandakan bayaran ini sebagai dipulangkan?"
                                             variant="ghost" size="sm" icon="refresh">
                                    Pulangkan
                                </x-ui.button>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-jelajah.admin-table>

        <div class="mt-6">{{ $payments->links() }}</div>
    @endif
</div>
