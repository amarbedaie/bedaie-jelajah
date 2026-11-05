<div>
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <p class="max-w-2xl text-ink-soft text-pretty">
            Semua sijil bersaiz A4. Pilih gaya reka bentuk & orientasi untuk setiap templat — atau tetapkan
            gaya berbeza bagi setiap <a href="{{ route('admin.kategori') }}" class="font-medium underline">kategori program</a>
            (cth. Kelas Qadha Solat vs Kelas Darah Wanita), atau terus pada halaman ubah program.
        </p>
        @unless ($showForm)
            <x-ui.button wire:click="create" variant="primary" size="sm" icon="plus">Tambah Templat</x-ui.button>
        @endunless
    </div>

    @if ($showForm)
        <x-ui.card class="mb-6">
            <h3 class="font-semibold text-ink">{{ $editingId ? 'Kemas Kini Templat Sijil' : 'Templat Sijil Baharu' }}</h3>

            <form wire:submit="save" class="mt-4 space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.field label="Nama templat" for="t-name" required :error="$errors->first('name')">
                        <x-ui.input id="t-name" wire:model="name" :error="$errors->has('name')"
                                    placeholder="Contoh: Sijil Kelas Darah Wanita" />
                    </x-ui.field>

                    <x-ui.field label="Jenis sijil" for="t-type" required :error="$errors->first('type')">
                        <x-ui.select id="t-type" wire:model="type">
                            @foreach ($types as $t)
                                <option value="{{ $t->value }}">{{ $t->label() }}</option>
                            @endforeach
                        </x-ui.select>
                    </x-ui.field>
                </div>

                <fieldset>
                    <legend class="mb-2.5 block text-sm font-medium text-ink">Reka bentuk & susunan</legend>
                    <div class="grid gap-2.5 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($designs as $d)
                            <button type="button" wire:click="$set('design', '{{ $d->value }}')"
                                    aria-pressed="{{ $design === $d->value ? 'true' : 'false' }}"
                                    class="rounded-xl border p-3 text-left transition
                                           {{ $design === $d->value
                                              ? 'border-brand-400 bg-brand-50 ring-1 ring-brand-400'
                                              : 'border-hairline bg-surface hover:border-brand-300' }}">
                                <span class="block font-medium text-ink">{{ $d->label() }}</span>
                                <span class="mt-1 block text-[0.8125rem] text-ink-soft">{{ $d->description() }}</span>
                            </button>
                        @endforeach
                    </div>
                </fieldset>

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.field label="Orientasi (A4)" for="t-orient" required :error="$errors->first('orientation')">
                        <x-ui.select id="t-orient" wire:model="orientation">
                            @foreach ($orientations as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </x-ui.select>
                    </x-ui.field>

                    <x-ui.field label="Warna utama" for="t-accent" required :error="$errors->first('accent_color')">
                        <div class="flex items-center gap-2">
                            <input type="color" wire:model.live="accent_color" aria-label="Pilih warna"
                                   class="h-11 w-14 cursor-pointer rounded-lg border border-hairline bg-surface">
                            <x-ui.input id="t-accent" wire:model.live="accent_color" :error="$errors->has('accent_color')" />
                        </div>
                    </x-ui.field>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.field label="Teks pembuka" for="t-intro" optional hint="Di atas nama penerima."
                                :error="$errors->first('intro_text')">
                        <x-ui.input id="t-intro" wire:model="intro_text" placeholder="Dengan ini disahkan bahawa" />
                    </x-ui.field>
                    <x-ui.field label="Teks penutup" for="t-closing" optional hint="Di bawah nama penerima."
                                :error="$errors->first('closing_text')">
                        <x-ui.input id="t-closing" wire:model="closing_text" />
                    </x-ui.field>
                    <x-ui.field label="Nama penandatangan" for="t-sign" optional :error="$errors->first('signature_name')">
                        <x-ui.input id="t-sign" wire:model="signature_name" />
                    </x-ui.field>
                    <x-ui.field label="Jawatan penandatangan" for="t-sign-title" optional
                                :error="$errors->first('signature_title')">
                        <x-ui.input id="t-sign-title" wire:model="signature_title" />
                    </x-ui.field>
                </div>

                <div class="grid gap-2.5 sm:grid-cols-2">
                    <x-ui.choice type="checkbox" name="is_default" value="1" wire:model="is_default" :checked="$is_default"
                                 label="Templat utama bagi jenis ini"
                                 hint="Digunakan apabila sijil jenis ini dikeluarkan." />
                    <x-ui.choice type="checkbox" name="is_active" value="1" wire:model="is_active" :checked="$is_active"
                                 label="Aktif" />
                </div>

                <div class="flex flex-wrap gap-2.5">
                    <x-ui.button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="save">
                        Simpan
                    </x-ui.button>
                    @if ($editingId)
                        <x-ui.button variant="outline" icon="eye" target="_blank"
                                     :href="route('admin.sijil.pratonton', ['template' => $editingId, 'design' => $design, 'orientation' => $orientation])">
                            Pratonton PDF
                        </x-ui.button>
                    @endif
                    <x-ui.button type="button" wire:click="cancel" variant="ghost">Batal</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    @endif

    <x-jelajah.admin-table caption="Templat sijil"
        :headers="['Templat', 'Jenis', 'Reka bentuk', 'Orientasi', 'Status', '']">
        @foreach ($templates as $t)
            <tr wire:key="tpl-{{ $t->id }}" class="hover:bg-mist/40">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-2.5">
                        <span class="h-4 w-4 shrink-0 rounded" style="background: {{ $t->accent_color ?: '#7A3BB8' }}"></span>
                        <span class="font-medium text-ink">{{ $t->name }}</span>
                        @if ($t->is_default)
                            <x-ui.badge color="grey">Utama</x-ui.badge>
                        @endif
                    </div>
                </td>
                <td class="px-4 py-3 text-ink-soft">{{ $t->type->label() }}</td>
                <td class="px-4 py-3 text-ink-soft">{{ \App\Enums\CertificateDesign::tryFrom((string) $t->design)?->label() ?? 'Klasik' }}</td>
                <td class="px-4 py-3 text-ink-soft">{{ $t->orientation === 'portrait' ? 'Potret' : 'Landskap' }}</td>
                <td class="px-4 py-3">
                    <button type="button" wire:click="toggleActive({{ $t->id }})" aria-label="Tukar status aktif">
                        <x-ui.badge :color="$t->is_active ? 'success' : 'grey'">
                            {{ $t->is_active ? 'Aktif' : 'Tidak aktif' }}
                        </x-ui.badge>
                    </button>
                </td>
                <td class="px-4 py-3 text-right">
                    <div class="flex justify-end gap-1">
                        <x-ui.button variant="ghost" size="sm" icon="eye" target="_blank"
                                     :href="route('admin.sijil.pratonton', ['template' => $t->id])">
                            Pratonton
                        </x-ui.button>
                        <x-ui.button wire:click="edit({{ $t->id }})" variant="ghost" size="sm" icon="edit">Ubah</x-ui.button>
                    </div>
                </td>
            </tr>
        @endforeach
    </x-jelajah.admin-table>
</div>
