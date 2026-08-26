@php
    $map = [
        'success' => ['success', 'check-circle'],
        'error'   => ['danger', 'x-circle'],
        'warning' => ['warning', 'alert'],
        'status'  => ['info', 'info'],
        'info'    => ['info', 'info'],
    ];
@endphp

@foreach ($map as $key => [$variant, $icon])
    @if (session($key))
        <div x-data="{ show: true }" x-show="show" x-transition class="mb-5">
            <x-ui.alert :variant="$variant" :icon="$icon">
                <div class="flex items-start justify-between gap-3">
                    <span>{{ session($key) }}</span>
                    <button type="button" x-on:click="show = false" class="shrink-0 opacity-60 hover:opacity-100" aria-label="Tutup">
                        <x-ui.icon name="x" class="w-4 h-4" />
                    </button>
                </div>
            </x-ui.alert>
        </div>
    @endif
@endforeach
