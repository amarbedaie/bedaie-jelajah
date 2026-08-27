@props(['value' => 0, 'max' => 100, 'label' => null, 'tone' => 'brand', 'showValue' => true])

@php
    $pct = $max > 0 ? min(100, max(0, round($value / $max * 100))) : 0;
    $tones = ['brand' => 'bg-clay-400', 'success' => 'bg-clay-600', 'warning' => 'bg-clay-400', 'navy' => 'bg-char-900'];
@endphp

<div {{ $attributes->merge(['class' => 'space-y-1.5']) }}>
    @if ($label || $showValue)
        <div class="flex items-baseline justify-between gap-3 text-sm">
            @if ($label) <span class="font-medium text-ink">{{ $label }}</span> @endif
            @if ($showValue) <span class="tabular-nums text-ink-muted">{{ $pct }}%</span> @endif
        </div>
    @endif
    <div class="h-2 w-full overflow-hidden rounded-full bg-mist"
         role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"
         @if($label) aria-label="{{ $label }}" @endif>
        <div class="h-full rounded-full transition-[width] duration-500 {{ $tones[$tone] ?? $tones['brand'] }}"
             style="width: {{ $pct }}%"></div>
    </div>
</div>
