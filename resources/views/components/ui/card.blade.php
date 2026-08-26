@props(['padded' => true, 'hover' => false, 'as' => 'div'])

<{{ $as }} {{ $attributes->merge([
    'class' => 'bg-surface border border-hairline rounded-[--radius-card] shadow-soft '
        . ($padded ? 'p-5 sm:p-6 ' : '')
        . ($hover ? 'transition-all duration-200 hover:shadow-lift hover:-translate-y-0.5 hover:border-brand-200 ' : '')
]) }}>
    {{ $slot }}
</{{ $as }}>
