<div>
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <p class="max-w-2xl text-ink-soft text-pretty">
            Rakan dan penaja yang aktif dipaparkan pada halaman utama dan halaman Rakan & Penaja.
        </p>
        @unless ($showForm)
            <x-ui.button wire:click="create" variant="primary" size="sm" icon="plus">Tambah Rakan</x-ui.button>
        @endunless
    </div>

    @if ($showForm)
        <x-ui.card class="mb-6">
            <h3 class="font-semibold text-ink">
                {{ $editingId ? 'Kemas Kini Rakan' : 'Rakan Baharu' }}
            </h3>

            <form wire:submit="save" class="mt-4 space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.field label="Nama" for="r-name" required :error="$errors->first('name')">
                        <x-ui.input id="r-name" wire:model="name" :error="$errors->has('name')" />
                    </x-ui.field>

                    <x-ui.field label="Jenis" for="r-type" required :error="$errors->first('type')">
                        <x-ui.select id="r-type" wire:model="type" :error="$errors->has('type')">
                            @foreach ($types as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </x-ui.select>
                    </x-ui.field>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.field label="Pautan laman" for="r-url" optional :error="$errors->first('website_url')">
                        <x-ui.input id="r-url" type="url" wire:model="website_url"
                                    :error="$errors->has('website_url')" placeholder="https://" />
                    </x-ui.field>

                    <x-ui.field label="Tahap" for="r-tier" optional
                                hint="Contoh: Platinum, Emas" :error="$errors->first('tier')">
                        <x-ui.input id="r-tier" wire:model="tier" :error="$errors->has('tier')" />
                    </x-ui.field>
                </div>

                <x-ui.field label="Penerangan" for="r-desc" optional :error="$errors->first('description')">
                    <x-ui.textarea id="r-desc" wire:model="description" rows="2"
                                   :error="$errors->has('description')" />
                </x-ui.field>

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.field label="Logo" for="r-logo" optional
                                hint="PNG, SVG atau WEBP. Maksimum 2MB. Latar lutsinar paling sesuai."
                                :error="$errors->first('logo')">
                        <input id="r-logo" type="file" wire:model="logo"
                               accept="image/jpeg,image/png,image/webp,image/svg+xml"
                               class="tap-target w-full rounded-xl border border-hairline bg-surface px-4 py-2.5
                                      text-sm text-ink file:mr-3 file:rounded-full file:border-0 file:bg-clay-50
                                      file:px-4 file:py-1.5 file:text-sm file:font-medium file:text-clay-700" />
                    </x-ui.field>

                    <x-ui.field label="Susunan" for="r-sort" optional :error="$errors->first('sort_order')">
                        <x-ui.input id="r-sort" type="number" min="0" wire:model="sort_order"
                                    :error="$errors->has('sort_order')" />
                    </x-ui.field>
                </div>

                <div class="grid gap-2.5 sm:grid-cols-2">
                    <x-ui.choice type="checkbox" name="is_active" value="1"
                                 wire:model="is_active" :checked="$is_active" label="Aktif" />
                    <x-ui.choice type="checkbox" name="is_featured" value="1"
                                 wire:model="is_featured" :checked="$is_featured"
                                 label="Papar pada halaman utama" />
                </div>

                <div class="flex flex-wrap gap-2.5">
                    <x-ui.button type="submit" variant="primary"
                                 wire:loading.attr="disabled" wire:target="save,logo">
                        Simpan
                    </x-ui.button>
                    <x-ui.button type="button" wire:click="cancel" variant="ghost">Batal</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    @endif

    @if ($partners->isEmpty())
        <x-ui.empty-state icon="handshake" title="Belum ada rakan atau penaja"
            description="Tambah rakan supaya mereka dipaparkan pada halaman awam." />
    @else
        <div class="space-y-8">
            @foreach ($partners as $type => $group)
                <section>
                    <h3 class="mb-3 text-lg font-semibold text-ink">
                        {{ $types[$type] ?? \Illuminate\Support\Str::headline($type) }}
                    </h3>

                    <x-jelajah.admin-table :headers="['Nama', 'Laman', 'Tahap', 'Susunan', 'Status', '']">
                        @foreach ($group as $partner)
                            <tr wire:key="partner-{{ $partner->id }}" class="hover:bg-mist/40">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        @if ($partner->logo_path)
                                            <img src="{{ Storage::url($partner->logo_path) }}" alt=""
                                                 class="h-8 w-auto max-w-24 object-contain" />
                                        @else
                                            <span class="grid h-8 w-8 place-items-center rounded-lg bg-clay-50">
                                                <x-ui.icon name="building" class="h-4 w-4 text-clay-600" />
                                            </span>
                                        @endif
                                        <span class="font-medium text-ink">{{ $partner->name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    @if ($partner->website_url)
                                        <a href="{{ $partner->website_url }}" target="_blank" rel="noopener"
                                           class="text-sm text-clay-600 hover:underline">
                                            {{ \Illuminate\Support\Str::limit($partner->website_url, 32) }}
                                        </a>
                                    @else
                                        <span class="text-[0.8125rem] text-ink-muted">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-ink-soft">{{ $partner->tier ?: '—' }}</td>
                                <td class="px-4 py-3 text-ink-soft">{{ $partner->sort_order }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-1.5">
                                        <button type="button" wire:click="toggleActive({{ $partner->id }})"
                                                aria-label="Tukar status aktif">
                                            <x-ui.badge :color="$partner->is_active ? 'success' : 'grey'">
                                                {{ $partner->is_active ? 'Aktif' : 'Tidak aktif' }}
                                            </x-ui.badge>
                                        </button>
                                        @if ($partner->is_featured)
                                            <x-ui.badge color="purple">Utama</x-ui.badge>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-1">
                                        <x-ui.button wire:click="edit({{ $partner->id }})"
                                                     variant="ghost" size="sm" icon="edit">Ubah</x-ui.button>
                                        <x-ui.button wire:click="delete({{ $partner->id }})"
                                                     wire:confirm="Buang rakan ini?"
                                                     variant="ghost" size="sm" icon="trash">Buang</x-ui.button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </x-jelajah.admin-table>
                </section>
            @endforeach
        </div>
    @endif
</div>
