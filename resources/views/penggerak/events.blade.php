<x-layouts.app title="Program Saya" nav="penggerak"
               heading="Program Saya"
               subheading="Semua program yang anda gerakkan.">

    @if ($events->isEmpty())
        <x-ui.empty-state icon="calendar" title="Belum ada program"
            description="Program akan muncul di sini sebaik permohonan anda disahkan oleh pasukan BeDaie.">
            <x-ui.button :href="route('jemput')" variant="primary" class="mt-5">Hantar Permohonan</x-ui.button>
        </x-ui.empty-state>
    @else
        <ul class="grid gap-5 sm:grid-cols-2">
            @foreach ($events as $event)
                <li>
                    <a href="{{ route('penggerak.program.show', $event) }}"
                       class="flex h-full flex-col rounded-[--radius-card] border border-hairline bg-surface p-5
                              transition hover:border-brand-200 hover:shadow-soft">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <x-ui.badge :color="$event->status->color()" dot>{{ $event->status->label() }}</x-ui.badge>
                            <span class="text-xs text-ink-muted">{{ $event->short_code }}</span>
                        </div>

                        <p class="mt-3 font-medium text-navy-900 text-pretty">{{ $event->title }}</p>
                        <p class="mt-1 text-sm text-ink-muted">
                            {{ $event->dateLabel() }} &middot; {{ $event->venue?->name ?? $event->locationLabel() }}
                        </p>

                        @if ($event->capacity)
                            <div class="mt-4">
                                <x-ui.progress :value="$event->fillPercent()" :showValue="false" />
                                <p class="mt-1.5 text-xs text-ink-muted">
                                    {{ number_format($event->seatsTaken()) }} / {{ number_format($event->capacity) }} tempat
                                    @if ($event->hasEnded())
                                        &middot; {{ number_format($event->attended_count) }} hadir
                                    @endif
                                </p>
                            </div>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="mt-8">{{ $events->links() }}</div>
    @endif
</x-layouts.app>
