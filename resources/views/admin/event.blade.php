<x-layouts.admin :title="$event->title" :heading="$event->title">
    <a href="{{ route('admin.program') }}"
       class="inline-flex items-center gap-1.5 text-sm font-medium text-ink-soft hover:text-ink">
        <x-ui.icon name="arrow-left" class="h-4 w-4" /> Semua program
    </a>

    <div class="mt-4 flex flex-wrap items-center gap-3">
        <span class="font-mono text-sm text-ink-muted">{{ $event->short_code }}</span>
        <x-ui.badge :color="$event->status->color()" dot>{{ $event->status->label() }}</x-ui.badge>
        <x-ui.badge color="grey">{{ $event->priceLabel() }}</x-ui.badge>
    </div>

    <div class="mt-5">
        <livewire:admin.event-editor :event="$event" />
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)]">
        <div class="space-y-6">
            {{-- Ringkasan --}}
            <x-ui.card>
                <h2 class="font-semibold text-ink">Ringkasan Pendaftaran</h2>

                @if ($event->capacity)
                    <div class="mt-4">
                        <x-ui.progress :value="$event->fillPercent()"
                                       :tone="$event->fillPercent() >= 90 ? 'success' : 'brand'" />
                    </div>
                @endif

                <dl class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach ([
                        ['Kapasiti', number_format($event->capacity ?? 0)],
                        ['Berdaftar', number_format($report['registered'])],
                        ['Senarai menunggu', number_format($report['waitlist'])],
                        ['Hadir', number_format($report['attended'])],
                        ['Kadar kehadiran', number_format($report['attendance_rate'], 1).'%'],
                        ['Walk-in', number_format($report['walk_in'])],
                        ['Ahli keluarga', number_format($report['guests'])],
                        ['Sijil sah', number_format($report['certificates'])],
                    ] as [$label, $value])
                        <div class="rounded-xl bg-mist p-3.5">
                            <dd class="font-display text-xl text-ink">{{ $value }}</dd>
                            <dt class="mt-0.5 text-xs text-ink-soft text-pretty">{{ $label }}</dt>
                        </div>
                    @endforeach
                </dl>
            </x-ui.card>

            {{-- Butiran --}}
            <x-ui.card>
                <h2 class="font-semibold text-ink">Butiran Program</h2>
                <dl class="mt-4 divide-y divide-hairline">
                    @foreach ([
                        'Tajuk' => $event->title,
                        'Tema' => $event->theme,
                        'Kategori' => $event->category?->name,
                        'Penceramah' => $event->speaker?->name,
                        'Tarikh & masa' => $event->dateLabel().' · '.$event->timeLabel(),
                        'Lokasi' => $event->venue?->name,
                        'Alamat' => $event->venue?->address,
                        'Kawasan' => trim(($event->district?->name ? $event->district->name.', ' : '').($event->state?->name ?? '')),
                        'Sasaran' => $event->target_audience?->label(),
                        'Rakan lokasi' => $event->organizer_name,
                        'Jam pembelajaran' => $event->learning_hours ? $event->learning_hours.' jam' : null,
                        'Penggerak' => $event->mobilizers->pluck('name')->join(', ') ?: null,
                        'Dari permohonan' => $event->application?->reference_no,
                    ] as $label => $value)
                        @if ($value)
                            <div class="flex flex-col gap-1 py-3 sm:flex-row sm:gap-4">
                                <dt class="w-40 shrink-0 text-sm text-ink-muted">{{ $label }}</dt>
                                <dd class="text-sm text-ink text-pretty">
                                    @if ($label === 'Dari permohonan')
                                        <a href="{{ route('admin.permohonan.show', $event->application) }}"
                                           class="font-medium text-clay-600 hover:underline">{{ $value }}</a>
                                    @else
                                        {{ $value }}
                                    @endif
                                </dd>
                            </div>
                        @endif
                    @endforeach
                </dl>
            </x-ui.card>

            <livewire:admin.recording-manager :event="$event" />

            {{-- Kewangan & maklum balas --}}
            <div class="grid gap-6 sm:grid-cols-2">
                <x-ui.card>
                    <h2 class="font-semibold text-ink">Kewangan</h2>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between gap-3">
                            <dt class="text-ink-muted">Kutipan berjaya</dt>
                            <dd class="font-medium text-ink">RM {{ number_format($report['revenue'], 2) }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-ink-muted">Menunggu pengesahan</dt>
                            <dd class="text-ink">{{ number_format($report['pending_payments']) }}</dd>
                        </div>
                    </dl>
                </x-ui.card>

                <x-ui.card>
                    <h2 class="font-semibold text-ink">Maklum Balas</h2>
                    @if ($report['rating_count'] > 0)
                        <p class="mt-3 font-display text-3xl text-ink">
                            {{ number_format((float) $report['rating'], 1) }}
                            <span class="text-base font-normal text-ink-muted">/ 5</span>
                        </p>
                        <p class="mt-0.5 text-xs text-ink-muted">
                            daripada {{ $report['rating_count'] }} maklum balas
                        </p>
                        <ul class="mt-4 space-y-1.5">
                            @foreach ($report['rating_breakdown'] as $star => $count)
                                <li class="flex items-center gap-2.5 text-xs">
                                    <span class="w-8 shrink-0 text-ink-muted">{{ $star }} ★</span>
                                    <span class="h-1.5 flex-1 overflow-hidden rounded-full bg-mist">
                                        <span class="block h-full rounded-full bg-clay-400"
                                              style="width: {{ $report['rating_count'] ? round($count / $report['rating_count'] * 100) : 0 }}%"></span>
                                    </span>
                                    <span class="w-6 shrink-0 text-right text-ink-muted">{{ $count }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="mt-3 text-sm text-ink-muted">Belum ada maklum balas.</p>
                    @endif
                </x-ui.card>
            </div>

            {{-- Demografi --}}
            @if ($report['by_state']->isNotEmpty())
                <x-ui.card>
                    <h2 class="font-semibold text-ink">Peserta Mengikut Negeri</h2>
                    <ul class="mt-4 space-y-2">
                        @foreach ($report['by_state']->take(8) as $stateName => $count)
                            <li class="flex items-center justify-between gap-3 text-sm">
                                <span class="text-ink-soft">{{ $stateName }}</span>
                                <span class="font-medium text-ink">{{ $count }}</span>
                            </li>
                        @endforeach
                    </ul>
                </x-ui.card>
            @endif
        </div>

        {{-- Sisi --}}
        <aside class="space-y-5 xl:sticky xl:top-24 xl:self-start">
            <x-ui.card>
                <h3 class="font-semibold text-ink">Tindakan</h3>
                <div class="mt-4 grid gap-2.5">
                    <x-ui.button :href="$event->publicUrl()" target="_blank" variant="outline" block icon="external">
                        Lihat Halaman Awam
                    </x-ui.button>
                    <x-ui.button :href="route('checkin.scanner', $event)" variant="primary" block icon="qr">
                        Pengimbas Check-in
                    </x-ui.button>
                    <x-ui.button :href="route('admin.kehadiran.show', $event)" variant="outline" block icon="users">
                        Papan Kehadiran
                    </x-ui.button>
                    <x-ui.button :href="route('admin.laporan.program', $event)" variant="outline" block icon="chart">
                        Laporan Penuh
                    </x-ui.button>
                    <x-ui.button :href="route('admin.sijil', ['program' => $event->short_code])"
                                 variant="outline" block icon="certificate">
                        Sijil Program Ini
                    </x-ui.button>
                    @can('export-participants')
                        <x-ui.button :href="route('admin.laporan.eksport', $event)" variant="outline" block icon="download">
                            Eksport Peserta (CSV)
                        </x-ui.button>
                    @endcan
                </div>
            </x-ui.card>

            <x-ui.card>
                <h3 class="font-semibold text-ink">Pautan & QR</h3>
                <div class="mt-4 flex flex-col items-center gap-4 rounded-xl bg-mist p-4">
                    <div class="rounded-xl bg-white p-2.5 ring-1 ring-hairline">
                        {!! app(\App\Services\QrCodeService::class)->svg($event->shortUrl(), 150) !!}
                    </div>
                    <p class="break-all text-center font-mono text-xs text-clay-700">{{ $event->shortUrl() }}</p>
                </div>
                <div class="mt-4 grid gap-2.5">
                    <x-ui.copy-button :text="$event->shortUrl()" label="Salin Link Pendek" variant="outline" block />
                    <x-ui.copy-button :text="$event->publicUrl()" label="Salin Link Penuh" variant="ghost" block />
                </div>
            </x-ui.card>
        </aside>
    </div>
</x-layouts.admin>
