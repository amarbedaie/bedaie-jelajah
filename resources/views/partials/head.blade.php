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

<meta name="theme-color" content="#8875FF">
<link rel="icon" href="{{ asset('brand/bedaie-logo-placeholder.svg') }}" type="image/svg+xml">
<link rel="manifest" href="{{ route('pwa.manifest') }}">
<link rel="apple-touch-icon" href="{{ asset('brand/bedaie-logo-placeholder.svg') }}">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;1,500&display=swap" rel="stylesheet">

@vite(['resources/css/app.css', 'resources/js/app.js'])
@livewireStyles
