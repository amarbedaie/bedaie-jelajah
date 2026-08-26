@props(['label' => null, 'for' => null, 'hint' => null, 'error' => null, 'required' => false, 'optional' => false])

<div {{ $attributes->merge(['class' => 'space-y-1.5']) }}>
    @if ($label)
        <label @if($for) for="{{ $for }}" @endif class="block text-sm font-medium text-navy-900">
            {{ $label }}
            @if ($required)
                <span class="text-danger" aria-hidden="true">*</span>
                <span class="sr-only">(wajib)</span>
            @elseif ($optional)
                <span class="font-normal text-ink-muted">(pilihan)</span>
            @endif
        </label>
    @endif

    {{ $slot }}

    @if ($error)
        <p class="flex items-start gap-1.5 text-sm text-danger">
            <x-ui.icon name="alert" class="w-4 h-4 mt-0.5 shrink-0" />
            <span>{{ $error }}</span>
        </p>
    @elseif ($hint)
        <p class="text-sm text-ink-muted">{{ $hint }}</p>
    @endif
</div>
