@props(['variant' => 'info', 'title' => null, 'icon' => null])

@php
    $styles = [
        'info'    => ['bg-brand-50 border-brand-200 text-brand-900', 'text-brand-600', 'info'],
        'success' => ['bg-success-soft border-[#B9EBD5] text-[#0A5537]', 'text-success', 'check-circle'],
        'warning' => ['bg-warning-soft border-[#F6DFB4] text-[#7A4E06]', 'text-warning', 'alert'],
        'danger'  => ['bg-danger-soft border-[#F5C3C5] text-[#8C1A1E]', 'text-danger', 'x-circle'],
        'neutral' => ['bg-mist border-hairline text-ink', 'text-ink-soft', 'info'],
    ];
    [$box, $iconColor, $defaultIcon] = $styles[$variant] ?? $styles['info'];
@endphp

<div role="{{ in_array($variant, ['danger','warning']) ? 'alert' : 'status' }}"
     {{ $attributes->merge(['class' => "flex items-start gap-3 rounded-xl border p-4 $box"]) }}>
    <x-ui.icon :name="$icon ?? $defaultIcon" class="w-5 h-5 mt-0.5 shrink-0 {{ $iconColor }}" />
    <div class="min-w-0 flex-1 text-sm">
        @if ($title)
            <p class="font-semibold">{{ $title }}</p>
        @endif
        <div class="{{ $title ? 'mt-0.5' : '' }}">{{ $slot }}</div>
    </div>
</div>
