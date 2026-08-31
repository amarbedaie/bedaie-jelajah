<x-layouts.admin :title="'Permohonan '.$application->reference_no"
                 :heading="$application->venue_name">

    <a href="{{ route('admin.permohonan') }}"
       class="inline-flex items-center gap-1.5 text-sm font-medium text-ink-soft hover:text-ink">
        <x-ui.icon name="arrow-left" class="h-4 w-4" /> Semua permohonan
    </a>

    <div class="mt-4 flex flex-wrap items-center gap-3">
        <span class="font-mono text-sm text-ink-muted">{{ $application->reference_no }}</span>
        <x-ui.badge :color="$application->status->color()" dot>{{ $application->status->label() }}</x-ui.badge>
        @if ($application->assignedAdmin)
            <x-ui.badge color="grey" icon="user">{{ $application->assignedAdmin->name }}</x-ui.badge>
        @endif
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)]">
        {{-- ── Butiran ────────────────────────────────────── --}}
        <div class="space-y-6">
            <x-ui.card>
                <h2 class="font-semibold text-ink">Maklumat Pemohon</h2>
                <dl class="mt-4 divide-y divide-hairline">
                    @foreach ([
                        'Nama' => $application->applicant_name,
                        'WhatsApp' => $application->applicant_phone,
                        'E-mel' => $application->applicant_email,
                        'Latar belakang' => $application->backgroundLabel(),
                        'Akaun Penggerak' => $application->user?->name,
                    ] as $label => $value)
                        @if ($value)
                            <div class="flex flex-col gap-1 py-3 sm:flex-row sm:gap-4">
                                <dt class="w-40 shrink-0 text-sm text-ink-muted">{{ $label }}</dt>
                                <dd class="text-sm text-ink text-pretty">
                                    @if ($label === 'WhatsApp')
                                        <a href="https://wa.me/{{ $value }}" target="_blank" rel="noopener"
                                           class="font-medium text-brand-600 hover:underline">{{ $value }}</a>
                                    @else
                                        {{ $value }}
                                    @endif
                                </dd>
                            </div>
                        @endif
                    @endforeach
                </dl>
            </x-ui.card>

            <x-ui.card>
                <h2 class="font-semibold text-ink">Maklumat Lokasi</h2>
                <dl class="mt-4 divide-y divide-hairline">
                    @foreach ([
                        'Lokasi' => $application->venue_name,
                        'Alamat' => $application->venue_address,
                        'Kawasan' => trim(($application->district?->name ? $application->district->name.', ' : '').($application->state?->name ?? '')),
                        'Persetujuan' => $application->venue_consent?->label(),
                        'PIC lokasi' => $application->venue_pic_name,
                        'Telefon PIC' => $application->venue_pic_phone,
                    ] as $label => $value)
                        @if ($value)
                            <div class="flex flex-col gap-1 py-3 sm:flex-row sm:gap-4">
                                <dt class="w-40 shrink-0 text-sm text-ink-muted">{{ $label }}</dt>
                                <dd class="text-sm text-ink text-pretty">{{ $value }}</dd>
                            </div>
                        @endif
                    @endforeach
                </dl>

                @if ($application->venue_maps_url)
                    <x-ui.button :href="$application->venue_maps_url" target="_blank" rel="noopener"
                                 variant="outline" size="sm" class="mt-4" icon="pin">
                        Buka Google Maps
                    </x-ui.button>
                @endif
            </x-ui.card>

            <x-ui.card>
                <h2 class="font-semibold text-ink">Cadangan Program</h2>
                <dl class="mt-4 divide-y divide-hairline">
                    @foreach ([
                        'Jenis program' => $application->category?->name,
                        'Topik' => $application->topic,
                        'Cadangan tarikh' => collect([$application->preferred_date_1, $application->preferred_date_2])
                            ->filter()->map(fn ($d) => $d->translatedFormat('j F Y (l)'))->join(' atau '),
                        'Anggaran peserta' => $application->estimated_attendees?->label(),
                        'Sasaran peserta' => $application->target_audience?->label(),
                        'Nota pemohon' => $application->notes,
                    ] as $label => $value)
                        @if ($value)
                            <div class="flex flex-col gap-1 py-3 sm:flex-row sm:gap-4">
                                <dt class="w-40 shrink-0 text-sm text-ink-muted">{{ $label }}</dt>
                                <dd class="text-sm text-ink text-pretty">{{ $value }}</dd>
                            </div>
                        @endif
                    @endforeach
                </dl>
            </x-ui.card>

            {{-- Timeline penuh (termasuk nota dalaman) --}}
            <x-ui.card>
                <h2 class="font-semibold text-ink">Timeline Status</h2>
                <ol class="mt-5">
                    @forelse ($application->statusHistories->sortByDesc('created_at') as $entry)
                        <li class="relative flex gap-4 border-l-2 border-brand-200 pb-6 pl-6 last:border-transparent last:pb-0">
                            <span class="absolute -left-[7px] top-1 h-3 w-3 rounded-full bg-brand-400 ring-4 ring-surface"></span>
                            <div class="min-w-0">
                                <p class="font-medium text-ink">
                                    {{ $entry->to_status->label() }}
                                </p>
                                @if ($entry->public_note)
                                    <p class="mt-1 text-sm text-ink-soft text-pretty">{{ $entry->public_note }}</p>
                                @endif
                                @if ($entry->internal_note)
                                    <p class="mt-1.5 rounded-lg bg-mist px-2.5 py-1.5 text-[0.8125rem] text-[#7A4E06] text-pretty">
                                        <strong>Dalaman:</strong> {{ $entry->internal_note }}
                                    </p>
                                @endif
                                <p class="mt-1.5 text-[0.8125rem] text-ink-muted">
                                    {{ $entry->created_at->translatedFormat('j M Y, g:ia') }}
                                    @if ($entry->user) &middot; {{ $entry->user->name }} @endif
                                </p>
                            </div>
                        </li>
                    @empty
                        <li class="text-sm text-ink-muted">Belum ada rekod status.</li>
                    @endforelse
                </ol>
            </x-ui.card>

            {{-- Nota dalaman / rekod komunikasi --}}
            <x-ui.card>
                <h2 class="font-semibold text-ink">Nota Dalaman & Komunikasi</h2>
                @if ($application->internalNotes->isNotEmpty())
                    <ul class="mt-4 space-y-3">
                        @foreach ($application->internalNotes as $note)
                            <li class="rounded-xl border border-hairline bg-mist/50 p-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <x-ui.badge color="grey">{{ ucfirst($note->channel) }}</x-ui.badge>
                                    <span class="text-[0.8125rem] text-ink-muted">
                                        {{ $note->occurred_at?->translatedFormat('j M Y, g:ia') }}
                                        @if ($note->user) &middot; {{ $note->user->name }} @endif
                                    </span>
                                </div>
                                <p class="mt-2 text-sm text-ink text-pretty">{{ $note->body }}</p>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-3 text-sm text-ink-muted">Belum ada nota.</p>
                @endif
            </x-ui.card>
        </div>

        {{-- ── Tindakan ───────────────────────────────────── --}}
        <div>
            <div class="xl:sticky xl:top-24">
                <livewire:admin.application-workflow :application="$application" />
            </div>
        </div>
    </div>
</x-layouts.admin>
