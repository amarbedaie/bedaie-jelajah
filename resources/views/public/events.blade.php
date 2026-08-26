<x-layouts.public title="Program Akan Datang — BeDaie Jelajah"
                  description="Senarai program BeDaie Jelajah yang dibuka untuk pendaftaran di seluruh Malaysia.">

    <x-jelajah.page-hero
        eyebrow="Program"
        title="Program Akan Datang"
        lead="Tempat adalah terhad dan diberikan mengikut giliran pendaftaran. Daftar awal untuk memastikan tempat anda." />

    <section class="jelajah-container py-12 sm:py-16">
        <x-jelajah.event-filters :states="$states" :categories="$categories" :showPrice="true" />

        @if ($events->isEmpty())
            <x-ui.empty-state class="mt-8" icon="calendar"
                title="Tiada program sepadan"
                description="Cuba ubah penapis anda, atau jemput BeDaie ke kawasan anda sendiri.">
                <div class="mt-5 flex flex-col justify-center gap-3 sm:flex-row">
                    <x-ui.button :href="route('program.index')" variant="outline">Kosongkan Penapis</x-ui.button>
                    <x-ui.button :href="route('jemput')" variant="primary">Jemput BeDaie</x-ui.button>
                </div>
            </x-ui.empty-state>
        @else
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($events as $event)
                    <x-jelajah.event-card :event="$event" />
                @endforeach
            </div>

            <div class="mt-10">{{ $events->links() }}</div>
        @endif
    </section>
</x-layouts.public>
