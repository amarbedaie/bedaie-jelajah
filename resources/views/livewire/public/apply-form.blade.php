@php
    $steps = [
        1 => ['Tentang Anda', 'user'],
        2 => ['Cadangan Lokasi', 'pin'],
        3 => ['Cadangan Program', 'calendar'],
        4 => ['Semak & Hantar', 'check-circle'],
    ];
@endphp

<div class="mx-auto max-w-3xl">
    {{-- ── Penunjuk kemajuan ─────────────────────────────────── --}}
    <nav aria-label="Kemajuan borang" class="mb-8">
        <p class="mb-3 text-sm font-medium text-ink-soft">
            Langkah {{ $step }} daripada {{ \App\Livewire\Public\ApplyForm::LAST_STEP }} &middot;
            <span class="text-navy-900">{{ $steps[$step][0] }}</span>
        </p>

        <ol class="flex items-center gap-1.5">
            @foreach ($steps as $number => $meta)
                @php
                    $done = $number < $step;
                    $current = $number === $step;
                    $bar = $done || $current ? 'bg-brand-500' : 'bg-hairline';
                @endphp
                <li class="flex-1">
                    <button type="button"
                            wire:click="goTo({{ $number }})"
                            @disabled(! $done)
                            aria-current="{{ $current ? 'step' : 'false' }}"
                            class="group flex w-full flex-col gap-1.5 text-left {{ $done ? 'cursor-pointer' : 'cursor-default' }}">
                        <span class="h-1.5 w-full rounded-full transition {{ $bar }} {{ $done ? 'group-hover:bg-brand-600' : '' }}"></span>
                        <span class="hidden text-xs font-medium sm:block
                                     {{ $done || $current ? 'text-brand-700' : 'text-ink-muted' }}">
                            {{ $meta[0] }}
                        </span>
                    </button>
                </li>
            @endforeach
        </ol>
    </nav>

    <x-ui.card class="sm:p-8">
        {{-- ══ LANGKAH 1 — TENTANG ANDA ══════════════════════ --}}
        @if ($step === 1)
            <div class="space-y-6">
                <div>
                    <h2 class="text-xl font-semibold text-navy-900">Tentang Anda</h2>
                    <p class="mt-1.5 text-sm text-ink-soft text-pretty">
                        Kami perlukan cara untuk menghubungi anda. WhatsApp adalah saluran utama kami.
                    </p>
                </div>

                <x-ui.field label="Nama penuh" for="applicant_name" required :error="$errors->first('applicant_name')">
                    <x-ui.input id="applicant_name" wire:model.blur="applicant_name"
                                :error="$errors->has('applicant_name')"
                                autocomplete="name" placeholder="Contoh: Ahmad bin Abdullah" />
                </x-ui.field>

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-ui.field label="Nombor WhatsApp" for="applicant_phone" required
                                hint="Contoh: 012-345 6789" :error="$errors->first('applicant_phone')">
                        <x-ui.input id="applicant_phone" type="tel" wire:model.blur="applicant_phone"
                                    :error="$errors->has('applicant_phone')" icon="whatsapp"
                                    inputmode="tel" autocomplete="tel" placeholder="012-345 6789" />
                    </x-ui.field>

                    <x-ui.field label="E-mel" for="applicant_email" optional
                                :error="$errors->first('applicant_email')">
                        <x-ui.input id="applicant_email" type="email" wire:model.blur="applicant_email"
                                    :error="$errors->has('applicant_email')" icon="mail"
                                    autocomplete="email" placeholder="nama@email.com" />
                    </x-ui.field>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-ui.field label="Negeri" for="state_id" required :error="$errors->first('state_id')">
                        <x-ui.select id="state_id" wire:model.live="state_id" :error="$errors->has('state_id')">
                            <option value="">Pilih negeri</option>
                            @foreach ($states as $state)
                                <option value="{{ $state->id }}">{{ $state->name }}</option>
                            @endforeach
                        </x-ui.select>
                    </x-ui.field>

                    <x-ui.field label="Daerah" for="district_id" optional :error="$errors->first('district_id')">
                        <x-ui.select id="district_id" wire:model="district_id"
                                     :error="$errors->has('district_id')"
                                     :disabled="$districts->isEmpty()">
                            <option value="">{{ $districts->isEmpty() ? 'Pilih negeri dahulu' : 'Pilih daerah' }}</option>
                            @foreach ($districts as $district)
                                <option value="{{ $district->id }}">{{ $district->name }}</option>
                            @endforeach
                        </x-ui.select>
                    </x-ui.field>
                </div>

                <fieldset>
                    <legend class="mb-2.5 block text-sm font-medium text-navy-900">
                        Saya ialah <span class="text-danger" aria-hidden="true">*</span>
                    </legend>
                    <div class="grid gap-2.5 sm:grid-cols-2">
                        @foreach ($backgrounds as $option)
                            <x-ui.choice name="background" :value="$option->value"
                                         :label="$option->label()"
                                         wire:model.live="background"
                                         :checked="$background === $option->value" />
                        @endforeach
                    </div>
                    @error('background')
                        <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </fieldset>

                @if ($background === 'lain_lain')
                    <x-ui.field label="Sila nyatakan" for="background_other"
                                :error="$errors->first('background_other')">
                        <x-ui.input id="background_other" wire:model.blur="background_other"
                                    :error="$errors->has('background_other')" />
                    </x-ui.field>
                @endif
            </div>
        @endif

        {{-- ══ LANGKAH 2 — CADANGAN LOKASI ═══════════════════ --}}
        @if ($step === 2)
            <div class="space-y-6">
                <div>
                    <h2 class="text-xl font-semibold text-navy-900">Cadangan Lokasi</h2>
                    <p class="mt-1.5 text-sm text-ink-soft text-pretty">
                        Di mana anda mahu program ini diadakan? Tidak mengapa jika pihak lokasi
                        belum bersetuju — kami boleh membantu.
                    </p>
                </div>

                <x-ui.field label="Nama lokasi / masjid / surau / organisasi" for="venue_name" required
                            :error="$errors->first('venue_name')">
                    <x-ui.input id="venue_name" wire:model.blur="venue_name"
                                :error="$errors->has('venue_name')"
                                placeholder="Contoh: Masjid Al-Hidayah Kubang Kerian" />
                </x-ui.field>

                <x-ui.field label="Alamat ringkas" for="venue_address" required
                            :error="$errors->first('venue_address')">
                    <x-ui.textarea id="venue_address" wire:model.blur="venue_address" rows="3"
                                   :error="$errors->has('venue_address')"
                                   placeholder="Jalan, taman/kampung, poskod, bandar" />
                </x-ui.field>

                <x-ui.field label="Pautan Google Maps" for="venue_maps_url" optional
                            hint="Memudahkan peserta mencari lokasi kemudian."
                            :error="$errors->first('venue_maps_url')">
                    <x-ui.input id="venue_maps_url" type="url" wire:model.blur="venue_maps_url"
                                :error="$errors->has('venue_maps_url')" placeholder="https://maps.app.goo.gl/..." />
                </x-ui.field>

                <fieldset>
                    <legend class="mb-2.5 block text-sm font-medium text-navy-900">
                        Adakah pihak lokasi telah bersetuju? <span class="text-danger" aria-hidden="true">*</span>
                    </legend>
                    <div class="grid gap-2.5">
                        @foreach ($consents as $option)
                            <x-ui.choice name="venue_consent" :value="$option->value"
                                         :label="$option->label()"
                                         wire:model="venue_consent"
                                         :checked="$venue_consent === $option->value" />
                        @endforeach
                    </div>
                    @error('venue_consent')
                        <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </fieldset>

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-ui.field label="Nama PIC lokasi" for="venue_pic_name" optional
                                :error="$errors->first('venue_pic_name')">
                        <x-ui.input id="venue_pic_name" wire:model.blur="venue_pic_name"
                                    :error="$errors->has('venue_pic_name')" />
                    </x-ui.field>

                    <x-ui.field label="Nombor PIC lokasi" for="venue_pic_phone" optional
                                :error="$errors->first('venue_pic_phone')">
                        <x-ui.input id="venue_pic_phone" type="tel" wire:model.blur="venue_pic_phone"
                                    :error="$errors->has('venue_pic_phone')" inputmode="tel" />
                    </x-ui.field>
                </div>
            </div>
        @endif

        {{-- ══ LANGKAH 3 — CADANGAN PROGRAM ══════════════════ --}}
        @if ($step === 3)
            <div class="space-y-6">
                <div>
                    <h2 class="text-xl font-semibold text-navy-900">Cadangan Program</h2>
                    <p class="mt-1.5 text-sm text-ink-soft text-pretty">
                        Beritahu kami keperluan komuniti anda. Pasukan BeDaie akan menentukan
                        penceramah dan pengisian yang paling sesuai.
                    </p>
                </div>

                <x-ui.field label="Jenis program" for="event_category_id" required
                            :error="$errors->first('event_category_id')">
                    <x-ui.select id="event_category_id" wire:model="event_category_id"
                                 :error="$errors->has('event_category_id')">
                        <option value="">Pilih jenis program</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Topik atau keperluan komuniti" for="topic" required
                            hint="Apa yang paling diperlukan oleh komuniti anda sekarang?"
                            :error="$errors->first('topic')">
                    <x-ui.textarea id="topic" wire:model.blur="topic" rows="4"
                                   :error="$errors->has('topic')"
                                   placeholder="Contoh: Ramai ahli kariah keliru tentang cara qadha solat yang tertinggal bertahun-tahun." />
                </x-ui.field>

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-ui.field label="Cadangan tarikh pertama" for="preferred_date_1" required
                                :error="$errors->first('preferred_date_1')">
                        <x-ui.input id="preferred_date_1" type="date" wire:model.blur="preferred_date_1"
                                    :error="$errors->has('preferred_date_1')"
                                    min="{{ now()->addDay()->toDateString() }}" />
                    </x-ui.field>

                    <x-ui.field label="Cadangan tarikh kedua" for="preferred_date_2" optional
                                :error="$errors->first('preferred_date_2')">
                        <x-ui.input id="preferred_date_2" type="date" wire:model.blur="preferred_date_2"
                                    :error="$errors->has('preferred_date_2')"
                                    min="{{ now()->addDay()->toDateString() }}" />
                    </x-ui.field>
                </div>

                <fieldset>
                    <legend class="mb-2.5 block text-sm font-medium text-navy-900">
                        Anggaran peserta <span class="text-danger" aria-hidden="true">*</span>
                    </legend>
                    <div class="grid gap-2.5 sm:grid-cols-2">
                        @foreach ($estimates as $option)
                            <x-ui.choice name="estimated_attendees" :value="$option->value"
                                         :label="$option->label()"
                                         wire:model="estimated_attendees"
                                         :checked="$estimated_attendees === $option->value" />
                        @endforeach
                    </div>
                    @error('estimated_attendees')
                        <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </fieldset>

                <fieldset>
                    <legend class="mb-2.5 block text-sm font-medium text-navy-900">
                        Sasaran peserta <span class="text-danger" aria-hidden="true">*</span>
                    </legend>
                    <div class="grid gap-2.5 sm:grid-cols-2">
                        @foreach ($audiences as $option)
                            <x-ui.choice name="target_audience" :value="$option->value"
                                         :label="$option->label()"
                                         wire:model="target_audience"
                                         :checked="$target_audience === $option->value" />
                        @endforeach
                    </div>
                    @error('target_audience')
                        <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </fieldset>
            </div>
        @endif

        {{-- ══ LANGKAH 4 — SEMAK & HANTAR ════════════════════ --}}
        @if ($step === 4)
            @php
                $state = $states->firstWhere('id', (int) $state_id);
                $district = $districts->firstWhere('id', (int) $district_id);
                $category = $categories->firstWhere('id', (int) $event_category_id);
                $summary = [
                    'Tentang Anda' => [1, [
                        'Nama' => $applicant_name,
                        'WhatsApp' => $applicant_phone,
                        'E-mel' => $applicant_email ?: '—',
                        'Kawasan' => trim(($district?->name ? $district->name.', ' : '').($state?->name ?? '')),
                        'Saya ialah' => $background
                            ? (\App\Enums\ApplicantBackground::from($background)->label()
                                . ($background_other ? " ({$background_other})" : ''))
                            : '—',
                    ]],
                    'Cadangan Lokasi' => [2, [
                        'Lokasi' => $venue_name,
                        'Alamat' => $venue_address,
                        'Persetujuan' => $venue_consent
                            ? \App\Enums\VenueConsent::from($venue_consent)->label() : '—',
                        'PIC Lokasi' => $venue_pic_name
                            ? $venue_pic_name.($venue_pic_phone ? " ({$venue_pic_phone})" : '') : '—',
                    ]],
                    'Cadangan Program' => [3, [
                        'Jenis' => $category?->name ?? '—',
                        'Topik' => $topic,
                        'Tarikh pilihan' => collect([$preferred_date_1, $preferred_date_2])
                            ->filter()
                            ->map(fn ($d) => \Illuminate\Support\Carbon::parse($d)->translatedFormat('j F Y'))
                            ->join(' atau '),
                        'Anggaran peserta' => $estimated_attendees
                            ? \App\Enums\AttendeeEstimate::from($estimated_attendees)->label() : '—',
                        'Sasaran' => $target_audience
                            ? \App\Enums\TargetAudience::from($target_audience)->label() : '—',
                    ]],
                ];
            @endphp

            <div class="space-y-6">
                <div>
                    <h2 class="text-xl font-semibold text-navy-900">Semak & Hantar</h2>
                    <p class="mt-1.5 text-sm text-ink-soft text-pretty">
                        Sila semak maklumat di bawah. Anda boleh kembali untuk membetulkannya.
                    </p>
                </div>

                <div class="space-y-4">
                    @foreach ($summary as $sectionTitle => [$targetStep, $rows])
                        <div class="rounded-xl border border-hairline bg-mist/50 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <h3 class="text-sm font-semibold text-navy-900">{{ $sectionTitle }}</h3>
                                <button type="button" wire:click="goTo({{ $targetStep }})"
                                        class="rounded-full px-2.5 py-1 text-xs font-medium text-brand-600 hover:bg-brand-50">
                                    Ubah
                                </button>
                            </div>
                            <dl class="mt-3 space-y-1.5">
                                @foreach ($rows as $label => $value)
                                    <div class="flex flex-col gap-0.5 sm:flex-row sm:gap-3">
                                        <dt class="w-40 shrink-0 text-xs text-ink-muted">{{ $label }}</dt>
                                        <dd class="text-sm text-ink text-pretty">{{ $value ?: '—' }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                    @endforeach
                </div>

                <x-ui.field label="Nota tambahan" for="notes" optional
                            hint="Apa-apa lagi yang patut kami tahu?" :error="$errors->first('notes')">
                    <x-ui.textarea id="notes" wire:model.blur="notes" rows="3"
                                   :error="$errors->has('notes')" />
                </x-ui.field>

                <div>
                    <x-ui.choice type="checkbox" name="privacy" value="1"
                                 wire:model="privacy" :checked="$privacy"
                                 label="Saya bersetuju dengan polisi privasi"
                                 hint="Maklumat ini digunakan untuk menghubungi saya berkaitan permohonan ini sahaja." />
                    @error('privacy')
                        <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <x-ui.alert variant="info" icon="info">
                    Menghantar permohonan tidak menjamin kelulusan. Pasukan BeDaie akan menghubungi
                    anda melalui WhatsApp dalam masa beberapa hari bekerja.
                </x-ui.alert>
            </div>
        @endif

        {{-- ── Navigasi borang ──────────────────────────────── --}}
        <div class="mt-8 flex flex-col-reverse gap-3 border-t border-hairline pt-6 sm:flex-row sm:justify-between">
            @if ($step > 1)
                <x-ui.button type="button" wire:click="back" variant="ghost" icon="arrow-left">
                    Kembali
                </x-ui.button>
            @else
                <span class="hidden sm:block"></span>
            @endif

            @if ($step < \App\Livewire\Public\ApplyForm::LAST_STEP)
                <x-ui.button type="button" wire:click="next" variant="primary" iconAfter="arrow-right"
                             wire:loading.attr="disabled">
                    Seterusnya
                </x-ui.button>
            @else
                <x-ui.button type="button" wire:click="submit" variant="primary" icon="check"
                             wire:loading.attr="disabled" wire:target="submit">
                    <span wire:loading.remove wire:target="submit">Hantar Permohonan</span>
                    <span wire:loading wire:target="submit">Menghantar…</span>
                </x-ui.button>
            @endif
        </div>
    </x-ui.card>

    <p class="mt-6 text-center text-sm text-ink-muted">
        Ada soalan? Hubungi kami di
        <a href="https://wa.me/{{ config('jelajah.support.phone') }}" target="_blank" rel="noopener"
           class="font-medium text-brand-600 hover:underline">WhatsApp</a>.
    </p>
</div>
