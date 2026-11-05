<div class="grid gap-6 lg:grid-cols-[16rem_minmax(0,1fr)]">
    {{-- Senarai blueprint --}}
    <aside class="space-y-2">
        @foreach ($blueprints as $bp)
            <button type="button" wire:click="select({{ $bp->id }})" wire:key="bp-{{ $bp->id }}"
                    class="w-full rounded-xl border p-3 text-left transition
                           {{ $selectedId === $bp->id ? 'border-brand-400 bg-brand-50 ring-1 ring-brand-400' : 'border-hairline bg-surface hover:border-brand-300' }}">
                <span class="block font-medium text-ink">{{ $bp->name }}</span>
                <span class="mt-0.5 block text-xs text-ink-muted">
                    {{ $bp->items_count }} item{{ $bp->is_active ? '' : ' · tidak aktif' }}
                </span>
            </button>
        @endforeach
        <x-ui.button wire:click="createBlueprint" variant="outline" size="sm" icon="plus" block>Blueprint Baharu</x-ui.button>
    </aside>

    @if ($selected)
        <div class="space-y-6">
            <x-ui.card>
                <form wire:submit="saveBlueprint" class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-ui.field label="Nama blueprint" for="bp-name" required :error="$errors->first('name')">
                            <x-ui.input id="bp-name" wire:model="name" :error="$errors->has('name')" />
                        </x-ui.field>
                        <x-ui.field label="Jenis lokasi" for="bp-type" optional
                                    hint="Digunakan untuk mencadangkan blueprint pada program.">
                            <x-ui.select id="bp-type" wire:model="venue_type">
                                <option value="">—</option>
                                @foreach ($venueTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </x-ui.select>
                        </x-ui.field>
                    </div>
                    <x-ui.field label="Penerangan" for="bp-desc" optional :error="$errors->first('description')">
                        <x-ui.textarea id="bp-desc" wire:model="description" rows="2" />
                    </x-ui.field>
                    <div class="flex flex-wrap items-center gap-2.5">
                        <x-ui.choice type="checkbox" name="is_active" value="1" wire:model="is_active" :checked="$is_active" label="Aktif" />
                        <x-ui.button type="submit" variant="primary">Simpan</x-ui.button>
                        <x-ui.button type="button" wire:click="duplicate" variant="ghost" icon="copy">Salin blueprint</x-ui.button>
                    </div>
                </form>
            </x-ui.card>

            <x-ui.card>
                <h3 class="font-semibold text-ink">Item SOP</h3>
                <p class="mt-1 text-sm text-ink-soft">
                    "Hari" = bila perlu siap berbanding tarikh program (−14 = 14 hari sebelum, 0 = hari program, 7 = seminggu selepas).
                </p>

                <div class="mt-5 grid gap-6 xl:grid-cols-3">
                    @foreach ($phases as $phase => $phaseLabel)
                        @php $list = $grouped->get($phase, collect())->values(); @endphp
                        <section>
                            <h4 class="border-b border-hairline pb-2 text-sm font-semibold uppercase tracking-wider text-ink-muted">
                                {{ $phaseLabel }} <span class="font-normal">({{ $list->count() }})</span>
                            </h4>
                            <ul class="mt-2 space-y-1.5">
                                @forelse ($list as $i => $item)
                                    <li wire:key="bpi-{{ $item->id }}" class="rounded-lg border border-hairline/70 p-2.5">
                                        @if ($editingItemId === $item->id)
                                            <div class="space-y-2">
                                                <x-ui.input wire:model="editTitle" aria-label="Item" />
                                                <x-ui.input type="number" wire:model="editOffset" aria-label="Hari" placeholder="Hari (cth. -14)" />
                                                <x-ui.textarea wire:model="editDetails" rows="2" aria-label="Butiran" placeholder="Butiran (pilihan)" />
                                                @error('editTitle') <p class="text-sm text-alert">{{ $message }}</p> @enderror
                                                <div class="flex gap-2">
                                                    <x-ui.button wire:click="saveItem" variant="primary" size="sm">Simpan</x-ui.button>
                                                    <x-ui.button wire:click="cancelEditItem" variant="ghost" size="sm">Batal</x-ui.button>
                                                </div>
                                            </div>
                                        @else
                                            <p class="text-sm text-ink text-pretty">{{ $item->title }}</p>
                                            <div class="mt-1 flex items-center justify-between gap-2">
                                                <span class="text-xs text-ink-muted">{{ \App\Models\BlueprintItem::offsetLabel($item->due_offset_days) }}</span>
                                                <span class="flex gap-0.5">
                                                    <button type="button" wire:click="moveItem({{ $item->id }}, -1)" aria-label="Naik" @disabled($i === 0)
                                                            class="rounded p-1 text-ink-muted hover:bg-mist disabled:opacity-30"><x-ui.icon name="chevron-up" class="h-4 w-4" /></button>
                                                    <button type="button" wire:click="moveItem({{ $item->id }}, 1)" aria-label="Turun" @disabled($i === $list->count() - 1)
                                                            class="rounded p-1 text-ink-muted hover:bg-mist disabled:opacity-30"><x-ui.icon name="chevron-down" class="h-4 w-4" /></button>
                                                    <button type="button" wire:click="editItem({{ $item->id }})" aria-label="Ubah"
                                                            class="rounded p-1 text-ink-muted hover:bg-mist"><x-ui.icon name="edit" class="h-4 w-4" /></button>
                                                    <button type="button" wire:click="removeItem({{ $item->id }})" wire:confirm="Buang item ini daripada blueprint?" aria-label="Buang"
                                                            class="rounded p-1 text-ink-muted hover:bg-mist"><x-ui.icon name="trash" class="h-4 w-4" /></button>
                                                </span>
                                            </div>
                                        @endif
                                    </li>
                                @empty
                                    <li class="p-2 text-sm text-ink-muted">Tiada item.</li>
                                @endforelse
                            </ul>
                        </section>
                    @endforeach
                </div>

                <form wire:submit="addItem" class="mt-6 grid gap-2.5 border-t border-hairline pt-5 sm:grid-cols-[10rem_minmax(0,1fr)_7rem_auto] sm:items-end">
                    <x-ui.field label="Fasa" for="bpi-phase">
                        <x-ui.select id="bpi-phase" wire:model="itemPhase">
                            @foreach ($phases as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </x-ui.select>
                    </x-ui.field>
                    <x-ui.field label="Item baharu" for="bpi-title" :error="$errors->first('itemTitle')">
                        <x-ui.input id="bpi-title" wire:model="itemTitle" placeholder="Contoh: Sahkan tempahan dewan" />
                    </x-ui.field>
                    <x-ui.field label="Hari" for="bpi-offset" :error="$errors->first('itemOffset')">
                        <x-ui.input id="bpi-offset" type="number" wire:model="itemOffset" placeholder="-7" />
                    </x-ui.field>
                    <x-ui.button type="submit" variant="outline" icon="plus">Tambah</x-ui.button>
                </form>
            </x-ui.card>
        </div>
    @endif
</div>
