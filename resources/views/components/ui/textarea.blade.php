@props(['error' => false, 'rows' => 4])

<textarea rows="{{ $rows }}" {{ $attributes->merge([
    'class' => 'w-full rounded-xl border bg-surface px-4 py-3 text-base text-ink placeholder:text-ink-muted '
        . 'transition focus:outline-none focus:ring-4 '
        . ($error
            ? 'border-danger focus:border-danger focus:ring-danger/15'
            : 'border-hairline focus:border-brand-400 focus:ring-brand-500/15')
]) }}>{{ $slot }}</textarea>
