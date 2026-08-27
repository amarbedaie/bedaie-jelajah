@props(['label', 'value', 'icon' => null, 'hint' => null, 'tone' => 'default'])

@php
    // Nombor ialah angka, bukan kad. Nada `default` menanggalkan chrome
    // sepenuhnya dan bergantung pada garis rambut — sama seperti kaunter
    // impak di halaman utama, yang sudah menjadi rujukan sistem ini.
    $tones = [
        'default' => 'border-t border-hairline pt-4',
        'brand'   => 'rounded-card border border-clay-200 bg-clay-50 p-4 sm:p-5',
        'success' => 'rounded-card border border-clay-300 bg-clay-50 p-4 sm:p-5',
        'warning' => 'rounded-card border border-control-line/35 bg-mist p-4 sm:p-5',
        'danger'  => 'rounded-card border border-alert-line bg-alert-soft p-4 sm:p-5',
    ];
    $bare = $tone === 'default';
@endphp

<div {{ $attributes->merge(['class' => $tones[$tone] ?? $tones['default']]) }}>
    <p class="font-display text-4xl leading-none text-ink tabular-nums sm:text-[2.75rem]">{{ $value }}</p>

    <div class="mt-2.5 flex items-start justify-between gap-3">
        <p class="text-sm leading-snug text-ink-muted text-pretty">{{ $label }}</p>
        @if ($icon && ! $bare)
            <x-ui.icon :name="$icon" class="h-5 w-5 shrink-0 text-clay-500" />
        @endif
    </div>

    @if ($hint)
        <p class="mt-1.5 text-xs text-ink-muted">{{ $hint }}</p>
    @endif
</div>
