{{--
    Logo rasmi BeDaie (public/brand/bedaie-logo.png), dimuat turun
    daripada bedaie.com.my dan dihos sendiri — tidak pernah dihotlink.

    Tanda itu membawa gradien lima warna jenama. Itu satu-satunya tempat
    gradien penuh muncul dalam sistem ini; antara muka kekal ungu.
--}}
@props(['variant' => 'full', 'light' => false, 'class' => ''])

@php
    // SVG diutamakan jika suatu hari fail vektor tersedia.
    $svg = public_path('brand/bedaie-logo.svg');
    $png = public_path('brand/bedaie-logo.png');
    $officialFile = is_file($svg) ? 'brand/bedaie-logo.svg' : (is_file($png) ? 'brand/bedaie-logo.png' : null);
    $textColor = $light ? 'text-white' : 'text-ink';
    $subColor  = $light ? 'text-brand-300' : 'text-brand-600';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5 ' . $class]) }}>
    @if ($officialFile)
        <img src="{{ asset($officialFile) }}" alt="BeDaie" class="h-9 w-auto"
             width="253" height="160" />
    @else
        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg {{ $light ? 'bg-white/15' : 'bg-brand-600' }}"
              data-logo="bedaie-logo-placeholder">
            <svg viewBox="0 0 64 64" class="h-6 w-6" fill="none" aria-hidden="true">
                <g stroke="#FFFFFF" stroke-width="3.4" stroke-linejoin="round">
                    <path d="M32 12 52 32 32 52 12 32Z" stroke-opacity="0.5"/>
                    <path d="M32 20 44 32 32 44 20 32Z"/>
                </g>
                <circle cx="32" cy="32" r="4" fill="#FFFFFF"/>
            </svg>
        </span>
    @endif

    @if ($variant !== 'mark')
        <span class="flex flex-col leading-none">
            <span class="text-lg font-semibold tracking-tight {{ $textColor }}">BeDaie</span>
            <span class="mt-0.5 text-[0.68rem] font-semibold uppercase tracking-[0.2em] {{ $subColor }}">Jelajah</span>
        </span>
    @endif
</span>
