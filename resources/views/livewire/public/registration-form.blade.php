@php
    $needsInvite = $event->pricing_mode === \App\Enums\PricingMode::JemputanSahaja;
    $isPaid = $event->pricing_mode === \App\Enums\PricingMode::Berbayar;
    $seats = 1 + count($guests);
@endphp

<div class="grid gap-8 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)] lg:items-start">
    <x-ui.card class="sm:p-8">
        @if ($event->isFull())
            <x-ui.alert variant="warning" icon="alert" title="Tempat telah penuh" class="mb-6">
                Anda masih boleh mendaftar untuk senarai menunggu. Kami akan memaklumkan
                melalui WhatsApp jika ada tempat kosong.
            </x-ui.alert>
        @endif

        <div class="space-y-6">
            <div>
                <h2 class="text-xl font-semibold text-ink">Maklumat Peserta</h2>
                <p class="mt-1.5 text-sm text-ink-soft text-pretty">
                    Hanya perlu seminit. Pastikan nama ditulis seperti yang anda mahu cetak pada sijil.
                </p>
            </div>

            <x-ui.field label="Nama penuh" for="r-name" required
                        hint="Seperti yang mahu dicetak pada sijil." :error="$errors->first('name')">
                <x-ui.input id="r-name" wire:model.blur="name" :error="$errors->has('name')"
                            autocomplete="name" />
            </x-ui.field>

            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.field label="Nombor WhatsApp" for="r-phone" required :error="$errors->first('phone')">
                    <x-ui.input id="r-phone" type="tel" wire:model.blur="phone" :error="$errors->has('phone')"
                                icon="whatsapp" inputmode="tel" autocomplete="tel" placeholder="012-345 6789" />
                </x-ui.field>

                <x-ui.field label="E-mel" for="r-email" optional
                            hint="Untuk menerima tiket & sijil." :error="$errors->first('email')">
                    <x-ui.input id="r-email" type="email" wire:model.blur="email" :error="$errors->has('email')"
                                icon="mail" autocomplete="email" />
                </x-ui.field>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.field label="Negeri" for="r-state" required :error="$errors->first('state_id')">
                    <x-ui.select id="r-state" wire:model.live="state_id" :error="$errors->has('state_id')">
                        <option value="">Pilih negeri</option>
                        @foreach ($states as $state)
                            <option value="{{ $state->id }}">{{ $state->name }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Daerah" for="r-district" optional :error="$errors->first('district_id')">
                    <x-ui.select id="r-district" wire:model="district_id" :error="$errors->has('district_id')"
                                 :disabled="$districts->isEmpty()">
                        <option value="">{{ $districts->isEmpty() ? 'Pilih negeri dahulu' : 'Pilih daerah' }}</option>
                        @foreach ($districts as $district)
                            <option value="{{ $district->id }}">{{ $district->name }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>
            </div>

            @if ($event->target_audience?->requiresGender())
                <fieldset>
                    <legend class="mb-2.5 block text-sm font-medium text-ink">Jantina</legend>
                    <div class="grid gap-2.5 sm:grid-cols-2">
                        <x-ui.choice name="gender" value="lelaki" label="Lelaki"
                                     wire:model="gender" :checked="$gender === 'lelaki'" />
                        <x-ui.choice name="gender" value="perempuan" label="Perempuan"
                                     wire:model="gender" :checked="$gender === 'perempuan'" />
                    </div>
                    @error('gender')
                        <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </fieldset>
            @endif

            @if ($needsInvite)
                <x-ui.field label="Kod jemputan" for="r-invite" required
                            hint="Program ini tertutup. Kod diberikan oleh penganjur."
                            :error="$errors->first('invite_code')">
                    <x-ui.input id="r-invite" wire:model.blur="invite_code" :error="$errors->has('invite_code')"
                                icon="lock" class="uppercase" />
                </x-ui.field>
            @endif

            {{-- ── Ahli keluarga ───────────────────────────── --}}
            @if ($event->allow_guest_registration)
                <div class="rounded-xl border border-hairline bg-mist/50 p-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-semibold text-ink">Daftar Ahli Keluarga</h3>
                            <p class="mt-0.5 text-xs text-ink-muted">
                                Sehingga {{ $event->max_guests_per_registration }} orang bersama anda.
                            </p>
                        </div>
                        @if ($this->canAddGuest())
                            <x-ui.button type="button" wire:click="addGuest" variant="outline" size="sm" icon="plus">
                                Tambah
                            </x-ui.button>
                        @endif
                    </div>

                    @if (count($guests) > 0)
                        <ul class="mt-4 space-y-3">
                            @foreach ($guests as $i => $guest)
                                <li class="rounded-xl border border-hairline bg-surface p-3.5">
                                    <div class="flex items-start gap-3">
                                        <div class="min-w-0 flex-1 space-y-3">
                                            <x-ui.field :label="'Nama ahli keluarga '.($i + 1)" :for="'g-name-'.$i"
                                                        :error="$errors->first('guests.'.$i.'.name')">
                                                <x-ui.input :id="'g-name-'.$i" wire:model.blur="guests.{{ $i }}.name"
                                                            :error="$errors->has('guests.'.$i.'.name')" />
                                            </x-ui.field>

                                            <div class="grid gap-3 sm:grid-cols-2">
                                                <x-ui.select wire:model="guests.{{ $i }}.gender"
                                                             aria-label="Jantina ahli keluarga {{ $i + 1 }}">
                                                    <option value="">Jantina (pilihan)</option>
                                                    <option value="lelaki">Lelaki</option>
                                                    <option value="perempuan">Perempuan</option>
                                                </x-ui.select>

                                                <x-ui.select wire:model="guests.{{ $i }}.age_group"
                                                             aria-label="Kumpulan umur ahli keluarga {{ $i + 1 }}">
                                                    <option value="dewasa">Dewasa</option>
                                                    <option value="remaja">Remaja</option>
                                                    <option value="kanak_kanak">Kanak-kanak</option>
                                                    <option value="warga_emas">Warga emas</option>
                                                </x-ui.select>
                                            </div>
                                        </div>

                                        <button type="button" wire:click="removeGuest({{ $i }})"
                                                class="tap-target grid shrink-0 place-items-center rounded-xl text-ink-muted
                                                       transition hover:bg-danger-soft hover:text-danger"
                                                aria-label="Buang ahli keluarga {{ $i + 1 }}">
                                            <x-ui.icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif

            <div>
                <x-ui.choice type="checkbox" name="privacy" value="1"
                             wire:model="privacy" :checked="$privacy"
                             label="Saya bersetuju dengan polisi privasi"
                             hint="Maklumat ini digunakan untuk pengesahan kehadiran dan penerbitan sijil." />
                @error('privacy')
                    <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                @enderror
                @error('event')
                    <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

            <x-ui.button type="button" wire:click="submit" variant="primary" size="lg" block
                         wire:loading.attr="disabled" wire:target="submit">
                <span wire:loading.remove wire:target="submit">
                    @if ($event->isFull())
                        Sertai Senarai Menunggu
                    @elseif ($isPaid)
                        Teruskan ke Pembayaran
                    @else
                        Sahkan Pendaftaran
                    @endif
                </span>
                <span wire:loading wire:target="submit">Memproses…</span>
            </x-ui.button>
        </div>
    </x-ui.card>

    {{-- ── Ringkasan program ───────────────────────────────── --}}
    <aside class="space-y-5 lg:sticky lg:top-24">
        <x-ui.card>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-clay-600">Anda mendaftar untuk</p>
            <h3 class="mt-2 font-semibold leading-snug text-ink text-pretty">{{ $event->title }}</h3>

            <dl class="mt-4 space-y-2.5 text-sm">
                <div class="flex gap-2.5">
                    <dt class="sr-only">Tarikh</dt>
                    <x-ui.icon name="calendar" class="mt-0.5 h-4 w-4 shrink-0 text-ink-muted" />
                    <dd class="text-ink-soft">{{ $event->dateLabel() }} &middot; {{ $event->timeLabel() }}</dd>
                </div>
                <div class="flex gap-2.5">
                    <dt class="sr-only">Lokasi</dt>
                    <x-ui.icon name="pin" class="mt-0.5 h-4 w-4 shrink-0 text-ink-muted" />
                    <dd class="text-ink-soft text-pretty">{{ $event->locationLabel() }}</dd>
                </div>
                @if ($event->speaker)
                    <div class="flex gap-2.5">
                        <dt class="sr-only">Penceramah</dt>
                        <x-ui.icon name="user" class="mt-0.5 h-4 w-4 shrink-0 text-ink-muted" />
                        <dd class="text-ink-soft">{{ $event->speaker->name }}</dd>
                    </div>
                @endif
            </dl>

            <div class="mt-5 border-t border-hairline pt-4">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-ink-soft">Bilangan tempat</span>
                    <span class="font-medium text-ink">{{ $seats }}</span>
                </div>

                @if ($isPaid)
                    <div class="mt-2 flex items-center justify-between text-sm">
                        <span class="text-ink-soft">Harga seunit</span>
                        <span class="font-medium text-ink">RM {{ number_format((float) $event->price, 2) }}</span>
                    </div>
                    <div class="mt-3 flex items-center justify-between border-t border-hairline pt-3">
                        <span class="font-medium text-ink">Jumlah</span>
                        <span class="font-display text-xl text-ink">
                            RM {{ number_format((float) $event->price * $seats, 2) }}
                        </span>
                    </div>
                @else
                    <div class="mt-3 flex items-center justify-between border-t border-hairline pt-3">
                        <span class="font-medium text-ink">Jumlah</span>
                        <span class="font-display text-xl text-success">{{ $event->priceLabel() }}</span>
                    </div>
                @endif
            </div>
        </x-ui.card>

        <div class="rounded-card border border-hairline bg-mist/60 p-5">
            <h3 class="text-sm font-semibold text-ink">Selepas mendaftar</h3>
            <ul class="mt-3 space-y-2">
                @foreach ([
                    'Anda terima tiket dengan QR kehadiran.',
                    'Peringatan dihantar sebelum program.',
                    'Imbas QR di pintu masuk pada hari program.',
                    'Sijil digital dijana automatik selepas program.',
                ] as $item)
                    <li class="flex gap-2.5 text-sm text-ink-soft text-pretty">
                        <x-ui.icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-success" />
                        {{ $item }}
                    </li>
                @endforeach
            </ul>
        </div>
    </aside>
</div>
