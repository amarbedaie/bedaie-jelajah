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
    $base = 'tap-target inline-flex items-center justify-center gap-2 font-medium rounded-full '
          . 'transition-all duration-200 disabled:opacity-50 disabled:pointer-events-none '
          . 'focus-visible:outline-2 focus-visible:outline-offset-2 whitespace-nowrap';

    $variants = [
        'primary'   => 'bg-brand-action text-white shadow-brand hover:bg-brand-action-hover hover:-translate-y-px active:translate-y-0 focus-visible:outline-brand-700',
        'navy'      => 'bg-navy-900 text-white hover:bg-navy-700 hover:-translate-y-px focus-visible:outline-brand-300',
        'secondary' => 'bg-brand-50 text-brand-700 hover:bg-brand-100 focus-visible:outline-brand-500',
        'outline'   => 'border border-hairline bg-surface text-navy-900 hover:border-brand-300 hover:bg-brand-50 focus-visible:outline-brand-500',
        'ghost'     => 'text-ink-soft hover:bg-mist hover:text-navy-900 focus-visible:outline-brand-500',
        'whatsapp'  => 'bg-whatsapp-ink text-white hover:brightness-110 hover:-translate-y-px focus-visible:outline-whatsapp-ink',
        'success'   => 'bg-success-ink text-white hover:brightness-110 focus-visible:outline-success-ink',
        'danger'    => 'bg-danger-ink text-white hover:brightness-110 focus-visible:outline-danger-ink',
        'danger-soft'=> 'bg-danger-soft text-danger-ink hover:bg-danger-ink hover:text-white focus-visible:outline-danger-ink',
        'glass'     => 'border border-white/25 bg-white/10 text-white backdrop-blur hover:bg-white/18 hover:-translate-y-px focus-visible:outline-white',
        'white'     => 'bg-white text-navy-900 hover:bg-brand-50 hover:-translate-y-px shadow-soft focus-visible:outline-navy-900',
    ];

    $sizes = [
        'sm' => 'text-sm px-4 py-2',
        'md' => 'text-sm px-5 py-2.5 sm:text-base',
        'lg' => 'text-base px-7 py-3.5',
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
