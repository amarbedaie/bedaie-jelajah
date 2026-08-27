@props(['color' => 'grey', 'icon' => null, 'dot' => false])

@php
    $colors = [
        'purple'   => 'bg-clay-50 text-clay-700 ring-clay-200',
        'navy'     => 'bg-char-100 text-char-800 ring-char-200',
        'success'  => 'bg-success-soft text-success ring-success-line',
        'warning'  => 'bg-warning-soft text-warning ring-warning-line',
        'danger'   => 'bg-danger-soft text-danger ring-danger-line',
        'grey'     => 'bg-mist text-ink-soft ring-hairline',
        'white'    => 'bg-surface text-ink ring-hairline shadow-soft',
    ];
    $dotColors = [
        'purple' => 'bg-clay-400', 'navy' => 'bg-char-800', 'success' => 'bg-success',
        'warning' => 'bg-warning', 'danger' => 'bg-danger', 'grey' => 'bg-ink-muted', 'white' => 'bg-white',
    ];
@endphp

<span {{ $attributes->merge([
    'class' => 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset '
        . ($colors[$color] ?? $colors['grey'])
]) }}>
    @if ($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotColors[$color] ?? $dotColors['grey'] }}"></span>
    @elseif ($icon)
        <x-ui.icon :name="$icon" class="w-3.5 h-3.5" />
    @endif
    {{ $slot }}
</span>
