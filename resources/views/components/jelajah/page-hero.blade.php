@props(['eyebrow' => null, 'title', 'lead' => null, 'dark' => false])

{{-- Kertas, sentiasa. Prop `dark` dikekalkan supaya panggilan sedia ada
     tidak pecah, tetapi sistem ini tidak lagi mempunyai slab gelap:
     hierarki datang daripada saiz taip dan ruang, bukan warna latar. --}}
<section {{ $attributes->merge(['class' => 'relative overflow-hidden border-b border-hairline']) }}>
    <div class="motif-girih pointer-events-none absolute inset-0 opacity-40" aria-hidden="true"></div>

    <div class="jelajah-container relative py-12 sm:py-16 lg:py-20">
        <div class="max-w-3xl">
            @if ($eyebrow)
                <p class="flex items-center gap-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-clay-700">
                    <span class="h-px w-8 bg-clay-400"></span>
                    {{ $eyebrow }}
                </p>
            @endif

            <h1 class="mt-7 font-display text-[2.25rem] leading-[1.02] text-ink text-pretty sm:text-[3.5rem] lg:text-[4.5rem]">
                {{ $title }}
            </h1>

            @if ($lead)
                <p class="mt-6 max-w-2xl text-lg leading-relaxed text-ink-soft text-pretty">
                    {{ $lead }}
                </p>
            @endif

            @if (trim($slot) !== '')
                <div class="mt-8 flex flex-wrap gap-3">{{ $slot }}</div>
            @endif
        </div>
    </div>
</section>
