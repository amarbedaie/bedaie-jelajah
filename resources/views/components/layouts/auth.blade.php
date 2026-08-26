@props(['title' => null, 'heading' => null, 'subheading' => null])

<!DOCTYPE html>
<html lang="ms">
<head>
    @include('partials.head', ['title' => $title])
</head>
<body class="min-h-screen bg-cream antialiased">
    <div class="grid min-h-screen lg:grid-cols-2">
        {{-- Panel jenama --}}
        <div class="relative hidden overflow-hidden bg-navy-900 lg:flex lg:flex-col lg:justify-between lg:p-12">
            <div class="motif-girih-dark absolute inset-0 opacity-70" aria-hidden="true"></div>
            <div class="absolute -left-24 top-1/4 h-80 w-80 rounded-full bg-brand-500/25 blur-3xl" aria-hidden="true"></div>

            <a href="{{ route('home') }}" class="relative rounded-xl"><x-brand.logo :light="true" /></a>

            <div class="relative max-w-md">
                <p class="font-display text-4xl leading-tight text-white">
                    Membawa Ilmu,<br>Menghidupkan Ummah.
                </p>
                <p class="mt-5 leading-relaxed text-white/70">
                    Sertai gerakan yang menghubungkan BeDaie dengan masjid, surau, sekolah,
                    tahfiz dan komuniti di seluruh Malaysia.
                </p>
                <p class="mt-8 inline-flex items-center gap-2 rounded-full bg-white/8 px-4 py-2 text-sm text-brand-300 ring-1 ring-white/10">
                    <x-ui.icon name="home" class="h-4 w-4" /> {{ config('jelajah.motto') }}
                </p>
            </div>

            <p class="relative text-xs text-white/40">&copy; {{ date('Y') }} {{ config('jelajah.org') }}</p>
        </div>

        {{-- Borang --}}
        <div class="flex flex-col justify-center px-5 py-10 sm:px-10 lg:px-16">
            <div class="mx-auto w-full max-w-md">
                <a href="{{ route('home') }}" class="mb-8 inline-block rounded-xl lg:hidden"><x-brand.logo /></a>

                @if ($heading)
                    <h1 class="text-2xl font-semibold tracking-tight text-navy-900 sm:text-3xl">{{ $heading }}</h1>
                @endif
                @if ($subheading)
                    <p class="mt-2 text-ink-soft text-pretty">{{ $subheading }}</p>
                @endif

                <div class="mt-7">
                    @include('partials.flash')
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>

    @livewireScripts
</body>
</html>
