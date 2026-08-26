<x-layouts.public title="Jejak Jelajah — BeDaie Jelajah"
                  description="Arkib program BeDaie Jelajah yang telah dilaksanakan di seluruh Malaysia.">

    <x-jelajah.page-hero
        eyebrow="Jejak Jelajah"
        title="Program Yang Telah Kami Laksanakan"
        lead="Setiap program meninggalkan kesan — pada ilmu, pada hati, pada komuniti." />

    <section class="jelajah-container py-12 sm:py-16">
        <x-jelajah.event-filters :states="$states" :categories="$categories" :action="route('jejak')" />

        @if ($events->isEmpty())
            <x-ui.empty-state class="mt-8" icon="map" title="Tiada rekod sepadan"
                description="Cuba ubah penapis anda." />
        @else
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($events as $event)
                    <article class="flex h-full flex-col overflow-hidden rounded-[--radius-card] border
                                    border-hairline bg-surface shadow-soft">
                        <a href="{{ $event->publicUrl() }}" class="relative block aspect-[16/9] bg-navy-900">
                            @if ($event->heroUrl())
                                <img src="{{ $event->heroUrl() }}" alt="{{ $event->title }}" loading="lazy"
                                     class="h-full w-full object-cover" />
                            @else
                                <div class="motif-girih-dark absolute inset-0 opacity-60" aria-hidden="true"></div>
                                <div class="absolute inset-0 bg-gradient-to-tr from-navy-900 to-brand-700/60"></div>
                            @endif
                            <div class="absolute left-3 top-3">
                                <x-ui.badge color="white">{{ $event->state?->name }}</x-ui.badge>
                            </div>
                        </a>

                        <div class="flex flex-1 flex-col p-5">
                            <p class="text-xs text-ink-muted">{{ $event->dateLabel() }}</p>
                            <h3 class="mt-1.5 font-semibold leading-snug text-navy-900 text-pretty">
                                <a href="{{ $event->publicUrl() }}" class="hover:text-brand-700">{{ $event->title }}</a>
                            </h3>
                            <p class="mt-2 text-sm text-ink-soft text-pretty">{{ $event->locationLabel() }}</p>

                            <div class="mt-4 flex flex-wrap gap-2 pt-1">
                                <x-ui.badge color="purple" icon="users">
                                    {{ number_format($event->attended_count) }} hadir
                                </x-ui.badge>
                                @if ($event->averageRating())
                                    <x-ui.badge color="success" icon="star">
                                        {{ number_format($event->averageRating(), 1) }}
                                    </x-ui.badge>
                                @endif
                                @if ($event->certificates_count)
                                    <x-ui.badge color="grey" icon="certificate">
                                        {{ $event->certificates_count }} sijil
                                    </x-ui.badge>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-10">{{ $events->links() }}</div>
        @endif
    </section>
</x-layouts.public>
