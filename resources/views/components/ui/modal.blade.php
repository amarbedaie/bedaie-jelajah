@props(['name', 'title' => null, 'maxWidth' => 'max-w-lg'])

<div x-data="{ open: false }"
     x-on:open-modal.window="if ($event.detail === '{{ $name }}' || $event.detail?.name === '{{ $name }}') open = true"
     x-on:close-modal.window="if ($event.detail === '{{ $name }}' || $event.detail?.name === '{{ $name }}') open = false"
     x-on:keydown.escape.window="open = false"
     x-cloak>
    <div x-show="open" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div x-show="open" x-transition.opacity class="absolute inset-0 bg-navy-900/50 backdrop-blur-sm"
             x-on:click="open = false" aria-hidden="true"></div>

        <div x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             class="relative w-full {{ $maxWidth }} max-h-[92vh] overflow-y-auto rounded-t-3xl sm:rounded-card-lg
                    bg-surface shadow-lift"
             role="dialog" aria-modal="true" @if($title) aria-label="{{ $title }}" @endif>
            @if ($title)
                <div class="sticky top-0 flex items-center justify-between gap-4 border-b border-hairline bg-surface px-5 py-4 sm:px-6">
                    <h2 class="text-lg font-semibold text-navy-900">{{ $title }}</h2>
                    <button type="button" x-on:click="open = false"
                            class="tap-target -mr-2 flex items-center justify-center rounded-full text-ink-muted hover:bg-mist hover:text-navy-900"
                            aria-label="Tutup">
                        <x-ui.icon name="x" class="w-5 h-5" />
                    </button>
                </div>
            @endif
            <div class="p-5 sm:p-6">{{ $slot }}</div>
        </div>
    </div>
</div>
