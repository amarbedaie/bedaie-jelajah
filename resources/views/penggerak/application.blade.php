<x-layouts.app :title="'Permohonan '.$application->reference_no" nav="penggerak">
    <a href="{{ route('penggerak.permohonan') }}"
       class="inline-flex items-center gap-1.5 text-sm font-medium text-ink-soft hover:text-ink">
        <x-ui.icon name="arrow-left" class="h-4 w-4" /> Semua permohonan
    </a>

    <div class="mt-4 flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <h1 class="font-display text-2xl text-ink sm:text-3xl text-pretty">{{ $application->venue_name }}</h1>
            <p class="mt-1.5 font-mono text-sm text-ink-muted">{{ $application->reference_no }}</p>
        </div>
        <x-ui.badge :color="$application->status->color()" dot>{{ $application->status->label() }}</x-ui.badge>
    </div>

    <div class="mt-7 grid gap-6 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)]">
        <div class="space-y-6">
            {{-- Status semasa --}}
            <x-ui.card>
                <h2 class="font-semibold text-ink">Status Permohonan</h2>
                <div class="mt-4">
                    <x-ui.progress :value="$application->status->progress()"
                                   :tone="$application->status->isClosed() ? 'navy' : 'brand'" />
                </div>
                <p class="mt-3 text-ink-soft text-pretty">{{ $application->status->description() }}</p>

                @if ($application->event)
                    <div class="mt-5 rounded-xl border border-success/25 bg-success-soft p-4">
                        <div class="flex items-start gap-3">
                            <x-ui.icon name="check-circle" class="mt-0.5 h-5 w-5 shrink-0 text-success" />
                            <div class="min-w-0 flex-1">
                                <p class="font-medium text-[#0A5537]">Program anda telah disahkan</p>
                                <p class="mt-1 text-sm text-[#0A5537]/85 text-pretty">
                                    {{ $application->event->title }}
                                </p>
                                <x-ui.button :href="route('penggerak.program.show', $application->event)"
                                             variant="success" size="sm" class="mt-3">
                                    Buka Dashboard Program
                                </x-ui.button>
                            </div>
                        </div>
                    </div>
                @endif
            </x-ui.card>

            {{-- Perjalanan permohonan (nota awam sahaja) --}}
            <x-ui.card>
                <h2 class="font-semibold text-ink">Perjalanan Permohonan</h2>
                <ol class="mt-5 space-y-0">
                    @forelse ($application->publicTimeline as $entry)
                        <li class="relative flex gap-4 border-l-2 border-clay-200 pb-6 pl-6 last:border-transparent last:pb-0">
                            <span class="absolute -left-[7px] top-1 h-3 w-3 rounded-full bg-clay-400 ring-4 ring-surface"></span>
                            <div class="min-w-0">
                                <p class="font-medium text-ink">
                                    {{ $entry->to_status->label() }}
                                </p>
                                @if ($entry->public_note)
                                    <p class="mt-1 text-sm text-ink-soft text-pretty">{{ $entry->public_note }}</p>
                                @endif
                                <p class="mt-1 text-xs text-ink-muted">
                                    {{ $entry->created_at->translatedFormat('j M Y, g:ia') }}
                                </p>
                            </div>
                        </li>
                    @empty
                        <li class="text-sm text-ink-muted">Belum ada kemas kini.</li>
                    @endforelse
                </ol>
            </x-ui.card>

            {{-- Maklumat dihantar --}}
            <x-ui.card>
                <h2 class="font-semibold text-ink">Maklumat Yang Anda Hantar</h2>
                <dl class="mt-4 divide-y divide-hairline">
                    @foreach ([
                        'Lokasi' => $application->venue_name,
                        'Alamat' => $application->venue_address,
                        'Kawasan' => trim(($application->district?->name ? $application->district->name.', ' : '').($application->state?->name ?? '')),
                        'Persetujuan lokasi' => $application->venue_consent?->label(),
                        'Jenis program' => $application->category?->name,
                        'Topik' => $application->topic,
                        'Cadangan tarikh' => collect([$application->preferred_date_1, $application->preferred_date_2])
                            ->filter()->map(fn ($d) => $d->translatedFormat('j F Y'))->join(' atau '),
                        'Anggaran peserta' => $application->estimated_attendees?->label(),
                        'Sasaran peserta' => $application->target_audience?->label(),
                        'Nota tambahan' => $application->notes,
                    ] as $label => $value)
                        @if ($value)
                            <div class="flex flex-col gap-1 py-3 sm:flex-row sm:gap-4">
                                <dt class="w-44 shrink-0 text-sm text-ink-muted">{{ $label }}</dt>
                                <dd class="text-sm text-ink text-pretty">{{ $value }}</dd>
                            </div>
                        @endif
                    @endforeach
                </dl>
            </x-ui.card>
        </div>

        {{-- Sisi --}}
        <aside class="space-y-5">
            <x-ui.card>
                <h3 class="font-semibold text-ink">Perlukan Bantuan?</h3>
                <p class="mt-2 text-sm text-ink-soft text-pretty">
                    Jika ada maklumat yang perlu dibetulkan atau anda ingin berbincang,
                    hubungi kami terus di WhatsApp.
                </p>
                <div class="mt-4 grid gap-2.5">
                    <x-ui.button
                        :href="'https://wa.me/'.config('jelajah.support.phone').'?text='.rawurlencode('Assalamualaikum. Saya ingin bertanya tentang permohonan '.$application->reference_no.'.')"
                        target="_blank" rel="noopener" variant="whatsapp" block icon="whatsapp">
                        Hubungi BeDaie
                    </x-ui.button>
                    <x-ui.button
                        :href="'https://wa.me/'.config('jelajah.support.phone').'?text='.rawurlencode('Assalamualaikum. Saya ingin meminta perubahan maklumat untuk permohonan '.$application->reference_no.'.')"
                        target="_blank" rel="noopener" variant="outline" block icon="edit">
                        Minta Perubahan Maklumat
                    </x-ui.button>
                </div>
            </x-ui.card>

            <div class="rounded-card bg-mist p-5">
                <h3 class="text-sm font-semibold text-ink">Apa yang berlaku seterusnya?</h3>
                <ol class="mt-3 space-y-2">
                    @foreach ([
                        'Kami semak kesesuaian lokasi dan tarikh.',
                        'Pasukan BeDaie hubungi anda di WhatsApp.',
                        'Tarikh dan penceramah ditetapkan bersama.',
                        'Program disahkan — link, QR dan poster dijana automatik.',
                    ] as $i => $text)
                        <li class="flex gap-2.5 text-sm text-ink-soft text-pretty">
                            <span class="grid h-5 w-5 shrink-0 place-items-center rounded-full bg-clay-600
                                         text-[0.65rem] font-bold text-white">{{ $i + 1 }}</span>
                            {{ $text }}
                        </li>
                    @endforeach
                </ol>
            </div>
        </aside>
    </div>
</x-layouts.app>
