@props(['eyebrow' => null, 'title', 'lead' => null, 'dark' => true])

<section {{ $attributes->merge(['class' => 'relative overflow-hidden ' . ($dark ? 'bg-navy-900' : 'bg-brand-50')]) }}>
    @if ($dark)
        <div class="motif-girih-dark absolute inset-0 opacity-70" aria-hidden="true"></div>
        <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-brand-500/25 blur-3xl" aria-hidden="true"></div>
    @else
        <div class="motif-girih absolute inset-0 opacity-60" aria-hidden="true"></div>
    @endif

    <div class="jelajah-container relative py-14 sm:py-20">
        <div class="max-w-3xl">
            @if ($eyebrow)
                <p class="text-xs font-semibold uppercase tracking-[0.2em] {{ $dark ? 'text-brand-300' : 'text-brand-600' }}">
                    {{ $eyebrow }}
                </p>
            @endif
            <h1 class="mt-3 font-display text-4xl leading-[1.1] tracking-tight text-pretty sm:text-5xl
                       {{ $dark ? 'text-white' : 'text-navy-900' }}">
                {{ $title }}
            </h1>
            @if ($lead)
                <p class="mt-5 max-w-2xl text-lg leading-relaxed text-pretty {{ $dark ? 'text-white/70' : 'text-ink-soft' }}">
                    {{ $lead }}
                </p>
            @endif
            @if (trim($slot) !== '')
                <div class="mt-8 flex flex-wrap gap-3">{{ $slot }}</div>
            @endif
        </div>
    </div>
</section>
