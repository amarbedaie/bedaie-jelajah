<div>
    {{-- ── Bar tindakan ───────────────────────────────────── --}}
    <div class="flex flex-wrap gap-2.5">
        <x-ui.button wire:click="$toggle('open')" variant="navy" size="sm" icon="edit">
            {{ $open ? 'Tutup Editor' : 'Sunting Program' }}
        </x-ui.button>

        <x-ui.button wire:click="regeneratePoster" variant="outline" size="sm" icon="image"
                     wire:loading.attr="disabled" wire:target="regeneratePoster">
            <span wire:loading.remove wire:target="regeneratePoster">Jana Semula Poster</span>
            <span wire:loading wire:target="regeneratePoster">Menjana…</span>
        </x-ui.button>

        @if ($event->status !== \App\Enums\EventStatus::Selesai)
            <x-ui.button wire:click="complete"
                         wire:confirm="Tutup program ini? Sijil akan dilepaskan kepada peserta yang hadir."
                         variant="success" size="sm" icon="check-circle">
                Tutup Program & Lepaskan Sijil
            </x-ui.button>

            <x-ui.button wire:click="postpone"
                         wire:confirm="Tandakan program ini sebagai ditangguhkan?"
                         variant="outline" size="sm" icon="clock">
                Tangguh
            </x-ui.button>

            <x-ui.button wire:click="cancelEvent"
                         wire:confirm="Batalkan program ini? Peserta akan dimaklumkan."
                         variant="danger-soft" size="sm" icon="x-circle">
                Batalkan
            </x-ui.button>
        @endif
    </div>

    {{-- ── Editor ─────────────────────────────────────────── --}}
    @if ($open)
        <x-ui.card class="mt-5">
            <form wire:submit="save" class="space-y-8">
                {{-- Maklumat teras --}}
                <section class="space-y-4">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-ink-muted">Maklumat Program</h3>

                    <x-ui.field label="Tajuk" for="e-title" required :error="$errors->first('title')">
                        <x-ui.input id="e-title" wire:model="title" :error="$errors->has('title')" />
                    </x-ui.field>

                    <x-ui.field label="Tema" for="e-theme" optional :error="$errors->first('theme')">
                        <x-ui.input id="e-theme" wire:model="theme" :error="$errors->has('theme')" />
                    </x-ui.field>

                    <x-ui.field label="Penerangan" for="e-desc" optional :error="$errors->first('description')">
                        <x-ui.textarea id="e-desc" wire:model="description" rows="4"
                                       :error="$errors->has('description')" />
                    </x-ui.field>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <x-ui.field label="Mula" for="e-start" required :error="$errors->first('starts_at')">
                            <x-ui.input id="e-start" type="datetime-local" wire:model="starts_at"
                                        :error="$errors->has('starts_at')" />
                        </x-ui.field>
                        <x-ui.field label="Tamat" for="e-end" optional :error="$errors->first('ends_at')">
                            <x-ui.input id="e-end" type="datetime-local" wire:model="ends_at"
                                        :error="$errors->has('ends_at')" />
                        </x-ui.field>
                        <x-ui.field label="Pendaftaran dibuka" for="e-doors" optional
                                    :error="$errors->first('doors_open_at')">
                            <x-ui.input id="e-doors" type="datetime-local" wire:model="doors_open_at"
                                        :error="$errors->has('doors_open_at')" />
                        </x-ui.field>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-4">
                        <x-ui.field label="Kategori" for="e-cat" required :error="$errors->first('event_category_id')">
                            <x-ui.select id="e-cat" wire:model="event_category_id"
                                         :error="$errors->has('event_category_id')">
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </x-ui.select>
                        </x-ui.field>

                        <x-ui.field label="Penceramah" for="e-speaker" optional :error="$errors->first('speaker_id')">
                            <x-ui.select id="e-speaker" wire:model="speaker_id" :error="$errors->has('speaker_id')">
                                <option value="">Belum ditetapkan</option>
                                @foreach ($speakers as $speaker)
                                    <option value="{{ $speaker->id }}">{{ $speaker->name }}</option>
                                @endforeach
                            </x-ui.select>
                        </x-ui.field>

                        <x-ui.field label="Sasaran" for="e-aud" required :error="$errors->first('target_audience')">
                            <x-ui.select id="e-aud" wire:model="target_audience"
                                         :error="$errors->has('target_audience')">
                                @foreach ($audiences as $audience)
                                    <option value="{{ $audience->value }}">{{ $audience->label() }}</option>
                                @endforeach
                            </x-ui.select>
                        </x-ui.field>

                        <x-ui.field label="Jam pembelajaran" for="e-hours" optional
                                    :error="$errors->first('learning_hours')">
                            <x-ui.input id="e-hours" type="number" step="0.5" min="0"
                                        wire:model="learning_hours" :error="$errors->has('learning_hours')" />
                        </x-ui.field>
                    </div>

                    <x-ui.field label="Poster" for="e-poster" optional
                                hint="JPG, PNG atau WEBP. Maksimum 5MB. Nisbah 4:5 paling sesuai."
                                :error="$errors->first('poster')">
                        <input id="e-poster" type="file" wire:model="poster"
                               accept="image/jpeg,image/png,image/webp"
                               class="tap-target w-full rounded-xl border border-hairline bg-surface px-4 py-2.5
                                      text-sm text-ink file:mr-3 file:rounded-full file:border-0 file:bg-brand-50
                                      file:px-4 file:py-1.5 file:text-sm file:font-medium file:text-brand-700" />
                    </x-ui.field>

                    @if ($poster && method_exists($poster, 'temporaryUrl'))
                        <img src="{{ $poster->temporaryUrl() }}" alt="Pratonton poster"
                             class="h-40 w-auto rounded-xl ring-1 ring-hairline" />
                    @elseif ($event->posterUrl())
                        <img src="{{ $event->posterUrl() }}" alt="Poster semasa"
                             class="h-40 w-auto rounded-xl ring-1 ring-hairline" />
                    @endif
                </section>

                {{-- Lokasi --}}
                <section class="space-y-4 border-t border-hairline pt-6">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-ink-muted">Lokasi</h3>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-ui.field label="Nama lokasi" for="e-venue" required :error="$errors->first('venue_name')">
                            <x-ui.input id="e-venue" wire:model="venue_name" :error="$errors->has('venue_name')" />
                        </x-ui.field>

                        <x-ui.field label="Pautan Google Maps" for="e-maps" optional
                                    :error="$errors->first('google_maps_url')">
                            <x-ui.input id="e-maps" type="url" wire:model="google_maps_url"
                                        :error="$errors->has('google_maps_url')" />
                        </x-ui.field>
                    </div>

                    <x-ui.field label="Alamat" for="e-addr" optional :error="$errors->first('venue_address')">
                        <x-ui.textarea id="e-addr" wire:model="venue_address" rows="2"
                                       :error="$errors->has('venue_address')" />
                    </x-ui.field>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-ui.field label="Negeri" for="e-state" required :error="$errors->first('state_id')">
                            <x-ui.select id="e-state" wire:model.live="state_id" :error="$errors->has('state_id')">
                                @foreach ($states as $state)
                                    <option value="{{ $state->id }}">{{ $state->name }}</option>
                                @endforeach
                            </x-ui.select>
                        </x-ui.field>

                        <x-ui.field label="Daerah" for="e-district" optional :error="$errors->first('district_id')">
                            <x-ui.select id="e-district" wire:model="district_id"
                                         :error="$errors->has('district_id')" :disabled="$districts->isEmpty()">
                                <option value="">Pilih daerah</option>
                                @foreach ($districts as $district)
                                    <option value="{{ $district->id }}">{{ $district->name }}</option>
                                @endforeach
                            </x-ui.select>
                        </x-ui.field>
                    </div>

                    <x-ui.field label="Maklumat parkir & arahan" for="e-parking" optional
                                :error="$errors->first('parking_info')">
                        <x-ui.textarea id="e-parking" wire:model="parking_info" rows="2"
                                       :error="$errors->has('parking_info')" />
                    </x-ui.field>
                </section>

                {{-- Kapasiti & harga --}}
                <section class="space-y-4 border-t border-hairline pt-6">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-ink-muted">Kapasiti & Harga</h3>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <x-ui.field label="Kapasiti" for="e-cap" required
                                    :hint="$event->seatsTaken().' tempat telah diambil'"
                                    :error="$errors->first('capacity')">
                            <x-ui.input id="e-cap" type="number" min="1" wire:model="capacity"
                                        :error="$errors->has('capacity')" />
                        </x-ui.field>

                        <x-ui.field label="Mod harga" for="e-pricing" required :error="$errors->first('pricing_mode')">
                            <x-ui.select id="e-pricing" wire:model.live="pricing_mode"
                                         :error="$errors->has('pricing_mode')">
                                @foreach ($pricingModes as $mode)
                                    <option value="{{ $mode->value }}">{{ $mode->label() }}</option>
                                @endforeach
                            </x-ui.select>
                        </x-ui.field>

                        @if ($pricing_mode === 'berbayar')
                            <x-ui.field label="Harga (RM)" for="e-price" required :error="$errors->first('price')">
                                <x-ui.input id="e-price" type="number" step="0.01" min="0"
                                            wire:model="price" :error="$errors->has('price')" />
                            </x-ui.field>
                        @elseif ($pricing_mode === 'jemputan')
                            <x-ui.field label="Kod jemputan" for="e-invite" optional
                                        :error="$errors->first('invite_code')">
                                <x-ui.input id="e-invite" wire:model="invite_code"
                                            :error="$errors->has('invite_code')" class="uppercase" />
                            </x-ui.field>
                        @endif
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-ui.field label="Pendaftaran ditutup pada" for="e-close" optional
                                    :error="$errors->first('registration_closes_at')">
                            <x-ui.input id="e-close" type="datetime-local" wire:model="registration_closes_at"
                                        :error="$errors->has('registration_closes_at')" />
                        </x-ui.field>

                        <x-ui.field label="Had ahli keluarga" for="e-guests" required
                                    :error="$errors->first('max_guests_per_registration')">
                            <x-ui.input id="e-guests" type="number" min="0" max="20"
                                        wire:model="max_guests_per_registration"
                                        :error="$errors->has('max_guests_per_registration')" />
                        </x-ui.field>
                    </div>

                    <div class="grid gap-2.5 sm:grid-cols-3">
                        <x-ui.choice type="checkbox" name="allow_waiting_list" value="1"
                                     wire:model="allow_waiting_list" :checked="$allow_waiting_list"
                                     label="Benarkan senarai menunggu" />
                        <x-ui.choice type="checkbox" name="allow_guest_registration" value="1"
                                     wire:model="allow_guest_registration" :checked="$allow_guest_registration"
                                     label="Benarkan daftar ahli keluarga" />
                        <x-ui.choice type="checkbox" name="certificate_enabled" value="1"
                                     wire:model="certificate_enabled" :checked="$certificate_enabled"
                                     label="Keluarkan sijil" />
                    </div>
                </section>

                {{-- Penganjur --}}
                <section class="space-y-4 border-t border-hairline pt-6">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-ink-muted">Penganjur</h3>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-ui.field label="Rakan lokasi" for="e-org" optional :error="$errors->first('organizer_name')">
                            <x-ui.input id="e-org" wire:model="organizer_name"
                                        :error="$errors->has('organizer_name')" />
                        </x-ui.field>

                        <x-ui.field label="Telefon hubungan" for="e-phone" optional
                                    :error="$errors->first('contact_phone')">
                            <x-ui.input id="e-phone" type="tel" wire:model="contact_phone"
                                        :error="$errors->has('contact_phone')" />
                        </x-ui.field>
                    </div>
                </section>

                <div class="flex flex-wrap gap-2.5 border-t border-hairline pt-6">
                    <x-ui.button type="submit" variant="primary"
                                 wire:loading.attr="disabled" wire:target="save,poster">
                        <span wire:loading.remove wire:target="save">Simpan Perubahan</span>
                        <span wire:loading wire:target="save">Menyimpan…</span>
                    </x-ui.button>
                    <x-ui.button type="button" wire:click="$set('open', false)" variant="ghost">Batal</x-ui.button>
                </div>

                <p class="text-xs text-ink-muted text-pretty">
                    Perubahan tarikh atau lokasi akan menghantar pemberitahuan automatik
                    kepada semua peserta yang telah mendaftar.
                </p>
            </form>
        </x-ui.card>
    @endif
</div>
