<x-layouts.app title="Permohonan Saya" nav="penggerak"
               heading="Permohonan Saya"
               subheading="Jejak status setiap permohonan yang anda hantar.">

    @if ($applications->isEmpty())
        <x-ui.empty-state icon="clipboard" title="Belum ada permohonan"
            description="Hantar permohonan untuk membawa BeDaie ke masjid, surau, sekolah atau komuniti anda.">
            <x-ui.button :href="route('jemput')" variant="primary" class="mt-5" icon="heart">Jemput BeDaie</x-ui.button>
        </x-ui.empty-state>
    @else
        <ul class="space-y-4">
            @foreach ($applications as $application)
                <li>
                    <a href="{{ route('penggerak.permohonan.show', $application) }}"
                       class="block rounded-[--radius-card] border border-hairline bg-surface p-5
                              transition hover:border-brand-200 hover:shadow-soft">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-medium text-navy-900 text-pretty">{{ $application->venue_name }}</p>
                                <p class="mt-0.5 text-sm text-ink-muted">
                                    {{ $application->reference_no }} &middot; {{ $application->category?->name }}
                                </p>
                                <p class="mt-0.5 text-sm text-ink-muted">
                                    {{ $application->district?->name ? $application->district->name.', ' : '' }}{{ $application->state?->name }}
                                    &middot; Dihantar {{ $application->submitted_at?->translatedFormat('j M Y') }}
                                </p>
                            </div>
                            <x-ui.badge :color="$application->status->color()" dot>
                                {{ $application->status->label() }}
                            </x-ui.badge>
                        </div>

                        <div class="mt-4">
                            <x-ui.progress :value="$application->status->progress()" :showValue="false"
                                           :tone="$application->status->isClosed() ? 'navy' : 'brand'" />
                            <p class="mt-2 text-sm text-ink-soft text-pretty">{{ $application->status->description() }}</p>
                        </div>

                        @if ($application->event)
                            <div class="mt-4 flex items-center gap-2 rounded-xl bg-success-soft px-3.5 py-2.5">
                                <x-ui.icon name="check-circle" class="h-4 w-4 shrink-0 text-success" />
                                <span class="text-sm text-[#0A5537] text-pretty">
                                    Program telah dijana: {{ $application->event->title }}
                                </span>
                            </div>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="mt-8">{{ $applications->links() }}</div>
    @endif
</x-layouts.app>
