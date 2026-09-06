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


        {{-- ── Pakej jualan ─────────────────────────────────── --}}
        <div>
            <div class="flex flex-wrap items-baseline justify-between gap-3">
                <div>
                    <h3 class="text-sm font-medium text-ink">Apa yang dijual</h3>
                    <p class="mt-1 text-sm text-ink-soft">
                        Pilih pakej yang dibawa. Tandakan satu sebagai tumpuan utama.
                    </p>
                </div>
                <x-ui.button :href="route('admin.pakej')" variant="ghost" size="sm" icon="external">
                    Urus katalog
                </x-ui.button>
            </div>

            @if ($katalog->isEmpty())
                <x-ui.empty-state icon="box" title="Katalog pakej masih kosong"
                                  description="Cipta pakej sekali, guna untuk semua program."
                                  compact class="mt-4">
                    <x-ui.button :href="route('admin.pakej')" variant="primary" size="sm" icon="plus">
                        Cipta Pakej
                    </x-ui.button>
                </x-ui.empty-state>
            @else
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach ($katalog as $pakej)
                        @php
                            $dipilih = array_key_exists($pakej->id, $packages);
                            $fokus = $dipilih && ($packages[$pakej->id]['focus'] ?? false);
                        @endphp
                        <div wire:key="pakej-{{ $pakej->id }}"
                             class="rounded-card border p-4 transition-colors
                                    {{ $fokus ? 'border-brand-500 bg-brand-50'
                                       : ($dipilih ? 'border-brand-300 bg-surface' : 'border-hairline bg-surface') }}">
                            <div class="flex items-start justify-between gap-3">
                                <button type="button" wire:click="togglePackage({{ $pakej->id }})"
                                        class="flex min-w-0 flex-1 items-start gap-3 text-left">
                                    <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded border
                                                 {{ $dipilih ? 'border-brand-600 bg-brand-600' : 'border-control-line bg-surface' }}">
                                        @if ($dipilih)
                                            <x-ui.icon name="check" class="h-3.5 w-3.5 text-white" />
                                        @endif
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block font-medium text-ink text-pretty">{{ $pakej->name }}</span>
                                        @if ($pakej->tagline)
                                            <span class="mt-0.5 block text-sm text-ink-soft text-pretty">{{ $pakej->tagline }}</span>
                                        @endif
                                        <span class="mt-1.5 block font-display text-lg text-ink">{{ $pakej->priceLabel() }}</span>
                                    </span>
                                </button>

                                @if ($dipilih)
                                    <button type="button" wire:click="setFocus({{ $pakej->id }})"
                                            class="tap-target grid shrink-0 place-items-center rounded-lg
                                                   {{ $fokus ? 'text-brand-700' : 'text-ink-muted hover:text-brand-700' }}"
                                            aria-label="Tandakan {{ $pakej->name }} sebagai tumpuan utama"
                                            aria-pressed="{{ $fokus ? 'true' : 'false' }}">
                                        <x-ui.icon name="star" :fill="$fokus ? 'currentColor' : 'none'" class="h-5 w-5" />
                                    </button>
                                @endif
                            </div>

                            @if ($dipilih)
                                <div class="mt-3 flex items-center gap-2.5 border-t border-hairline pt-3">
                                    <label for="sasaran-{{ $pakej->id }}" class="text-sm text-ink-soft">Sasaran</label>
                                    <input id="sasaran-{{ $pakej->id }}" type="number" min="0" inputmode="numeric"
                                           wire:model="packages.{{ $pakej->id }}.target"
                                           placeholder="—"
                                           class="w-24 rounded-lg border border-control-line bg-surface px-3 py-1.5 text-sm
                                                  text-ink focus:border-brand-600 focus:outline-none focus:ring-3
                                                  focus:ring-brand-500/45">
                                    <span class="text-sm text-ink-muted">unit</span>
                                    @if ($fokus)
                                        <x-ui.badge color="soft" class="ml-auto">Tumpuan</x-ui.badge>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ── Pasukan bertugas ─────────────────────────────── --}}
        <div>
            <div class="flex items-baseline justify-between gap-3">
                <div>
                    <h3 class="text-sm font-medium text-ink">Siapa yang terlibat</h3>
                    <p class="mt-1 text-sm text-ink-soft">
                        Termasuk AJK masjid dan sukarelawan tempatan.
                    </p>
                </div>
                <span class="text-xs text-ink-muted">{{ count($team) }} orang</span>
            </div>

            <div class="mt-4 space-y-2.5">
                @foreach ($team as $i => $member)
                    <div wire:key="ahli-{{ $i }}"
                         class="grid gap-2.5 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_10rem_auto] sm:items-start">
                        <div>
                            <label class="sr-only" for="peranan-{{ $i }}">Peranan ahli {{ $i + 1 }}</label>
                            <x-ui.input id="peranan-{{ $i }}" wire:model="team.{{ $i }}.role"
                                        placeholder="Ketua program" />
                        </div>
                        <div>
                            <label class="sr-only" for="nama-{{ $i }}">Nama ahli {{ $i + 1 }}</label>
                            <x-ui.input id="nama-{{ $i }}" wire:model="team.{{ $i }}.name"
                                        placeholder="Nama penuh" />
                        </div>
                        <div>
                            <label class="sr-only" for="telefon-{{ $i }}">Nombor ahli {{ $i + 1 }}</label>
                            <x-ui.input id="telefon-{{ $i }}" wire:model="team.{{ $i }}.phone"
                                        type="tel" inputmode="tel" placeholder="012-345 6789" />
                        </div>
                        <button type="button" wire:click="removeMember({{ $i }})"
                                class="tap-target grid place-items-center rounded-lg text-ink-muted
                                       hover:bg-alert-soft hover:text-alert"
                                aria-label="Buang ahli {{ $i + 1 }}">
                            <x-ui.icon name="trash" class="h-4 w-4" />
                        </button>
                    </div>
                @endforeach
            </div>

            <x-ui.button type="button" wire:click="addMember" variant="outline" size="sm" class="mt-3" icon="plus">
                Tambah orang
            </x-ui.button>
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
