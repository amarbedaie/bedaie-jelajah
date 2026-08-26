{{--
    PLACEHOLDER LOGO — gantikan dengan fail logo rasmi BeDaie.
    Letakkan fail rasmi di public/brand/bedaie-logo.svg; komponen ini
    akan menggunakannya secara automatik sebaik sahaja fail itu wujud.
    Jangan hotlink imej daripada laman lama.
--}}
@props(['variant' => 'full', 'light' => false, 'class' => ''])

@php
    $officialPath = public_path('brand/bedaie-logo.svg');
    $hasOfficial = is_file($officialPath);
    $markColor = $light ? '#FFFFFF' : '#8875FF';
    $textColor = $light ? 'text-white' : 'text-navy-900';
    $subColor  = $light ? 'text-brand-300' : 'text-brand-600';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5 ' . $class]) }}>
    @if ($hasOfficial)
        <img src="{{ asset('brand/bedaie-logo.svg') }}" alt="BeDaie" class="h-9 w-auto" />
    @else
        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl"
              style="background: {{ $light ? 'rgba(255,255,255,0.14)' : '#8875FF' }}"
              data-logo="bedaie-logo-placeholder">
            <svg viewBox="0 0 64 64" class="h-6 w-6" fill="none" aria-hidden="true">
                <g stroke="#FFFFFF" stroke-width="3.4" stroke-linejoin="round">
                    <path d="M32 12 52 32 32 52 12 32Z" stroke-opacity="0.5"/>
                    <path d="M32 20 44 32 32 44 20 32Z"/>
                </g>
                <circle cx="32" cy="32" r="4" fill="#FFFFFF"/>
            </svg>
        </span>
    @endif

    @if ($variant !== 'mark')
        <span class="flex flex-col leading-none">
            <span class="text-lg font-semibold tracking-tight {{ $textColor }}">BeDaie</span>
            <span class="mt-0.5 text-[0.68rem] font-semibold uppercase tracking-[0.2em] {{ $subColor }}">Jelajah</span>
        </span>
    @endif
</span>
