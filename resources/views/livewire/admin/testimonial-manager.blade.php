<div>
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-ink">Testimoni</h2>
            <p class="mt-0.5 text-sm text-ink-soft">Hanya testimoni yang diluluskan dipaparkan kepada umum.</p>
        </div>
        @unless ($showForm)
            <x-ui.button wire:click="create" variant="primary" size="sm" icon="plus">Tambah Testimoni</x-ui.button>
        @endunless
    </div>

    {{-- ── Borang ─────────────────────────────────────────── --}}
    @if ($showForm)
        <x-ui.card class="mb-6">
            <h3 class="font-semibold text-ink">
                {{ $editingId ? 'Kemas Kini Testimoni' : 'Testimoni Baharu' }}
            </h3>

            <form wire:submit="save" class="mt-4 space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.field label="Nama" for="t-name" required :error="$errors->first('name')">
                        <x-ui.input id="t-name" wire:model="name" :error="$errors->has('name')" />
                    </x-ui.field>

                    <x-ui.field label="Peranan" for="t-role" optional
                                hint="Contoh: Ahli Kariah, Kota Bharu" :error="$errors->first('role_label')">
                        <x-ui.input id="t-role" wire:model="role_label" :error="$errors->has('role_label')" />
                    </x-ui.field>
                </div>

                <x-ui.field label="Testimoni" for="t-quote" required :error="$errors->first('quote')">
                    <x-ui.textarea id="t-quote" wire:model="quote" rows="3" :error="$errors->has('quote')" />
                </x-ui.field>

                <div class="grid gap-4 sm:grid-cols-4">
                    <x-ui.field label="Penilaian" for="t-rating" optional :error="$errors->first('rating')">
                        <x-ui.select id="t-rating" wire:model="rating" :error="$errors->has('rating')">
                            <option value="">Tiada</option>
                            @for ($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}">{{ $i }} / 5</option>
                            @endfor
                        </x-ui.select>
                    </x-ui.field>

                    <x-ui.field label="Program" for="t-event" optional :error="$errors->first('event_id')">
                        <x-ui.select id="t-event" wire:model="event_id" :error="$errors->has('event_id')">
                            <option value="">Tiada kaitan</option>
                            @foreach ($events as $event)
                                <option value="{{ $event->id }}">{{ \Illuminate\Support\Str::limit($event->title, 45) }}</option>
                            @endforeach
                        </x-ui.select>
                    </x-ui.field>

                    <x-ui.field label="Negeri" for="t-state" optional :error="$errors->first('state_id')">
                        <x-ui.select id="t-state" wire:model="state_id" :error="$errors->has('state_id')">
                            <option value="">Tiada</option>
                            @foreach ($states as $state)
                                <option value="{{ $state->id }}">{{ $state->name }}</option>
                            @endforeach
                        </x-ui.select>
                    </x-ui.field>

                    <x-ui.field label="Susunan" for="t-sort" optional :error="$errors->first('sort_order')">
                        <x-ui.input id="t-sort" type="number" min="0" wire:model="sort_order"
                                    :error="$errors->has('sort_order')" />
                    </x-ui.field>
                </div>

                <div class="grid gap-2.5 sm:grid-cols-2">
                    <x-ui.choice type="checkbox" name="is_approved" value="1"
                                 wire:model="is_approved" :checked="$is_approved"
                                 label="Diluluskan untuk paparan awam" />
                    <x-ui.choice type="checkbox" name="is_featured" value="1"
                                 wire:model="is_featured" :checked="$is_featured"
                                 label="Papar pada halaman utama"
                                 hint="Testimoni pilihan muncul di bahagian utama." />
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

    {{-- ── Senarai ────────────────────────────────────────── --}}
    @if ($testimonials->isEmpty())
        <x-ui.empty-state icon="chat" title="Belum ada testimoni"
            description="Tambah testimoni daripada Penggerak, masjid atau peserta." />
    @else
        <x-jelajah.admin-table :headers="['Nama', 'Testimoni', 'Penilaian', 'Program', 'Status', '']">
            @foreach ($testimonials as $testimonial)
                <tr wire:key="testimonial-{{ $testimonial->id }}" class="hover:bg-mist/40">
                    <td class="px-4 py-3">
                        <p class="font-medium text-ink">{{ $testimonial->name }}</p>
                        <p class="text-xs text-ink-muted">{{ $testimonial->role_label }}</p>
                    </td>
                    <td class="max-w-lg px-4 py-3 text-ink-soft text-pretty">
                        {{ \Illuminate\Support\Str::limit($testimonial->quote, 110) }}
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-ink-soft">
                        {{ $testimonial->rating ? $testimonial->rating.' / 5' : '—' }}
                    </td>
                    <td class="px-4 py-3 text-xs text-ink-muted">
                        {{ $testimonial->event?->title ? \Illuminate\Support\Str::limit($testimonial->event->title, 30) : '—' }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap gap-1.5">
                            <button type="button" wire:click="toggleApproved({{ $testimonial->id }})"
                                    aria-label="Tukar status kelulusan">
                                <x-ui.badge :color="$testimonial->is_approved ? 'success' : 'warning'">
                                    {{ $testimonial->is_approved ? 'Diluluskan' : 'Menunggu' }}
                                </x-ui.badge>
                            </button>
                            <button type="button" wire:click="toggleFeatured({{ $testimonial->id }})"
                                    aria-label="Tukar status pilihan">
                                <x-ui.badge :color="$testimonial->is_featured ? 'purple' : 'grey'">
                                    {{ $testimonial->is_featured ? 'Pilihan' : 'Biasa' }}
                                </x-ui.badge>
                            </button>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex justify-end gap-1">
                            <x-ui.button wire:click="edit({{ $testimonial->id }})" variant="ghost" size="sm" icon="edit">
                                Ubah
                            </x-ui.button>
                            <x-ui.button wire:click="delete({{ $testimonial->id }})"
                                         wire:confirm="Buang testimoni ini?"
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
