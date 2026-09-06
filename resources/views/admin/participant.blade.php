<x-layouts.admin :title="$registration->name" :heading="$registration->name">

    <div class="max-w-4xl">
        <x-ui.button :href="route('admin.peserta')" variant="ghost" size="sm" icon="arrow-left">
            Semua peserta
        </x-ui.button>

        {{-- ── Siapa orang ini ───────────────────────────────── --}}
        <x-ui.card class="mt-5 sm:p-7">
            <div class="flex flex-wrap items-start justify-between gap-5">
                <div class="min-w-0">
                    <h2 class="font-display text-3xl leading-tight text-ink text-pretty">
                        {{ $registration->name }}
                    </h2>

                    <dl class="mt-4 space-y-2 text-sm">
                        <div class="flex gap-3">
                            <dt class="w-24 shrink-0 text-ink-muted">WhatsApp</dt>
                            <dd>
                                <a href="https://wa.me/{{ $registration->phone }}" target="_blank" rel="noopener"
                                   class="font-medium text-brand-700 hover:underline">{{ $registration->phone }}</a>
                            </dd>
                        </div>
                        @if ($registration->email)
                            <div class="flex gap-3">
                                <dt class="w-24 shrink-0 text-ink-muted">E-mel</dt>
                                <dd class="text-ink text-pretty">{{ $registration->email }}</dd>
                            </div>
                        @endif
                        <div class="flex gap-3">
                            <dt class="w-24 shrink-0 text-ink-muted">Kawasan</dt>
                            <dd class="text-ink">
                                {{ $registration->district?->name ? $registration->district->name.', ' : '' }}{{ $registration->state?->name ?? '—' }}
                            </dd>
                        </div>
                        @if ($stats['pertama'])
                            <div class="flex gap-3">
                                <dt class="w-24 shrink-0 text-ink-muted">Kenal sejak</dt>
                                <dd class="text-ink">{{ $stats['pertama']->translatedFormat('F Y') }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <x-ui.button :href="'https://wa.me/'.$registration->phone" target="_blank" rel="noopener"
                             variant="primary" icon="whatsapp">
                    Hubungi
                </x-ui.button>
            </div>

            {{-- Nombor yang paling bermakna: berapa kali orang ini datang. --}}
            <dl class="mt-7 grid grid-cols-2 gap-x-8 gap-y-6 border-t border-hairline pt-6 sm:grid-cols-4">
                @foreach ([
                    ['Kali mendaftar', $stats['jumlah']],
                    ['Kali hadir', $stats['hadir']],
                    ['Tidak hadir', $stats['terlepas']],
                    ['Sijil diperoleh', $stats['sijil']],
                ] as [$label, $value])
                    <div>
                        <dd class="font-display text-4xl leading-none text-ink tabular-nums">{{ $value }}</dd>
                        <dt class="mt-2 text-sm leading-snug text-ink-muted text-pretty">{{ $label }}</dt>
                    </div>
                @endforeach
            </dl>

            @if ($stats['negeri']->isNotEmpty())
                <p class="mt-6 flex flex-wrap items-center gap-2 border-t border-hairline pt-5 text-sm text-ink-muted">
                    <span>Mengikuti jelajah di</span>
                    @foreach ($stats['negeri'] as $negeri)
                        <x-ui.badge color="soft">{{ $negeri }}</x-ui.badge>
                    @endforeach
                </p>
            @endif
        </x-ui.card>

        {{-- ── Sejarah penuh ─────────────────────────────────── --}}
        <section class="mt-8">
            <h2 class="font-display text-2xl text-ink">Sejarah Penyertaan</h2>
            <p class="mt-1.5 text-sm text-ink-soft">
                Semua pendaftaran yang menggunakan nombor WhatsApp yang sama.
            </p>

            <ul class="mt-5 divide-y divide-hairline border-t border-hairline">
                @foreach ($history as $row)
                    <li class="grid gap-2 py-5 sm:grid-cols-[8rem_minmax(0,1fr)_auto] sm:items-baseline sm:gap-5">
                        <span class="text-sm text-ink-muted tabular-nums">
                            {{ $row->event?->dateLabel() ?? '—' }}
                        </span>

                        <span class="min-w-0">
                            @if ($row->event)
                                <a href="{{ route('admin.program.show', $row->event) }}"
                                   class="block font-display text-lg leading-snug text-ink hover:text-brand-700 text-pretty">
                                    {{ $row->event->title }}
                                </a>
                                <span class="mt-1 block text-sm text-ink-soft text-pretty">
                                    {{ $row->event->venue?->name ?? $row->event->locationLabel() }}
                                </span>
                            @else
                                <span class="text-ink-muted">Program telah dipadam</span>
                            @endif
                        </span>

                        <span class="flex flex-wrap items-center gap-1.5 sm:justify-self-end">
                            @if ($row->hasAttended())
                                <x-ui.badge color="solid" icon="check-circle">Hadir</x-ui.badge>
                            @elseif ($row->event?->hasEnded())
                                <x-ui.badge color="quiet">Tidak hadir</x-ui.badge>
                            @else
                                <x-ui.badge :color="$row->status->color()">{{ $row->status->label() }}</x-ui.badge>
                            @endif

                            @if ($row->certificate?->isValid())
                                <x-ui.badge color="soft" icon="certificate">Sijil</x-ui.badge>
                            @endif

                            @if ($row->feedback?->rating)
                                <x-ui.badge color="edge" icon="star">{{ $row->feedback->rating }}/5</x-ui.badge>
                            @endif
                        </span>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>
</x-layouts.admin>
