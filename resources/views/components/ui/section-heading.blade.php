@props(['eyebrow' => null, 'title', 'description' => null, 'align' => 'left', 'light' => false,
        'action' => null, 'actionLabel' => 'Lihat Semua'])

@php
    $wrapper = $action
        ? 'flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between'
        : ($align === 'center' ? 'mx-auto max-w-2xl text-center' : 'max-w-2xl');
@endphp

<div {{ $attributes->merge(['class' => $wrapper]) }}>
<div class="{{ $action ? 'max-w-2xl' : '' }}">
    @if ($eyebrow)
        <p class="mb-2.5 flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.14em]
                  {{ $light ? 'text-brand-300' : 'text-brand-700' }} {{ $align === 'center' ? 'justify-center' : '' }}">
            <span class="h-px w-6 {{ $light ? 'bg-brand-300/60' : 'bg-brand-300' }}"></span>
            {{ $eyebrow }}
        </p>
    @endif

    <h2 class="text-2xl sm:text-3xl lg:text-[2.15rem] font-semibold tracking-tight text-balance
               {{ $light ? 'text-white' : 'text-navy-900' }}">
        {{ $title }}
    </h2>

    @if ($description)
        <p class="mt-3 text-base leading-relaxed text-pretty {{ $light ? 'text-white/75' : 'text-ink-soft' }}">
            {{ $description }}
        </p>
    @endif

    @if (trim($slot) !== '')
        <div class="mt-5">{{ $slot }}</div>
    @endif
</div>

@if ($action)
    <a href="{{ $action }}"
       class="tap-target inline-flex shrink-0 items-center gap-1.5 self-start rounded-full px-4 text-sm
              font-medium transition sm:self-auto
              {{ $light ? 'text-brand-300 hover:bg-white/10' : 'text-brand-600 hover:bg-brand-50' }}">
        {{ $actionLabel }}
        <x-ui.icon name="arrow-right" class="h-4 w-4" />
    </a>
@endif
</div>
