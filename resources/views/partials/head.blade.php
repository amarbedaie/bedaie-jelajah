<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>{{ $title ?? 'BeDaie Jelajah' }}</title>
<meta name="description" content="{{ $description ?? 'Gerakan ilmu yang menghubungkan BeDaie dengan masjid, surau, sekolah, tahfiz dan komuniti di seluruh Malaysia.' }}">

<meta property="og:site_name" content="BeDaie Jelajah">
<meta property="og:title" content="{{ $title ?? 'BeDaie Jelajah' }}">
<meta property="og:description" content="{{ $description ?? 'Membawa Ilmu, Menghidupkan Ummah.' }}">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
@isset($ogImage)
    <meta property="og:image" content="{{ $ogImage }}">
@endisset
<meta name="twitter:card" content="summary_large_image">

<meta name="theme-color" content="#D97757">
<link rel="icon" href="{{ asset('brand/bedaie-logo-placeholder.svg') }}" type="image/svg+xml">
<link rel="manifest" href="{{ route('pwa.manifest') }}">
<link rel="apple-touch-icon" href="{{ asset('brand/bedaie-logo-placeholder.svg') }}">



{{-- Fon dihos sendiri (resources/css/fonts.css). Dua muka taip yang
     paling awal kelihatan dipramuat supaya tajuk tidak berkelip. --}}
<link rel="preload" as="font" type="font/woff2" href="{{ asset('fonts/SourceSerif4-400-normal.woff2') }}" crossorigin>
<link rel="preload" as="font" type="font/woff2" href="{{ asset('fonts/HankenGrotesk-400-normal.woff2') }}" crossorigin>

@vite(['resources/css/app.css', 'resources/js/app.js'])
@livewireStyles
