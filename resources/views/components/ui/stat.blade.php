@props(['label', 'value', 'icon' => null, 'hint' => null, 'tone' => 'default'])

@php
    $tones = [
        'default' => 'bg-surface border-hairline',
        'brand'   => 'bg-brand-50 border-brand-200',
        'success' => 'bg-success-soft border-[#B9EBD5]',
        'warning' => 'bg-warning-soft border-[#F6DFB4]',
        'danger'  => 'bg-danger-soft border-[#F5C3C5]',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-[--radius-card] border p-4 sm:p-5 ' . ($tones[$tone] ?? $tones['default'])]) }}>
    <div class="flex items-start justify-between gap-3">
        <p class="text-sm font-medium text-ink-soft">{{ $label }}</p>
        @if ($icon)
            <x-ui.icon :name="$icon" class="w-5 h-5 shrink-0 text-brand-500" />
        @endif
    </div>
    <p class="mt-1.5 text-2xl sm:text-3xl font-semibold tracking-tight text-navy-900 tabular-nums">{{ $value }}</p>
    @if ($hint)
        <p class="mt-1 text-xs text-ink-muted">{{ $hint }}</p>
    @endif
</div>
