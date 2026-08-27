@props(['variant' => 'info', 'title' => null, 'icon' => null])

@php
    $styles = [
        'info'    => ['bg-clay-50 border-clay-200 text-clay-900', 'text-clay-700', 'info'],
        'success' => ['bg-clay-50 border-clay-300 text-ink', 'text-clay-700', 'check-circle'],
        'warning' => ['bg-mist border-control-line/35 text-ink', 'text-ink-soft', 'alert'],
        'danger'  => ['bg-alert-soft border-alert-line text-alert', 'text-alert', 'x-circle'],
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
