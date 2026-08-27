@props(['label', 'value', 'icon' => null, 'hint' => null, 'tone' => 'default'])

@php
    $tones = [
        'default' => 'bg-surface border-hairline',
        'brand'   => 'bg-clay-50 border-clay-200',
        'success' => 'bg-success-soft border-success-line',
        'warning' => 'bg-warning-soft border-warning-line',
        'danger'  => 'bg-danger-soft border-danger-line',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-card border p-4 sm:p-5 ' . ($tones[$tone] ?? $tones['default'])]) }}>
    <div class="flex items-start justify-between gap-3">
        <p class="text-sm font-medium text-ink-soft">{{ $label }}</p>
        @if ($icon)
            <x-ui.icon :name="$icon" class="w-5 h-5 shrink-0 text-clay-400" />
        @endif
    </div>
    <p class="mt-1.5 text-2xl sm:text-3xl font-semibold tracking-tight text-ink tabular-nums">{{ $value }}</p>
    @if ($hint)
        <p class="mt-1 text-xs text-ink-muted">{{ $hint }}</p>
    @endif
</div>
