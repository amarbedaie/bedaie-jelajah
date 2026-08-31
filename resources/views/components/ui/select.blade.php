@props(['error' => false])

@php
    $classes = 'tap-target w-full appearance-none rounded-xl border bg-surface px-4 py-2.5 pr-10 text-base text-ink '
        . 'transition focus:outline-none focus:ring-3 '
        . ($error
            ? 'border-alert focus:border-alert focus:ring-alert/45'
            : 'border-control-line focus:border-brand-600 focus:ring-brand-400/45');

    $ariaId = $attributes->get('id');
    $aria = $error && $ariaId
        ? ['aria-invalid' => 'true', 'aria-describedby' => $ariaId.'-error']
        : ($error ? ['aria-invalid' => 'true'] : []);
@endphp

<div class="relative">
    <select {{ $attributes->merge(['class' => $classes])->merge($aria) }}>{{ $slot }}</select>
    <x-ui.icon name="chevron-down" class="w-4 h-4 absolute right-4 top-1/2 -translate-y-1/2 text-ink-muted pointer-events-none" />
</div>
