<div class="flex flex-col items-end gap-2">
    @if ($mode === '')
        <div class="flex justify-end gap-1">
            <x-ui.button :href="$verifyUrl" target="_blank"
                         variant="ghost" size="sm" icon="shield">Semak</x-ui.button>

            @if ($isValid)
                <x-ui.button :href="$downloadUrl" target="_blank"
                             variant="ghost" size="sm" icon="download">PDF</x-ui.button>
                <x-ui.button wire:click="startRegenerate" variant="ghost" size="sm" icon="refresh">
                    Betulkan
                </x-ui.button>
                <x-ui.button wire:click="startRevoke" variant="ghost" size="sm" icon="x-circle">
                    Tarik
                </x-ui.button>
            @endif
        </div>
    @elseif ($mode === 'regenerate')
        <div class="w-full max-w-sm rounded-xl border border-brand-200 bg-brand-50/60 p-3 text-left">
            <p class="text-[0.8125rem] font-semibold text-ink">Betulkan nama pada sijil</p>
            <p class="mt-1 text-[0.8125rem] text-ink-soft text-pretty">
                Sijil baharu akan dijana. Sijil lama ditandakan "Digantikan".
            </p>

            <div class="mt-2.5">
                <label for="cn-{{ $certificateId }}" class="sr-only">Nama yang dibetulkan</label>
                <x-ui.input id="cn-{{ $certificateId }}" wire:model="correctedName"
                            :error="$errors->has('correctedName')" class="text-sm" />
                @error('correctedName')
                    <p class="mt-1.5 text-[0.8125rem] text-alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-2.5 flex gap-1.5">
                <x-ui.button wire:click="regenerate" variant="primary" size="sm"
                             wire:loading.attr="disabled" wire:target="regenerate">
                    Jana Semula
                </x-ui.button>
                <x-ui.button wire:click="cancel" variant="ghost" size="sm">Batal</x-ui.button>
            </div>
        </div>
    @elseif ($mode === 'revoke')
        <div class="w-full max-w-sm rounded-xl border border-alert/30 bg-alert-soft p-3 text-left">
            <p class="text-[0.8125rem] font-semibold text-ink">Tarik balik sijil</p>
            <p class="mt-1 text-[0.8125rem] text-ink-soft text-pretty">
                Sijil tidak lagi boleh dimuat turun dan pengesahan awam akan menunjukkan
                status ditarik balik.
            </p>

            <div class="mt-2.5">
                <label for="cr-{{ $certificateId }}" class="sr-only">Sebab pembatalan</label>
                <x-ui.input id="cr-{{ $certificateId }}" wire:model="revokeReason"
                            placeholder="Sebab pembatalan…"
                            :error="$errors->has('revokeReason')" class="text-sm" />
                @error('revokeReason')
                    <p class="mt-1.5 text-[0.8125rem] text-alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-2.5 flex gap-1.5">
                <x-ui.button wire:click="revoke" variant="danger" size="sm"
                             wire:loading.attr="disabled" wire:target="revoke">
                    Tarik Balik
                </x-ui.button>
                <x-ui.button wire:click="cancel" variant="ghost" size="sm">Batal</x-ui.button>
            </div>
        </div>
    @endif
</div>
