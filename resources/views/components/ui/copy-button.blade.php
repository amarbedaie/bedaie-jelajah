@props(['text', 'label' => 'Salin Link', 'variant' => 'outline', 'size' => 'md', 'block' => false])

@php
    // Arahan Blade tidak dikompil di dalam tag komponen <x-...>, jadi label
    // untuk Alpine mesti disiapkan di sini dahulu.
    $copyLabel = addslashes('Salin: '.$label);
@endphp

<div x-data="{ copied: false, copy() {
        navigator.clipboard.writeText(@js($text)).then(() => {
            this.copied = true;
            setTimeout(() => this.copied = false, 2000);
        });
     } }" @class(['w-full' => $block])>
    <x-ui.button :variant="$variant" :size="$size" :block="$block" x-on:click="copy()"
                 x-bind:aria-label="copied ? 'Telah disalin' : '{{ $copyLabel }}'">
        <span x-show="!copied" class="flex items-center gap-2">
            <x-ui.icon name="copy" class="w-[1.15em] h-[1.15em]" />{{ $label }}
        </span>
        <span x-show="copied" x-cloak class="flex items-center gap-2 text-success">
            <x-ui.icon name="check" class="w-[1.15em] h-[1.15em]" />Telah disalin!
        </span>
    </x-ui.button>
</div>
