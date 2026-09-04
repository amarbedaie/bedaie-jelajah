@props(['color' => 'quiet', 'icon' => null, 'dot' => false])

@php
    // Status dibawa oleh BENTUK, bukan rona. Enam bentuk yang boleh
    // dibezakan sepintas lalu, dibina daripada tiga warna sahaja —
    // senyap, bergaris, lembut, tebal, pejal, dan satu isyarat.
    $forms = [
        // quiet dan line ialah pasangan paling rapat, jadi ia dipisahkan
        // oleh bentuk: quiet ialah isian tanpa cincin, line ialah cincin
        // tanpa isian.
        'quiet'  => 'bg-mist text-ink-muted ring-transparent',
        'line'   => 'bg-transparent text-ink ring-control-line/70',
        'edge'   => 'bg-transparent text-brand-700 ring-brand-400',
        'soft'   => 'bg-brand-50 text-brand-700 ring-brand-200',
        'strong' => 'bg-brand-600 text-white ring-brand-600',
        'solid'  => 'bg-ink text-cream ring-ink',
        // Teal daripada logo, dikhaskan untuk keadaan yang sedang berlaku
        // sekarang — satu-satunya rona selain ungu dalam antara muka.
        'live'   => 'bg-teal text-white ring-teal',
        'alert'  => 'bg-alert-soft text-alert ring-alert-line',
        'paper'  => 'bg-surface text-ink ring-hairline shadow-soft',
    ];

    // Nama lama dipetakan supaya panggilan sedia ada tidak pecah.
    $aliases = [
        'grey' => 'quiet', 'slate' => 'line', 'navy' => 'line',
        'purple' => 'soft', 'clay' => 'strong',
        'success' => 'solid', 'warning' => 'soft',
        'danger' => 'alert', 'white' => 'paper',
    ];

    $form = $aliases[$color] ?? $color;

    $dots = [
        'quiet' => 'bg-ink-muted', 'line' => 'bg-ink-soft',
        'edge' => 'bg-brand-500', 'soft' => 'bg-brand-500',
        'strong' => 'bg-white', 'solid' => 'bg-cream', 'live' => 'bg-white', 'alert' => 'bg-alert',
        'paper' => 'bg-brand-500',
    ];
@endphp

<span {{ $attributes->merge([
    'class' => 'inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 '
        . 'text-xs font-medium ring-1 ring-inset '
        . ($forms[$form] ?? $forms['quiet'])
]) }}>
    @if ($dot)
        <span class="h-1.5 w-1.5 rounded-full {{ $dots[$form] ?? $dots['quiet'] }}"></span>
    @elseif ($icon)
        <x-ui.icon :name="$icon" class="h-3.5 w-3.5" />
    @endif
    {{ $slot }}
</span>
