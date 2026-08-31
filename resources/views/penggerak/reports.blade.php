<x-layouts.app title="Sijil & Laporan" nav="penggerak" heading="Sijil & Laporan"
               subheading="Sijil penghargaan anda dan ringkasan impak setiap program.">

    {{-- Sijil penghargaan Penggerak --}}
    <section>
        <h2 class="text-lg font-semibold text-ink">Sijil Penghargaan Penggerak</h2>

        @if ($certificates->isEmpty())
            <x-ui.empty-state class="mt-4" compact icon="certificate"
                title="Belum ada sijil"
                description="Sijil penghargaan dijana automatik selepas program yang anda gerakkan selesai." />
        @else
            <ul class="mt-4 grid gap-4 sm:grid-cols-2">
                @foreach ($certificates as $certificate)
                    <li class="flex h-full flex-col rounded-card border border-hairline bg-surface p-5">
                        <div class="flex items-start gap-3">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-50">
                                <x-ui.icon name="certificate" class="h-5 w-5 text-brand-600" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="font-medium text-ink text-pretty">{{ $certificate->event_title }}</p>
                                <p class="mt-0.5 font-mono text-xs text-ink-muted">
                                    {{ $certificate->certificate_number }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2 pt-1">
                            <x-ui.button :href="$certificate->downloadUrl()" target="_blank"
                                         variant="outline" size="sm" icon="download">
                                Muat Turun
                            </x-ui.button>
                            <x-ui.button :href="$certificate->verificationUrl()" target="_blank"
                                         variant="ghost" size="sm" icon="shield">
                                Semak
                            </x-ui.button>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    {{-- Laporan ringkas --}}
    <section class="mt-10">
        <h2 class="text-lg font-semibold text-ink">Laporan Program</h2>

        @if ($events->isEmpty())
            <x-ui.empty-state class="mt-4" compact icon="chart"
                title="Belum ada program selesai"
                description="Laporan impak dijana automatik selepas program tamat." />
        @else
            <div class="mt-4 space-y-5">
                @foreach ($events as $event)
                    @php $report = $reports[$event->id] ?? null; @endphp
                    <x-ui.card>
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-medium text-ink text-pretty">{{ $event->title }}</p>
                                <p class="mt-0.5 text-sm text-ink-muted">
                                    {{ $event->dateLabel() }} &middot; {{ $event->venue?->name ?? $event->state?->name }}
                                </p>
                            </div>
                            <x-ui.button :href="route('penggerak.program.show', $event)" variant="ghost" size="sm">
                                Butiran
                            </x-ui.button>
                        </div>

                        @if ($report)
                            <dl class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                                @foreach ([
                                    ['Berdaftar', number_format($report['registered'])],
                                    ['Hadir', number_format($report['attended'])],
                                    ['Kadar', number_format($report['attendance_rate'], 0).'%'],
                                    ['Penilaian', $report['rating'] ? $report['rating'].'/5' : '—'],
                                    ['Sijil', number_format($report['certificates'])],
                                    ['Mahu lanjutan', number_format($report['wants_advanced'])],
                                ] as [$label, $value])
                                    <div class="rounded-xl bg-mist p-3">
                                        <dd class="font-display text-lg text-ink">{{ $value }}</dd>
                                        <dt class="mt-0.5 text-xs text-ink-soft text-pretty">{{ $label }}</dt>
                                    </div>
                                @endforeach
                            </dl>
                        @endif
                    </x-ui.card>
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.app>
