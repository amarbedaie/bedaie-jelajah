@props(['error' => false, 'icon' => null])

@php
    $classes = 'tap-target w-full rounded-xl border bg-surface px-4 py-2.5 text-base text-ink '
        . 'placeholder:text-ink-muted transition focus:outline-none focus:ring-3 '
        . ($error
            ? 'border-danger focus:border-danger focus:ring-danger/45 '
            : 'border-control-line focus:border-clay-600 focus:ring-clay-400/45 ')
        . ($icon ? 'pl-11 ' : '');

    $ariaId = $attributes->get('id');
    $aria = $error && $ariaId
        ? ['aria-invalid' => 'true', 'aria-describedby' => $ariaId.'-error']
        : ($error ? ['aria-invalid' => 'true'] : []);
@endphp

@if ($icon)
    <div class="relative">
        <x-ui.icon :name="$icon" class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-ink-muted pointer-events-none" />
        <input {{ $attributes->merge(['class' => $classes])->merge($aria) }} />
    </div>
@else
    <input {{ $attributes->merge(['class' => $classes])->merge($aria) }} />
@endif
