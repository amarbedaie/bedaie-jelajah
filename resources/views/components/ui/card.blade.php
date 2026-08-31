@props(['padded' => true, 'hover' => false, 'as' => 'div'])

{{-- Kertas: kad dibezakan oleh sempadan rambut dan ruang, bukan bayang.
     Hover menggelapkan sempadan, ia tidak mengangkat kad. --}}
<{{ $as }} {{ $attributes->merge([
    'class' => 'bg-surface border border-hairline rounded-card '
        . ($padded ? 'p-5 sm:p-6 ' : '')
        . ($hover ? 'transition-colors duration-150 hover:border-brand-300 ' : '')
]) }}>
    {{ $slot }}
</{{ $as }}>
