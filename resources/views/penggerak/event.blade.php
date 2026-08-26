<x-layouts.app :title="$event->title" nav="penggerak">
    <a href="{{ route('penggerak.program') }}"
       class="inline-flex items-center gap-1.5 text-sm font-medium text-ink-soft hover:text-navy-900">
        <x-ui.icon name="arrow-left" class="h-4 w-4" /> Semua program
    </a>

    <div class="mt-4 flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <h1 class="font-display text-2xl text-navy-900 sm:text-3xl text-pretty">{{ $event->title }}</h1>
            <p class="mt-1.5 text-ink-soft text-pretty">
                {{ $event->dateLabel() }} &middot; {{ $event->timeLabel() }}<br>
                {{ $event->venue?->name ?? $event->locationLabel() }}
            </p>
        </div>
        <x-ui.badge :color="$event->status->color()" dot>{{ $event->status->label() }}</x-ui.badge>
    </div>

    <div class="mt-7 grid gap-6 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)]">
        <div class="space-y-6">
            {{-- Statistik --}}
            <x-ui.card>
                <h2 class="font-semibold text-navy-900">Pendaftaran</h2>

                @if ($event->capacity)
                    <div class="mt-4">
                        <x-ui.progress :value="$event->fillPercent()"
                                       :tone="$event->fillPercent() >= 90 ? 'success' : 'brand'" />
                    </div>
                @endif

                <dl class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach ([
                        ['Sasaran', number_format($event->capacity ?? 0)],
                        ['Berdaftar', number_format($event->seatsTaken())],
                        ['Baki tempat', $event->seatsLeft() !== null ? number_format($event->seatsLeft()) : '∞'],
                        ['Hadir', number_format($event->attended_count)],
                    ] as [$label, $value])
                        <div class="rounded-xl bg-mist p-3.5">
                            <dd class="font-display text-xl text-navy-900">{{ $value }}</dd>
                            <dt class="mt-0.5 text-xs text-ink-soft">{{ $label }}</dt>
                        </div>
                    @endforeach
                </dl>
            </x-ui.card>

            {{-- Kongsi --}}
            <x-ui.card>
                <h2 class="font-semibold text-navy-900">Sebarkan Program</h2>

                <div class="mt-4 flex flex-col items-center gap-5 rounded-xl bg-mist p-5 sm:flex-row">
                    <div class="shrink-0 rounded-xl bg-white p-2.5 ring-1 ring-hairline">{!! $qrSvg !!}</div>
                    <div class="min-w-0 flex-1 text-center sm:text-left">
                        <p class="text-sm font-medium text-navy-900">Link pendaftaran</p>
                        <p class="mt-1 break-all font-mono text-sm text-brand-700">{{ $event->shortUrl() }}</p>
                    </div>
                </div>

                <div class="mt-4 grid gap-2.5 sm:grid-cols-2">
                    <x-ui.button :href="$event->whatsappShareUrl()" target="_blank" rel="noopener"
                                 variant="whatsapp" block icon="whatsapp" class="sm:col-span-2">
                        Kongsi di WhatsApp
                    </x-ui.button>
                    <x-ui.copy-button :text="$event->shortUrl()" label="Salin Link" variant="outline" block />
                    <x-ui.button :href="route('penggerak.program.qr', $event)" variant="outline" block icon="download">
                        Muat Turun QR
                    </x-ui.button>
                    <x-ui.button :href="route('penggerak.program.poster', $event)" target="_blank"
                                 variant="outline" block icon="image">
                        Poster Rasmi
                    </x-ui.button>
                    <x-ui.button :href="$event->publicUrl()" target="_blank" variant="outline" block icon="external">
                        Halaman Awam
                    </x-ui.button>
                </div>
            </x-ui.card>

            {{-- Laporan ringkas selepas program --}}
            @if ($summary)
                <x-ui.card>
                    <h2 class="font-semibold text-navy-900">Laporan Ringkas</h2>
                    <dl class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @foreach ([
                            ['Berdaftar', number_format($summary['registered'])],
                            ['Hadir', number_format($summary['attended'])],
                            ['Kadar kehadiran', number_format($summary['attendance_rate'], 0).'%'],
                            ['Penilaian', $summary['rating'] ? $summary['rating'].' / 5' : '—'],
                            ['Sijil dikeluarkan', number_format($summary['certificates'])],
                            ['Mahu kelas lanjutan', number_format($summary['wants_advanced'])],
                        ] as [$label, $value])
                            <div class="rounded-xl bg-mist p-3.5">
                                <dd class="font-display text-xl text-navy-900">{{ $value }}</dd>
                                <dt class="mt-0.5 text-xs text-ink-soft text-pretty">{{ $label }}</dt>
                            </div>
                        @endforeach
                    </dl>
                </x-ui.card>
            @endif
        </div>

        {{-- Sisi --}}
        <aside class="space-y-5">
            <x-ui.card>
                <h3 class="font-semibold text-navy-900">Tindakan</h3>
                <div class="mt-4 grid gap-2.5">
                    <x-ui.button :href="route('penggerak.peserta', ['program' => $event->short_code])"
                                 variant="navy" block icon="users">
                        Lihat Peserta
                    </x-ui.button>

                    @if (! $event->hasEnded())
                        <x-ui.button :href="route('checkin.scanner', $event)" variant="primary" block icon="qr">
                            Buka Pengimbas Check-in
                        </x-ui.button>
                    @endif

                    <x-ui.button
                        :href="'https://wa.me/'.config('jelajah.support.phone').'?text='.rawurlencode('Assalamualaikum. Saya ingin meminta perubahan maklumat untuk program '.$event->short_code.' ('.$event->title.').')"
                        target="_blank" rel="noopener" variant="outline" block icon="edit">
                        Minta Perubahan Maklumat
                    </x-ui.button>

                    <x-ui.button :href="'https://wa.me/'.config('jelajah.support.phone')" target="_blank"
                                 rel="noopener" variant="ghost" block icon="chat">
                        Hubungi BeDaie
                    </x-ui.button>
                </div>

                <p class="mt-4 text-xs text-ink-muted text-pretty">
                    Reka bentuk dan maklumat kritikal program diuruskan oleh pasukan BeDaie
                    supaya semua program kekal konsisten.
                </p>
            </x-ui.card>

            <x-ui.card>
                <h3 class="font-semibold text-navy-900">Butiran Program</h3>
                <dl class="mt-3 space-y-3 text-sm">
                    @foreach ([
                        'Kod program' => $event->short_code,
                        'Kategori' => $event->category?->name,
                        'Penceramah' => $event->speaker?->name,
                        'Harga' => $event->priceLabel(),
                        'Sasaran' => $event->target_audience?->label(),
                        'Rakan lokasi' => $event->organizer_name,
                    ] as $label => $value)
                        @if ($value)
                            <div class="flex justify-between gap-3">
                                <dt class="text-ink-muted">{{ $label }}</dt>
                                <dd class="text-right text-navy-900 text-pretty">{{ $value }}</dd>
                            </div>
                        @endif
                    @endforeach
                </dl>
            </x-ui.card>
        </aside>
    </div>
</x-layouts.app>
