@props(['error' => false, 'rows' => 4])

@php
    $classes = 'w-full rounded-xl border bg-surface px-4 py-3 text-base text-ink placeholder:text-ink-muted '
        . 'transition focus:outline-none focus:ring-3 '
        . ($error
            ? 'border-alert focus:border-alert focus:ring-alert/45'
            : 'border-control-line focus:border-clay-600 focus:ring-clay-400/45');

    $ariaId = $attributes->get('id');
    $aria = $error && $ariaId
        ? ['aria-invalid' => 'true', 'aria-describedby' => $ariaId.'-error']
        : ($error ? ['aria-invalid' => 'true'] : []);
@endphp

<textarea rows="{{ $rows }}" {{ $attributes->merge(['class' => $classes])->merge($aria) }}>{{ $slot }}</textarea>
