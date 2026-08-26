@props(['error' => false, 'icon' => null])

@php
    $classes = 'tap-target w-full rounded-xl border bg-surface px-4 py-2.5 text-base text-ink '
        . 'placeholder:text-ink-muted transition focus:outline-none focus:ring-4 '
        . ($error
            ? 'border-danger focus:border-danger focus:ring-danger/15 '
            : 'border-hairline focus:border-brand-400 focus:ring-brand-500/15 ')
        . ($icon ? 'pl-11 ' : '');
@endphp

@if ($icon)
    <div class="relative">
        <x-ui.icon :name="$icon" class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-ink-muted pointer-events-none" />
        <input {{ $attributes->merge(['class' => $classes]) }} />
    </div>
@else
    <input {{ $attributes->merge(['class' => $classes]) }} />
@endif
