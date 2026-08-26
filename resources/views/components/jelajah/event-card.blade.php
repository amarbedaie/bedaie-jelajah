@props(['event', 'compact' => false])

@php
    $seatsLeft = $event->seatsLeft();
    $isPast = $event->hasEnded();
@endphp

<article class="group relative flex h-full flex-col overflow-hidden rounded-card border border-hairline
                bg-surface shadow-soft transition-all duration-200 hover:-translate-y-0.5
                hover:border-brand-200 hover:shadow-lift">
    <a href="{{ $event->publicUrl() }}" class="relative block aspect-[16/10] overflow-hidden bg-navy-900">
        @if ($event->heroUrl())
            <img src="{{ $event->heroUrl() }}" alt="{{ $event->title }}" loading="lazy"
                 width="1080" height="608"
                 class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
        @else
            {{-- Fallback berjenama apabila poster belum dimuat naik --}}
            <div class="motif-girih-dark absolute inset-0 opacity-60" aria-hidden="true"></div>
            <div class="absolute inset-0 bg-gradient-to-br from-brand-600/70 to-navy-900" aria-hidden="true"></div>
            <div class="relative flex h-full flex-col justify-end p-5">
                <p class="font-display text-xl leading-tight text-white text-pretty">{{ $event->theme ?? $event->title }}</p>
                <p class="mt-1 text-xs font-medium uppercase tracking-[0.16em] text-brand-200">
                    {{ $event->category?->name }}
                </p>
            </div>
        @endif

        <div class="absolute left-3 top-3 flex flex-wrap gap-1.5">
            @if ($isPast)
                <x-ui.badge color="white">Selesai</x-ui.badge>
            @elseif ($event->isFull())
                <x-ui.badge color="white">Penuh</x-ui.badge>
            @elseif ($seatsLeft !== null && $seatsLeft <= 20)
                <x-ui.badge color="white">{{ $seatsLeft }} tempat terakhir</x-ui.badge>
            @endif
            <x-ui.badge color="white">{{ $event->priceLabel() }}</x-ui.badge>
        </div>
    </a>

    <div class="flex flex-1 flex-col p-5">
        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-brand-600">
            {{ $event->category?->name ?? 'Jelajah' }}
        </p>

        <h2 class="mt-1.5 text-lg font-semibold leading-snug text-navy-900 text-pretty">
            <a href="{{ $event->publicUrl() }}"
               class="after:absolute after:inset-0 hover:text-brand-700 focus-visible:outline-none">{{ $event->title }}</a>
        </h2>

        <dl class="mt-3 space-y-1.5 text-sm text-ink-soft">
            <div class="flex items-start gap-2">
                <dt class="sr-only">Tarikh</dt>
                <x-ui.icon name="calendar" class="mt-0.5 h-4 w-4 shrink-0 text-ink-muted" />
                <dd>{{ $event->dateLabel() }} &middot; {{ $event->timeLabel() }}</dd>
            </div>
            <div class="flex items-start gap-2">
                <dt class="sr-only">Lokasi</dt>
                <x-ui.icon name="pin" class="mt-0.5 h-4 w-4 shrink-0 text-ink-muted" />
                <dd class="text-pretty">{{ $event->locationLabel() }}</dd>
            </div>
            @if (! $compact && $event->speaker)
                <div class="flex items-start gap-2">
                    <dt class="sr-only">Penceramah</dt>
                    <x-ui.icon name="user" class="mt-0.5 h-4 w-4 shrink-0 text-ink-muted" />
                    <dd>{{ $event->speaker->name }}</dd>
                </div>
            @endif
        </dl>

        <div class="relative z-10 mt-auto pt-4">
            @if ($isPast)
                <x-ui.button :href="$event->publicUrl()" variant="ghost" size="sm" block iconAfter="arrow-right">
                    Lihat Jejak Program
                </x-ui.button>
            @else
                @if ($event->capacity && $seatsLeft !== null)
                    <div class="mb-3">
                        <x-ui.progress :value="$event->fillPercent()" />
                        <p class="mt-1.5 text-xs text-ink-muted">
                            {{ number_format($event->seatsTaken()) }} daftar
                            @if ($seatsLeft > 0)
                                &middot; {{ number_format($seatsLeft) }} tempat lagi
                            @endif
                        </p>
                    </div>
                @endif
                @php
                    $inviteOnly = $event->pricing_mode === \App\Enums\PricingMode::JemputanSahaja;
                @endphp
                <x-ui.button :href="$event->publicUrl()"
                             :variant="$inviteOnly ? 'outline' : 'primary'" size="sm" block>
                    @if ($inviteOnly)
                        Perlu Kod Jemputan
                    @elseif ($event->isFull())
                        Sertai Senarai Menunggu
                    @else
                        Daftar Sekarang
                    @endif
                </x-ui.button>
            @endif
        </div>
    </div>
</article>
