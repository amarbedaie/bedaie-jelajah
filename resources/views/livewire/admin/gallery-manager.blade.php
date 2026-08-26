<div class="space-y-10">
    {{-- ── Muat naik ──────────────────────────────────────── --}}
    <x-ui.card>
        <h2 class="font-semibold text-navy-900">Muat Naik Gambar Program</h2>
        <p class="mt-1.5 text-sm text-ink-soft text-pretty">
            Gambar yang dimuat naik oleh admin terus diluluskan dan dipaparkan pada
            halaman program serta Galeri Impak.
        </p>

        <form wire:submit="upload" class="mt-5 space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.field label="Program" for="g-event" required :error="$errors->first('eventId')">
                    <x-ui.select id="g-event" wire:model="eventId" :error="$errors->has('eventId')">
                        <option value="">Pilih program</option>
                        @foreach ($events as $event)
                            <option value="{{ $event->id }}">
                                {{ \Illuminate\Support\Str::limit($event->title, 55) }}
                                — {{ $event->starts_at->translatedFormat('j M Y') }}
                            </option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Kapsyen" for="g-caption" optional
                            hint="Digunakan untuk semua gambar dalam muat naik ini."
                            :error="$errors->first('caption')">
                    <x-ui.input id="g-caption" wire:model="caption" :error="$errors->has('caption')"
                                placeholder="Contoh: Sesi soal jawab bersama peserta" />
                </x-ui.field>
            </div>

            <x-ui.field label="Gambar" for="g-photos" required
                        hint="JPG, PNG atau WEBP. Maksimum 5MB setiap satu, 20 gambar sekali muat naik."
                        :error="$errors->first('photos') ?: $errors->first('photos.*')">
                <input id="g-photos" type="file" wire:model="photos" multiple
                       accept="image/jpeg,image/png,image/webp"
                       class="tap-target w-full rounded-xl border border-hairline bg-surface px-4 py-2.5
                              text-sm text-ink file:mr-3 file:rounded-full file:border-0 file:bg-brand-50
                              file:px-4 file:py-1.5 file:text-sm file:font-medium file:text-brand-700
                              hover:file:bg-brand-100" />
            </x-ui.field>

            <div wire:loading wire:target="photos" class="text-sm text-ink-muted">Memuat naik gambar…</div>

            @if ($photos)
                <div class="grid grid-cols-3 gap-3 sm:grid-cols-6">
                    @foreach ($photos as $photo)
                        @if (method_exists($photo, 'temporaryUrl'))
                            <img src="{{ $photo->temporaryUrl() }}" alt=""
                                 class="aspect-square w-full rounded-xl object-cover ring-1 ring-hairline" />
                        @endif
                    @endforeach
                </div>
            @endif

            <x-ui.button type="submit" variant="primary" icon="image"
                         wire:loading.attr="disabled" wire:target="upload,photos">
                <span wire:loading.remove wire:target="upload">Muat Naik</span>
                <span wire:loading wire:target="upload">Menyimpan…</span>
            </x-ui.button>
        </form>
    </x-ui.card>

    {{-- ── Menunggu kelulusan ─────────────────────────────── --}}
    <section>
        <h2 class="mb-3 text-lg font-semibold text-navy-900">
            Menunggu Kelulusan
            @if ($pending->isNotEmpty())
                <x-ui.badge color="warning" class="ml-1">{{ $pending->count() }}</x-ui.badge>
            @endif
        </h2>

        @if ($pending->isEmpty())
            <x-ui.empty-state compact icon="image" title="Tiada gambar menunggu kelulusan"
                description="Gambar yang dihantar dari sumber luar akan muncul di sini untuk disemak." />
        @else
            <ul class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($pending as $photo)
                    <li wire:key="pending-{{ $photo->id }}"
                        class="overflow-hidden rounded-[--radius-card] border border-warning/30 bg-surface">
                        <img src="{{ Storage::url($photo->image_path) }}" alt="{{ $photo->caption }}"
                             class="aspect-square w-full object-cover" />
                        <div class="p-3">
                            <p class="truncate text-xs text-ink-muted">{{ $photo->event?->title }}</p>
                            <div class="mt-2.5 flex gap-1.5">
                                <x-ui.button wire:click="approve({{ $photo->id }})" variant="success" size="sm" icon="check">
                                    Luluskan
                                </x-ui.button>
                                <x-ui.button wire:click="delete({{ $photo->id }})"
                                             wire:confirm="Buang gambar ini secara kekal?"
                                             variant="danger-soft" size="sm" icon="trash">
                                    Buang
                                </x-ui.button>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    {{-- ── Diterbitkan ────────────────────────────────────── --}}
    <section>
        <div class="mb-3 flex items-end justify-between gap-3">
            <h2 class="text-lg font-semibold text-navy-900">Galeri Diterbitkan</h2>
            <a href="{{ route('galeri') }}" target="_blank"
               class="text-sm font-medium text-brand-600 hover:underline">Lihat halaman awam</a>
        </div>

        @if ($approved->isEmpty())
            <x-ui.empty-state compact icon="image" title="Galeri masih kosong"
                description="Muat naik gambar program di atas untuk mengisi Galeri Impak." />
        @else
            <ul class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($approved as $photo)
                    <li wire:key="approved-{{ $photo->id }}"
                        class="overflow-hidden rounded-[--radius-card] border border-hairline bg-surface">
                        <img src="{{ Storage::url($photo->image_path) }}" alt="{{ $photo->caption }}"
                             loading="lazy" class="aspect-square w-full object-cover" />
                        <div class="p-3">
                            <p class="truncate text-xs text-ink-muted">{{ $photo->event?->title }}</p>

                            <label class="sr-only" for="cap-{{ $photo->id }}">Kapsyen gambar</label>
                            <input id="cap-{{ $photo->id }}" type="text" value="{{ $photo->caption }}"
                                   wire:change="updateCaption({{ $photo->id }}, $event.target.value)"
                                   placeholder="Tambah kapsyen…"
                                   class="mt-2 w-full rounded-lg border border-hairline bg-surface px-2.5 py-1.5
                                          text-xs text-ink placeholder:text-ink-muted focus:border-brand-400
                                          focus:outline-none focus:ring-2 focus:ring-brand-500/15" />

                            <div class="mt-2.5 flex gap-1.5">
                                <x-ui.button wire:click="unapprove({{ $photo->id }})" variant="ghost" size="sm" icon="eye">
                                    Tarik
                                </x-ui.button>
                                <x-ui.button wire:click="delete({{ $photo->id }})"
                                             wire:confirm="Buang gambar ini secara kekal?"
                                             variant="danger-soft" size="sm" icon="trash">
                                    Buang
                                </x-ui.button>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="mt-6">{{ $approved->links() }}</div>
        @endif
    </section>
</div>
