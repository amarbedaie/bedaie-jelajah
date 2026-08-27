@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'icon' => null,
    'iconAfter' => null,
    'type' => 'button',
    'block' => false,
])

@php
    // Bentuk pil ditinggalkan. Kertas mempunyai tepi; butang di sini
    // ialah blok kecil dengan radius yang sepadan dengan kad.
    $base = 'tap-target inline-flex items-center justify-center gap-2 font-medium rounded-lg '
          . 'transition-colors duration-150 disabled:opacity-45 disabled:pointer-events-none '
          . 'focus-visible:outline-2 focus-visible:outline-offset-2 whitespace-nowrap';

    $variants = [
        'primary'   => 'bg-clay-600 text-white hover:bg-clay-700 focus-visible:outline-clay-700',
        // Hanya untuk permukaan arang yang disanksi (bar sisi, pengimbas).
        'navy'      => 'bg-ink text-white hover:bg-char-800 focus-visible:outline-clay-400',
        'secondary' => 'bg-clay-50 text-clay-700 hover:bg-clay-100 focus-visible:outline-clay-600',
        'outline'   => 'border border-control-line/60 bg-transparent text-ink hover:border-ink hover:bg-ink/[0.04] focus-visible:outline-clay-600',
        'ghost'     => 'text-ink-soft hover:bg-ink/[0.05] hover:text-ink focus-visible:outline-clay-600',
        // WhatsApp tidak lagi membawa hijaunya sendiri — ikon yang
        // mengenalkannya, dan hijau itu satu-satunya benda paling kuat
        // pada halaman kertas.
        'whatsapp'  => 'bg-clay-600 text-white hover:bg-clay-700 focus-visible:outline-clay-700',
        'success'   => 'bg-ink text-cream hover:bg-char-800 focus-visible:outline-clay-600',
        'danger'    => 'bg-alert text-white hover:brightness-110 focus-visible:outline-alert',
        'danger-soft'=> 'bg-alert-soft text-alert hover:bg-alert hover:text-white focus-visible:outline-alert',
        'white'     => 'bg-surface text-ink border border-hairline hover:bg-clay-50 hover:border-clay-300 focus-visible:outline-ink',
    ];

    $sizes = [
        'sm' => 'text-sm px-3.5 py-2',
        'md' => 'text-[0.9375rem] px-4.5 py-2.5',
        'lg' => 'text-base px-6 py-3',
    ];

    $classes = implode(' ', [
        $base,
        $variants[$variant] ?? $variants['primary'],
        $sizes[$size] ?? $sizes['md'],
        $block ? 'w-full' : '',
    ]);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon) <x-ui.icon :name="$icon" class="w-[1.15em] h-[1.15em] shrink-0" /> @endif
        <span>{{ $slot }}</span>
        @if ($iconAfter) <x-ui.icon :name="$iconAfter" class="w-[1.15em] h-[1.15em] shrink-0" /> @endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon) <x-ui.icon :name="$icon" class="w-[1.15em] h-[1.15em] shrink-0" /> @endif
        <span>{{ $slot }}</span>
        @if ($iconAfter) <x-ui.icon :name="$iconAfter" class="w-[1.15em] h-[1.15em] shrink-0" /> @endif
    </button>
@endif
