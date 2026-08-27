<x-ui.card>
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="font-semibold text-ink">Rakaman & Bahan</h2>
            <p class="mt-1 text-sm text-ink-soft text-pretty">
                Peserta membukanya melalui tiket mereka. Rakaman "peserta hadir sahaja"
                memberi nilai tambahan kepada mereka yang benar-benar datang.
            </p>
        </div>
        @unless ($showForm)
            <x-ui.button wire:click="create" variant="outline" size="sm" icon="plus">Tambah Rakaman</x-ui.button>
        @endunless
    </div>

    @if ($showForm)
        <form wire:submit="save" class="mt-5 space-y-4 rounded-xl border border-clay-200 bg-clay-50/50 p-4">
            <x-ui.field label="Tajuk" for="r-title" required :error="$errors->first('title')">
                <x-ui.input id="r-title" wire:model="title" :error="$errors->has('title')"
                            placeholder="Contoh: Sesi Penuh — Bidayatul Hidayah" />
            </x-ui.field>

            <x-ui.field label="Penerangan" for="r-desc" optional :error="$errors->first('description')">
                <x-ui.textarea id="r-desc" wire:model="description" rows="2" :error="$errors->has('description')" />
            </x-ui.field>

            <div class="grid gap-4 sm:grid-cols-3">
                <x-ui.field label="Jenis" for="r-type" required :error="$errors->first('type')">
                    <x-ui.select id="r-type" wire:model="type" :error="$errors->has('type')">
                        @foreach ($types as $option)
                            <option value="{{ $option->value }}">{{ $option->label() }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Sumber" for="r-provider" required :error="$errors->first('provider')">
                    <x-ui.select id="r-provider" wire:model="provider" :error="$errors->has('provider')">
                        <option value="youtube">YouTube</option>
                        <option value="vimeo">Vimeo</option>
                        <option value="pautan">Pautan luar</option>
                        <option value="fail">Fail</option>
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Tempoh (minit)" for="r-dur" optional :error="$errors->first('duration_minutes')">
                    <x-ui.input id="r-dur" type="number" min="1" wire:model="duration_minutes"
                                :error="$errors->has('duration_minutes')" />
                </x-ui.field>
            </div>

            <x-ui.field label="Pautan" for="r-url" required
                        hint="YouTube dan Vimeo dibenamkan terus dalam halaman."
                        :error="$errors->first('url')">
                <x-ui.input id="r-url" type="url" wire:model="url" :error="$errors->has('url')"
                            placeholder="https://youtu.be/..." />
            </x-ui.field>

            <fieldset>
                <legend class="mb-2.5 block text-sm font-medium text-ink">
                    Siapa boleh tonton? <span class="text-danger" aria-hidden="true">*</span>
                </legend>
                <div class="grid gap-2.5">
                    @foreach ($visibilities as $option)
                        <x-ui.choice name="visibility" :value="$option->value"
                                     :label="$option->label()" :hint="$option->description()"
                                     wire:model="visibility" :checked="$visibility === $option->value" />
                    @endforeach
                </div>
            </fieldset>

            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.field label="Dibuka pada" for="r-from" optional
                            hint="Kosongkan untuk terus tersedia."
                            :error="$errors->first('available_from')">
                    <x-ui.input id="r-from" type="datetime-local" wire:model="available_from"
                                :error="$errors->has('available_from')" />
                </x-ui.field>

                <x-ui.field label="Susunan" for="r-sort" optional :error="$errors->first('sort_order')">
                    <x-ui.input id="r-sort" type="number" min="0" wire:model="sort_order"
                                :error="$errors->has('sort_order')" />
                </x-ui.field>
            </div>

            <x-ui.choice type="checkbox" name="is_published" value="1"
                         wire:model="is_published" :checked="$is_published"
                         label="Terbitkan sekarang"
                         hint="Rakaman tidak diterbitkan tidak kelihatan kepada sesiapa." />

            <div class="flex flex-wrap gap-2.5">
                <x-ui.button type="submit" variant="primary" size="sm">Simpan Rakaman</x-ui.button>
                <x-ui.button type="button" wire:click="cancel" variant="ghost" size="sm">Batal</x-ui.button>
            </div>
        </form>
    @endif

    @if ($recordings->isEmpty())
        <p class="mt-4 text-sm text-ink-muted">Belum ada rakaman untuk program ini.</p>
    @else
        <ul class="mt-5 divide-y divide-hairline">
            @foreach ($recordings as $recording)
                <li wire:key="rec-{{ $recording->id }}" class="flex flex-wrap items-start justify-between gap-3 py-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <x-ui.icon :name="$recording->type->icon()" class="h-4 w-4 text-ink-muted" />
                            <span class="font-medium text-ink">{{ $recording->title }}</span>
                            <x-ui.badge :color="$recording->visibility->color()">
                                {{ $recording->visibility->label() }}
                            </x-ui.badge>
                            <button type="button" wire:click="togglePublished({{ $recording->id }})"
                                    aria-label="Tukar status terbitan">
                                <x-ui.badge :color="$recording->is_published ? 'success' : 'warning'">
                                    {{ $recording->is_published ? 'Diterbitkan' : 'Draf' }}
                                </x-ui.badge>
                            </button>
                        </div>

                        <p class="mt-1 text-xs text-ink-muted">
                            {{ $recording->durationLabel() ?? 'Tempoh tidak dinyatakan' }}
                            &middot; {{ $recording->views_count }} tontonan
                            @if ($recording->available_from)
                                &middot; dibuka {{ $recording->available_from->translatedFormat('j M Y, g:ia') }}
                            @endif
                        </p>
                    </div>

                    <div class="flex gap-1">
                        <x-ui.button wire:click="edit({{ $recording->id }})" variant="ghost" size="sm" icon="edit">
                            Ubah
                        </x-ui.button>
                        <x-ui.button wire:click="delete({{ $recording->id }})"
                                     wire:confirm="Buang rakaman ini?"
                                     variant="ghost" size="sm" icon="trash">Buang</x-ui.button>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</x-ui.card>
