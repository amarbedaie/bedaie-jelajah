<x-ui.card class="sm:p-7">
    {{-- Penanda dalaman: staf mesti tahu ini tidak pernah dilihat sesiapa
         di luar pasukan BeDaie, supaya mereka boleh menulis dengan jujur. --}}
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="font-display text-2xl text-ink">Perancangan Dalaman</h2>
            <p class="mt-1.5 text-sm text-ink-soft text-pretty">
                Objektif, aliran acara dan SOP untuk pasukan BeDaie.
            </p>
        </div>
        <x-ui.badge color="quiet" icon="lock">Staf sahaja</x-ui.badge>
    </div>

    <form wire:submit="save" class="mt-7 space-y-8">
        {{-- ── Objektif ─────────────────────────────────────── --}}
        <x-ui.field label="Objektif program" for="objectives"
                    hint="Apa yang program ini mahu capai. Satu objektif satu baris."
                    :error="$errors->first('objectives')">
            <x-ui.textarea id="objectives" wire:model="objectives" rows="4"
                           :error="$errors->has('objectives')"
                           placeholder="Contoh:&#10;Memperkenalkan fiqh solat kepada ahli kariah&#10;Mengumpul 150 peserta dari tiga surau berdekatan" />
        </x-ui.field>

        {{-- ── Aliran acara ─────────────────────────────────── --}}
        <div>
            <div class="flex items-baseline justify-between gap-3">
                <h3 class="text-sm font-medium text-ink">Aliran acara</h3>
                <span class="text-xs text-ink-muted">{{ count($steps) }} langkah</span>
            </div>

            <div class="mt-3 overflow-x-auto">
                <table class="w-full min-w-[38rem] border-separate border-spacing-0">
                    <thead>
                        <tr class="text-left text-xs font-medium uppercase tracking-wider text-ink-muted">
                            <th class="w-32 pb-2 pr-3">Masa</th>
                            <th class="pb-2 pr-3">Aktiviti</th>
                            <th class="w-44 pb-2 pr-3">Siapa</th>
                            <th class="w-24 pb-2"><span class="sr-only">Tindakan</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($steps as $i => $step)
                            <tr wire:key="step-{{ $i }}" class="align-top">
                                <td class="border-t border-hairline py-2.5 pr-3">
                                    <label class="sr-only" for="masa-{{ $i }}">Masa langkah {{ $i + 1 }}</label>
                                    <x-ui.input id="masa-{{ $i }}" wire:model="steps.{{ $i }}.time_label"
                                                placeholder="8:30 mlm" />
                                </td>
                                <td class="border-t border-hairline py-2.5 pr-3">
                                    <label class="sr-only" for="aktiviti-{{ $i }}">Aktiviti langkah {{ $i + 1 }}</label>
                                    <x-ui.input id="aktiviti-{{ $i }}" wire:model="steps.{{ $i }}.activity"
                                                :error="$errors->has('steps.'.$i.'.activity')"
                                                placeholder="Pendaftaran dibuka" />
                                </td>
                                <td class="border-t border-hairline py-2.5 pr-3">
                                    <label class="sr-only" for="siapa-{{ $i }}">Bertanggungjawab langkah {{ $i + 1 }}</label>
                                    <x-ui.input id="siapa-{{ $i }}" wire:model="steps.{{ $i }}.responsible"
                                                placeholder="Penggerak" />
                                </td>
                                <td class="border-t border-hairline py-2.5">
                                    <div class="flex items-center gap-0.5">
                                        <button type="button" wire:click="moveStep({{ $i }}, -1)"
                                                @disabled($i === 0)
                                                class="tap-target grid place-items-center rounded-lg text-ink-muted
                                                       hover:bg-mist hover:text-ink disabled:opacity-30"
                                                aria-label="Naikkan langkah {{ $i + 1 }}">
                                            <x-ui.icon name="chevron-up" class="h-4 w-4" />
                                        </button>
                                        <button type="button" wire:click="moveStep({{ $i }}, 1)"
                                                @disabled($i === count($steps) - 1)
                                                class="tap-target grid place-items-center rounded-lg text-ink-muted
                                                       hover:bg-mist hover:text-ink disabled:opacity-30"
                                                aria-label="Turunkan langkah {{ $i + 1 }}">
                                            <x-ui.icon name="chevron-down" class="h-4 w-4" />
                                        </button>
                                        <button type="button" wire:click="removeStep({{ $i }})"
                                                class="tap-target grid place-items-center rounded-lg text-ink-muted
                                                       hover:bg-alert-soft hover:text-alert"
                                                aria-label="Buang langkah {{ $i + 1 }}">
                                            <x-ui.icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <x-ui.button type="button" wire:click="addStep" variant="outline" size="sm" class="mt-3" icon="plus">
                Tambah langkah
            </x-ui.button>

            <div class="mt-5">
                <x-ui.field label="Nota aliran" for="flowNotes" optional
                            hint="Perkara yang tidak muat dalam jadual di atas."
                            :error="$errors->first('flowNotes')">
                    <x-ui.textarea id="flowNotes" wire:model="flowNotes" rows="3"
                                   :error="$errors->has('flowNotes')"
                                   placeholder="Contoh: penceramah tiba awal 30 minit untuk sesi gambar." />
                </x-ui.field>
            </div>
        </div>

        {{-- ── SOP ──────────────────────────────────────────── --}}
        <x-ui.field label="SOP program" for="sop"
                    hint="Prosedur tetap: persiapan, semasa, dan selepas program."
                    :error="$errors->first('sop')">
            <x-ui.textarea id="sop" wire:model="sop" rows="7"
                           :error="$errors->has('sop')"
                           placeholder="Contoh:&#10;SEBELUM&#10;Sahkan PA system dua hari sebelum&#10;&#10;SEMASA&#10;Kaunter pendaftaran dibuka 30 minit awal&#10;&#10;SELEPAS&#10;Imbas kehadiran sebelum bersurai" />
        </x-ui.field>

        <div class="flex flex-wrap items-center gap-3 border-t border-hairline pt-6">
            <x-ui.button type="submit" variant="primary">
                <span wire:loading.remove wire:target="save">Simpan Perancangan</span>
                <span wire:loading wire:target="save">Menyimpan…</span>
            </x-ui.button>

            <x-ui.button :href="route('admin.program.perancangan', $this->event)" target="_blank"
                         variant="outline" icon="external">
                Buka Versi Cetak
            </x-ui.button>
        </div>
    </form>
</x-ui.card>
