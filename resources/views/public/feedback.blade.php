<x-layouts.public :title="'Maklum Balas — '.$event->title">
    <section class="border-b border-hairline bg-surface">
        <div class="jelajah-container py-8">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-clay-600">Maklum Balas</p>
            <h1 class="mt-2 font-display text-2xl text-ink sm:text-3xl text-pretty">{{ $event->title }}</h1>
            <p class="mt-1.5 text-sm text-ink-muted">
                {{ $event->dateLabel() }} &middot; {{ $event->locationLabel() }}
            </p>
        </div>
    </section>

    <section class="jelajah-container py-10 sm:py-14">
        <livewire:public.feedback-form :registration="$registration" />
    </section>
</x-layouts.public>
