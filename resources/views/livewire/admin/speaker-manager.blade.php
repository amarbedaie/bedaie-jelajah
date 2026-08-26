<div>
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <p class="max-w-2xl text-ink-soft text-pretty">
            Penceramah ditetapkan oleh pasukan BeDaie semasa mengesahkan program.
            Platform ini bukan marketplace — pemohon tidak memilih penceramah.
        </p>
        @unless ($showForm)
            <x-ui.button wire:click="create" variant="primary" size="sm" icon="plus">Tambah Penceramah</x-ui.button>
        @endunless
    </div>

    @if ($showForm)
        <x-ui.card class="mb-6">
            <h3 class="font-semibold text-navy-900">
                {{ $editingId ? 'Kemas Kini Penceramah' : 'Penceramah Baharu' }}
            </h3>

            <form wire:submit="save" class="mt-4 space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.field label="Nama" for="s-name" required :error="$errors->first('name')">
                        <x-ui.input id="s-name" wire:model="name" :error="$errors->has('name')" />
                    </x-ui.field>

                    <x-ui.field label="Gelaran" for="s-title" optional
                                hint="Contoh: Pendakwah BeDaie" :error="$errors->first('title')">
                        <x-ui.input id="s-title" wire:model="title" :error="$errors->has('title')" />
                    </x-ui.field>
                </div>

                <x-ui.field label="Biodata" for="s-bio" optional :error="$errors->first('bio')">
                    <x-ui.textarea id="s-bio" wire:model="bio" rows="3" :error="$errors->has('bio')" />
                </x-ui.field>

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.field label="Gambar" for="s-photo" optional
                                hint="JPG, PNG atau WEBP. Maksimum 3MB." :error="$errors->first('photo')">
                        <input id="s-photo" type="file" wire:model="photo"
                               accept="image/jpeg,image/png,image/webp"
                               class="tap-target w-full rounded-xl border border-hairline bg-surface px-4 py-2.5
                                      text-sm text-ink file:mr-3 file:rounded-full file:border-0 file:bg-brand-50
                                      file:px-4 file:py-1.5 file:text-sm file:font-medium file:text-brand-700" />
                    </x-ui.field>

                    <x-ui.field label="Susunan" for="s-sort" optional :error="$errors->first('sort_order')">
                        <x-ui.input id="s-sort" type="number" min="0" wire:model="sort_order"
                                    :error="$errors->has('sort_order')" />
                    </x-ui.field>
                </div>

                <x-ui.choice type="checkbox" name="is_active" value="1"
                             wire:model="is_active" :checked="$is_active"
                             label="Aktif"
                             hint="Hanya penceramah aktif boleh ditetapkan pada program baharu." />

                <div class="flex flex-wrap gap-2.5">
                    <x-ui.button type="submit" variant="primary"
                                 wire:loading.attr="disabled" wire:target="save,photo">
                        Simpan
                    </x-ui.button>
                    <x-ui.button type="button" wire:click="cancel" variant="ghost">Batal</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    @endif

    @if ($speakers->isEmpty())
        <x-ui.empty-state icon="user" title="Belum ada penceramah"
            description="Tambah penceramah supaya mereka boleh ditetapkan pada program." />
    @else
        <ul class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($speakers as $speaker)
                <li wire:key="speaker-{{ $speaker->id }}"
                    class="flex h-full flex-col rounded-card border border-hairline bg-surface p-5">
                    <div class="flex items-start gap-4">
                        @if ($speaker->photo_path)
                            <img src="{{ Storage::url($speaker->photo_path) }}" alt="{{ $speaker->name }}"
                                 class="h-14 w-14 shrink-0 rounded-2xl object-cover" />
                        @else
                            <span class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-brand-50
                                         font-display text-xl text-brand-600">
                                {{ mb_strtoupper(mb_substr($speaker->name, 0, 1)) }}
                            </span>
                        @endif
                        <div class="min-w-0">
                            <p class="font-semibold text-navy-900 text-pretty">{{ $speaker->name }}</p>
                            @if ($speaker->title)
                                <p class="mt-0.5 text-sm text-ink-muted text-pretty">{{ $speaker->title }}</p>
                            @endif
                        </div>
                    </div>

                    @if ($speaker->bio)
                        <p class="mt-4 flex-1 text-sm leading-relaxed text-ink-soft text-pretty">{{ $speaker->bio }}</p>
                    @endif

                    <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-hairline pt-4">
                        <x-ui.badge color="purple">{{ $speaker->events_count }} program</x-ui.badge>
                        <button type="button" wire:click="toggleActive({{ $speaker->id }})"
                                aria-label="Tukar status aktif">
                            <x-ui.badge :color="$speaker->is_active ? 'success' : 'grey'">
                                {{ $speaker->is_active ? 'Aktif' : 'Tidak aktif' }}
                            </x-ui.badge>
                        </button>

                        <div class="ml-auto flex gap-1">
                            <x-ui.button wire:click="edit({{ $speaker->id }})" variant="ghost" size="sm" icon="edit">
                                Ubah
                            </x-ui.button>
                            <x-ui.button wire:click="delete({{ $speaker->id }})"
                                         wire:confirm="Buang penceramah ini?"
                                         variant="ghost" size="sm" icon="trash">
                                Buang
                            </x-ui.button>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</div>
