@props(['color' => 'grey', 'icon' => null, 'dot' => false])

@php
    $colors = [
        'purple'   => 'bg-brand-50 text-brand-700 ring-brand-200',
        'navy'     => 'bg-navy-50 text-navy-700 ring-navy-100',
        'success'  => 'bg-success-soft text-[#00794A] ring-[#B9EBD5]',
        'warning'  => 'bg-warning-soft text-[#9A6208] ring-[#F6DFB4]',
        'danger'   => 'bg-danger-soft text-[#B02226] ring-[#F5C3C5]',
        'grey'     => 'bg-mist text-ink-soft ring-hairline',
        'white'    => 'bg-white/15 text-white ring-white/25 backdrop-blur',
    ];
    $dotColors = [
        'purple' => 'bg-brand-500', 'navy' => 'bg-navy-700', 'success' => 'bg-success',
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
