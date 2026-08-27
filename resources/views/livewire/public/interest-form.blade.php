<div class="mx-auto max-w-xl">
    @if ($submitted)
        <x-ui.card class="text-center sm:p-8">
            <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-success-soft">
                <x-ui.icon name="check-circle" class="h-7 w-7 text-success" />
            </div>
            <h2 class="mt-5 font-display text-2xl text-ink">Permintaan Anda Direkodkan</h2>
            <p class="mt-3 text-ink-soft text-pretty">
                Terima kasih. Kawasan yang paling banyak diminta akan diutamakan dalam
                perancangan jelajah kami yang seterusnya.
            </p>

            <div class="mt-7 rounded-xl bg-clay-50 p-5 text-left">
                <p class="text-sm font-medium text-ink">Mahu lebih pantas?</p>
                <p class="mt-1.5 text-sm text-ink-soft text-pretty">
                    Jika anda mempunyai lokasi tertentu dalam fikiran — masjid, surau, sekolah
                    atau dewan — hantar permohonan penuh supaya kami boleh terus berbincang.
                </p>
                <x-ui.button :href="route('jemput')" variant="primary" size="sm" class="mt-4" icon="heart">
                    Jemput BeDaie Sekarang
                </x-ui.button>
            </div>

            <div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
                <x-ui.button :href="route('peta')" variant="outline">Lihat Peta Jelajah</x-ui.button>
                <x-ui.button :href="route('home')" variant="ghost">Kembali ke Utama</x-ui.button>
            </div>
        </x-ui.card>
    @else
        <x-ui.card class="sm:p-8">
            <div class="space-y-5">
                <x-ui.field label="Nama" for="i-name" required :error="$errors->first('name')">
                    <x-ui.input id="i-name" wire:model.blur="name" :error="$errors->has('name')"
                                autocomplete="name" />
                </x-ui.field>

                <x-ui.field label="Nombor WhatsApp" for="i-phone" required
                            hint="Kami hubungi anda jika jelajah dirancang ke kawasan anda."
                            :error="$errors->first('phone')">
                    <x-ui.input id="i-phone" type="tel" wire:model.blur="phone" :error="$errors->has('phone')"
                                icon="whatsapp" inputmode="tel" autocomplete="tel" placeholder="012-345 6789" />
                </x-ui.field>

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-ui.field label="Negeri" for="i-state" required :error="$errors->first('state_id')">
                        <x-ui.select id="i-state" wire:model.live="state_id" :error="$errors->has('state_id')">
                            <option value="">Pilih negeri</option>
                            @foreach ($states as $state)
                                <option value="{{ $state->id }}">{{ $state->name }}</option>
                            @endforeach
                        </x-ui.select>
                    </x-ui.field>

                    <x-ui.field label="Daerah" for="i-district" optional :error="$errors->first('district_id')">
                        <x-ui.select id="i-district" wire:model="district_id"
                                     :error="$errors->has('district_id')" :disabled="$districts->isEmpty()">
                            <option value="">{{ $districts->isEmpty() ? 'Pilih negeri dahulu' : 'Pilih daerah' }}</option>
                            @foreach ($districts as $district)
                                <option value="{{ $district->id }}">{{ $district->name }}</option>
                            @endforeach
                        </x-ui.select>
                    </x-ui.field>
                </div>

                <x-ui.field label="Poskod" for="i-postcode" optional
                            hint="Membantu kami mengenal pasti kawasan dengan lebih tepat."
                            :error="$errors->first('postcode')">
                    <x-ui.input id="i-postcode" wire:model.blur="postcode" :error="$errors->has('postcode')"
                                inputmode="numeric" maxlength="5" placeholder="43650" />
                </x-ui.field>

                <x-ui.field label="Program yang diminati" for="i-category" optional
                            :error="$errors->first('event_category_id')">
                    <x-ui.select id="i-category" wire:model="event_category_id"
                                 :error="$errors->has('event_category_id')">
                        <option value="">Mana-mana program</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.button type="button" wire:click="submit" variant="primary" size="lg" block
                             wire:loading.attr="disabled" wire:target="submit">
                    <span wire:loading.remove wire:target="submit">Hantar Permintaan</span>
                    <span wire:loading wire:target="submit">Menghantar…</span>
                </x-ui.button>

                <p class="text-center text-xs text-ink-muted text-pretty">
                    Kami hanya memaparkan jumlah agregat kepada umum. Maklumat peribadi anda
                    tidak didedahkan.
                </p>
            </div>
        </x-ui.card>
    @endif
</div>
