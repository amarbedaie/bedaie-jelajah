{{-- Pilihan radio/checkbox bergaya kad — sasaran sentuh besar, mesra mobile --}}
@props(['type' => 'radio', 'name', 'value', 'checked' => false, 'label', 'hint' => null, 'icon' => null])

<label class="group relative flex items-start gap-3 rounded-xl border border-hairline bg-surface p-3.5 cursor-pointer
              transition hover:border-clay-300 hover:bg-clay-50/40
              has-[:checked]:border-clay-400 has-[:checked]:bg-clay-50 has-[:checked]:ring-1 has-[:checked]:ring-clay-400
              has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-clay-400 has-[:focus-visible]:ring-offset-2">
    <input type="{{ $type }}" name="{{ $name }}" value="{{ $value }}" @checked($checked)
           {{ $attributes }}
           class="mt-0.5 h-5 w-5 shrink-0 border-hairline text-clay-400 focus:ring-clay-400
                  {{ $type === 'radio' ? 'rounded-full' : 'rounded' }}" />
    <span class="min-w-0 flex-1">
        <span class="flex items-center gap-2 text-sm font-medium text-ink">
            @if ($icon) <x-ui.icon :name="$icon" class="w-4 h-4 text-clay-400" /> @endif
            {{ $label }}
        </span>
        @if ($hint)
            <span class="mt-0.5 block text-sm text-ink-muted">{{ $hint }}</span>
        @endif
    </span>
</label>
