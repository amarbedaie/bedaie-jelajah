<div class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h2 class="font-display text-2xl text-ink">Katalog Pakej</h2>
            <p class="mt-1.5 text-sm text-ink-soft text-pretty">
                Cipta sekali, guna untuk mana-mana program. Staf sahaja.
            </p>
        </div>
        @unless ($showForm)
            <x-ui.button type="button" wire:click="create" variant="primary" icon="plus">
                Pakej Baharu
            </x-ui.button>
        @endunless
    </div>

    @if ($showForm)
        <x-ui.card class="sm:p-7">
            <h3 class="font-display text-xl text-ink">
                {{ $editingId ? 'Kemas Kini Pakej' : 'Pakej Baharu' }}
            </h3>

            <form wire:submit="save" class="mt-6 space-y-5">
                <div class="grid gap-5 sm:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
                    <x-ui.field label="Nama pakej" for="pkg-name" required :error="$errors->first('name')">
                        <x-ui.input id="pkg-name" wire:model="name" :error="$errors->has('name')"
                                    placeholder="Pakej Tadabbur Lengkap" />
                    </x-ui.field>

                    <x-ui.field label="Harga (RM)" for="pkg-price" required :error="$errors->first('price')">
                        <x-ui.input id="pkg-price" wire:model="price" type="number" step="0.01" min="0"
                                    inputmode="decimal" :error="$errors->has('price')" />
                    </x-ui.field>
                </div>

                <x-ui.field label="Tagline" for="pkg-tagline" optional
                            hint="Satu baris pendek yang staf boleh sebut."
                            :error="$errors->first('tagline')">
                    <x-ui.input id="pkg-tagline" wire:model="tagline" :error="$errors->has('tagline')"
                                placeholder="Empat kitab tadabbur dalam satu kotak" />
                </x-ui.field>

                <x-ui.field label="Kandungan" for="pkg-items" optional
                            hint="Satu barang satu baris."
                            :error="$errors->first('items')">
                    <x-ui.textarea id="pkg-items" wire:model="items" rows="5"
                                   :error="$errors->has('items')"
                                   placeholder="Tadabbur Surah Al-Fatihah&#10;Tadabbur Surah Al-Mulk&#10;Tadabbur Surah Yasin" />
                </x-ui.field>

                <x-ui.field label="Ayat jualan" for="pkg-pitch" optional
                            hint="Apa yang staf sebut di kaunter."
                            :error="$errors->first('pitch')">
                    <x-ui.textarea id="pkg-pitch" wire:model="pitch" rows="3"
                                   :error="$errors->has('pitch')"
                                   placeholder="Sesuai untuk keluarga yang baru mula belajar tadabbur." />
                </x-ui.field>

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-ui.field label="Susunan" for="pkg-order" optional
                                hint="Nombor kecil muncul dahulu.">
                        <x-ui.input id="pkg-order" wire:model="sort_order" type="number" min="0" inputmode="numeric" />
                    </x-ui.field>

                    <label class="flex items-center gap-3 self-end pb-2.5">
                        <input type="checkbox" wire:model="is_active"
                               class="h-4 w-4 shrink-0 rounded border-control-line text-brand-600 focus:ring-brand-500">
                        <span class="text-sm text-ink">Aktif dalam katalog</span>
                    </label>
                </div>

                <div class="flex flex-wrap gap-3 border-t border-hairline pt-5">
                    <x-ui.button type="submit" variant="primary">Simpan Pakej</x-ui.button>
                    <x-ui.button type="button" wire:click="cancel" variant="ghost">Batal</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    @endif

    @if ($packages->isEmpty())
        <x-ui.empty-state icon="box" title="Katalog masih kosong"
                          description="Cipta pakej pertama anda — ia akan muncul pada setiap program.">
            <x-ui.button type="button" wire:click="create" variant="primary" icon="plus">
                Cipta Pakej
            </x-ui.button>
        </x-ui.empty-state>
    @else
        <div class="grid gap-4 lg:grid-cols-2">
            @foreach ($packages as $p)
                <x-ui.card wire:key="pkg-{{ $p->id }}"
                           class="{{ $p->is_active ? '' : 'opacity-60' }}">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <h3 class="font-display text-xl leading-snug text-ink text-pretty">{{ $p->name }}</h3>
                            @if ($p->tagline)
                                <p class="mt-1 text-sm text-ink-soft text-pretty">{{ $p->tagline }}</p>
                            @endif
                        </div>
                        <span class="shrink-0 font-display text-2xl text-ink">{{ $p->priceLabel() }}</span>
                    </div>

                    @if ($p->itemLines())
                        <ul class="mt-4 space-y-1.5 border-t border-hairline pt-4">
                            @foreach ($p->itemLines() as $item)
                                <li class="flex gap-2.5 text-sm text-ink-soft">
                                    <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-brand-400"></span>
                                    <span class="text-pretty">{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @if ($p->pitch)
                        <p class="mt-4 rounded-card bg-mist p-3.5 text-sm leading-relaxed text-ink-soft text-pretty">
                            &ldquo;{{ $p->pitch }}&rdquo;
                        </p>
                    @endif

                    <div class="mt-5 flex flex-wrap items-center gap-2.5 border-t border-hairline pt-4">
                        <x-ui.button type="button" wire:click="edit({{ $p->id }})"
                                     variant="outline" size="sm" icon="edit">Sunting</x-ui.button>
                        <x-ui.button type="button" wire:click="toggleActive({{ $p->id }})"
                                     variant="ghost" size="sm">
                            {{ $p->is_active ? 'Sembunyikan' : 'Aktifkan' }}
                        </x-ui.button>
                        <span class="ml-auto text-xs text-ink-muted">
                            {{ $p->events_count }} program
                        </span>
                    </div>
                </x-ui.card>
            @endforeach
        </div>
    @endif
</div>
