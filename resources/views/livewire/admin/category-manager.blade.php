<div>
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <p class="max-w-2xl text-ink-soft text-pretty">
            Kategori ini dipaparkan pada halaman "Pilihan Program" dan borang permohonan.
        </p>
        @unless ($showForm)
            <x-ui.button wire:click="create" variant="primary" size="sm" icon="plus">Tambah Kategori</x-ui.button>
        @endunless
    </div>

    @if ($showForm)
        <x-ui.card class="mb-6">
            <h3 class="font-semibold text-navy-900">
                {{ $editingId ? 'Kemas Kini Kategori' : 'Kategori Baharu' }}
            </h3>

            <form wire:submit="save" class="mt-4 space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.field label="Nama kategori" for="c-name" required :error="$errors->first('name')">
                        <x-ui.input id="c-name" wire:model="name" :error="$errors->has('name')"
                                    placeholder="Contoh: Jelajah Ilmu" />
                    </x-ui.field>

                    <x-ui.field label="Slogan" for="c-tagline" optional :error="$errors->first('tagline')">
                        <x-ui.input id="c-tagline" wire:model="tagline" :error="$errors->has('tagline')" />
                    </x-ui.field>
                </div>

                <x-ui.field label="Penerangan" for="c-desc" optional
                            hint="Dipaparkan pada kad kategori di halaman awam."
                            :error="$errors->first('description')">
                    <x-ui.textarea id="c-desc" wire:model="description" rows="2"
                                   :error="$errors->has('description')" />
                </x-ui.field>

                <fieldset>
                    <legend class="mb-2.5 block text-sm font-medium text-navy-900">
                        Ikon <span class="text-danger" aria-hidden="true">*</span>
                    </legend>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($icons as $option)
                            <button type="button" wire:click="$set('icon', '{{ $option }}')"
                                    aria-pressed="{{ $icon === $option ? 'true' : 'false' }}"
                                    aria-label="Ikon {{ $option }}"
                                    class="tap-target grid place-items-center rounded-xl border transition
                                           {{ $icon === $option
                                              ? 'border-brand-500 bg-brand-50 text-brand-700 ring-1 ring-brand-500'
                                              : 'border-hairline bg-surface text-ink-soft hover:border-brand-300' }}">
                                <x-ui.icon :name="$option" class="h-5 w-5" />
                            </button>
                        @endforeach
                    </div>
                    @error('icon')
                        <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </fieldset>

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.field label="Susunan" for="c-sort" optional :error="$errors->first('sort_order')">
                        <x-ui.input id="c-sort" type="number" min="0" wire:model="sort_order"
                                    :error="$errors->has('sort_order')" />
                    </x-ui.field>

                    <div class="flex items-end">
                        <x-ui.choice type="checkbox" name="is_active" value="1"
                                     wire:model="is_active" :checked="$is_active"
                                     label="Aktif"
                                     hint="Hanya kategori aktif boleh dipilih oleh pemohon." />
                    </div>
                </div>

                <div class="flex flex-wrap gap-2.5">
                    <x-ui.button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="save">
                        Simpan
                    </x-ui.button>
                    <x-ui.button type="button" wire:click="cancel" variant="ghost">Batal</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    @endif

    @if ($categories->isEmpty())
        <x-ui.empty-state icon="list" title="Belum ada kategori" />
    @else
        <x-jelajah.admin-table caption="Kategori program"
            :headers="['Kategori', 'Penerangan', 'Program', 'Permohonan', 'Permintaan', 'Status', '']">
            @foreach ($categories as $category)
                <tr wire:key="category-{{ $category->id }}" class="hover:bg-mist/40">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-brand-50">
                                <x-ui.icon :name="$category->icon ?? 'book'" class="h-4 w-4 text-brand-600" />
                            </span>
                            <div class="min-w-0">
                                <p class="font-medium text-navy-900">{{ $category->name }}</p>
                                <p class="font-mono text-xs text-ink-muted">{{ $category->slug }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="max-w-md px-4 py-3 text-ink-soft text-pretty">
                        {{ \Illuminate\Support\Str::limit($category->description, 90) }}
                    </td>
                    <td class="px-4 py-3 text-navy-900">{{ $category->events_count }}</td>
                    <td class="px-4 py-3 text-navy-900">{{ $category->applications_count }}</td>
                    <td class="px-4 py-3 text-navy-900">{{ $category->area_interest_requests_count }}</td>
                    <td class="px-4 py-3">
                        <button type="button" wire:click="toggleActive({{ $category->id }})"
                                aria-label="Tukar status aktif">
                            <x-ui.badge :color="$category->is_active ? 'success' : 'grey'">
                                {{ $category->is_active ? 'Aktif' : 'Tidak aktif' }}
                            </x-ui.badge>
                        </button>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex justify-end gap-1">
                            <x-ui.button wire:click="edit({{ $category->id }})" variant="ghost" size="sm" icon="edit">
                                Ubah
                            </x-ui.button>
                            <x-ui.button wire:click="delete({{ $category->id }})"
                                         wire:confirm="Buang kategori ini?"
                                         variant="ghost" size="sm" icon="trash">
                                Buang
                            </x-ui.button>
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-jelajah.admin-table>
    @endif
</div>
