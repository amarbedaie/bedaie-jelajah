@props(['variant' => 'info', 'title' => null, 'icon' => null])

@php
    $styles = [
        'info'    => ['bg-clay-50 border-clay-200 text-clay-900', 'text-clay-700', 'info'],
        'success' => ['bg-success-soft border-success-line text-success', 'text-success', 'check-circle'],
        'warning' => ['bg-warning-soft border-warning-line text-warning', 'text-warning', 'alert'],
        'danger'  => ['bg-danger-soft border-danger-line text-danger', 'text-danger', 'x-circle'],
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
