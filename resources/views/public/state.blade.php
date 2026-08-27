@php
    $state = $detail['state'];
@endphp

<x-layouts.public :title="'Jelajah '.$state->name.' — BeDaie Jelajah'"
                  :description="'Rekod jelajah BeDaie di '.$state->name.': '.$detail['total_events'].' program, '.number_format($detail['total_participants']).' peserta.'">

    <x-jelajah.page-hero
        eyebrow="Peta Jelajah"
        :title="'Jelajah '.$state->name"
        :lead="$detail['total_events'] > 0
            ? 'BeDaie telah melaksanakan '.$detail['total_events'].' program di negeri ini.'
            : 'BeDaie belum sampai ke '.$state->name.'. Anda boleh menjadi orang yang membawanya ke sini.'">
        <x-ui.button :href="route('jemput', ['negeri' => $state->slug])" variant="primary" icon="heart">
            Jemput BeDaie ke {{ $state->name }}
        </x-ui.button>
        <x-ui.button :href="route('peta')" variant="outline">Kembali ke Peta</x-ui.button>
    </x-jelajah.page-hero>

    <section class="border-b border-hairline bg-surface">
        <div class="jelajah-container py-8">
            <dl class="grid grid-cols-2 gap-x-4 gap-y-6 sm:grid-cols-4">
                @foreach ([
                    ['Program', number_format($detail['total_events'])],
                    ['Peserta Hadir', number_format($detail['total_participants'])],
                    ['Daerah Dilawati', $detail['districts_visited']->count()],
                    ['Permintaan Komuniti', number_format($detail['interest_count'])],
                ] as [$label, $value])
                    <div>
                        <dd class="font-display text-3xl text-ink">{{ $value }}</dd>
                        <dt class="mt-0.5 text-sm text-ink-soft">{{ $label }}</dt>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    <section class="jelajah-container space-y-14 py-12 sm:py-16">
        @if ($detail['districts_visited']->isNotEmpty())
            <div>
                <h2 class="text-xl font-semibold text-ink">Daerah Yang Telah Dilawati</h2>
                <ul class="mt-4 flex flex-wrap gap-2">
                    @foreach ($detail['districts_visited'] as $district)
                        <li><x-ui.badge color="purple" icon="pin">{{ $district }}</x-ui.badge></li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div>
            <x-ui.section-heading eyebrow="Akan Datang" title="Program Akan Datang di {{ $state->name }}" />
            @if ($detail['upcoming']->isEmpty())
                <x-ui.empty-state class="mt-6" icon="calendar" compact
                    title="Belum ada program dijadualkan"
                    description="Jadilah Penggerak Jelajah yang membawa BeDaie ke kawasan anda.">
                    <x-ui.button :href="route('jemput', ['negeri' => $state->slug])" variant="primary" size="sm" class="mt-4">
                        Jemput BeDaie
                    </x-ui.button>
                </x-ui.empty-state>
            @else
                <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($detail['upcoming'] as $event)
                        <x-jelajah.event-card :event="$event" compact />
                    @endforeach
                </div>
            @endif
        </div>

        @if ($detail['completed']->isNotEmpty())
            <div>
                <x-ui.section-heading eyebrow="Jejak" title="Program Yang Telah Selesai" />
                <ul class="mt-6 divide-y divide-hairline overflow-hidden rounded-card border border-hairline bg-surface">
                    @foreach ($detail['completed'] as $event)
                        <li>
                            <a href="{{ $event->publicUrl() }}"
                               class="flex flex-wrap items-center justify-between gap-3 p-5 transition hover:bg-mist/60">
                                <div class="min-w-0">
                                    <p class="font-medium text-ink text-pretty">{{ $event->title }}</p>
                                    <p class="mt-0.5 text-sm text-ink-muted">
                                        {{ $event->dateLabel() }} &middot; {{ $event->venue?->name ?? $event->district?->name }}
                                    </p>
                                </div>
                                <x-ui.badge color="grey" icon="users">
                                    {{ number_format($event->attended_count) }} hadir
                                </x-ui.badge>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </section>
</x-layouts.public>
