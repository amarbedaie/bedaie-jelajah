@props(['error' => false, 'rows' => 4])

<textarea rows="{{ $rows }}" {{ $attributes->merge([
    'class' => 'w-full rounded-xl border bg-surface px-4 py-3 text-base text-ink placeholder:text-ink-muted '
        . 'transition focus:outline-none focus:ring-3 '
        . ($error
            ? 'border-danger focus:border-danger-ink focus:ring-danger/45'
            : 'border-control-line focus:border-brand-600 focus:ring-brand-500/45')
]) }}>{{ $slot }}</textarea>
