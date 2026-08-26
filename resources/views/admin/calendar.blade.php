@php
    $start = $month->copy()->startOfMonth()->startOfWeek();
    $end = $month->copy()->endOfMonth()->endOfWeek();
    $days = [];
    for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
        $days[] = $d->copy();
    }
@endphp

<x-layouts.admin title="Kalendar" heading="Kalendar Program">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <h2 class="font-display text-xl text-navy-900">{{ $month->translatedFormat('F Y') }}</h2>
        <div class="flex gap-2">
            <x-ui.button :href="route('admin.kalendar', ['bulan' => $month->copy()->subMonth()->format('Y-m')])"
                         variant="outline" size="sm" icon="arrow-left">Sebelum</x-ui.button>
            <x-ui.button :href="route('admin.kalendar')" variant="outline" size="sm">Bulan Ini</x-ui.button>
            <x-ui.button :href="route('admin.kalendar', ['bulan' => $month->copy()->addMonth()->format('Y-m')])"
                         variant="outline" size="sm" iconAfter="arrow-right">Seterusnya</x-ui.button>
        </div>
    </div>

    <div class="overflow-hidden rounded-card border border-hairline bg-surface">
        <div class="grid grid-cols-7 border-b border-hairline bg-mist/60">
            @foreach (['Isn', 'Sel', 'Rab', 'Kha', 'Jum', 'Sab', 'Ahd'] as $label)
                <div class="px-2 py-2.5 text-center text-xs font-semibold text-ink-soft">{{ $label }}</div>
            @endforeach
        </div>

        <div class="grid grid-cols-7">
            @foreach ($days as $day)
                @php
                    $key = $day->toDateString();
                    $dayEvents = $events[$key] ?? collect();
                    $inMonth = $day->month === $month->month;
                @endphp
                <div class="min-h-[6.5rem] border-b border-r border-hairline p-2 last:border-r-0
                            {{ $inMonth ? '' : 'bg-mist/40' }}">
                    <p class="text-xs font-medium
                              {{ $day->isToday() ? 'inline-grid h-5 w-5 place-items-center rounded-full bg-brand-action text-white'
                                 : ($inMonth ? 'text-ink-soft' : 'text-ink-muted') }}">
                        {{ $day->day }}
                    </p>

                    @foreach ($dayEvents as $event)
                        <a href="{{ route('admin.program.show', $event) }}"
                           class="mt-1.5 block rounded-lg bg-brand-50 px-2 py-1 text-[0.7rem] leading-tight
                                  text-brand-800 transition hover:bg-brand-100">
                            <span class="block font-medium">{{ $event->starts_at->format('g:ia') }}</span>
                            <span class="block truncate">{{ $event->title }}</span>
                        </a>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.admin>
