@props(['title' => null, 'heading' => null, 'subheading' => null])

<!DOCTYPE html>
<html lang="ms">
<head>
    @include('partials.head', ['title' => $title])
</head>
<body class="min-h-screen bg-surface antialiased">
    <div class="grid min-h-screen lg:grid-cols-2">
        {{-- Panel jenama: kertas dengan tekstur girih, bukan slab gelap. --}}
        <div class="relative hidden overflow-hidden border-r border-hairline bg-cream
                    lg:flex lg:flex-col lg:justify-between lg:p-14">
            <div class="motif-girih absolute inset-0 opacity-50" aria-hidden="true"></div>

            <a href="{{ route('home') }}" class="relative rounded-lg"><x-brand.logo /></a>

            <div class="relative max-w-md">
                <p class="font-display text-[2.5rem] leading-[1.1] text-ink">
                    Membawa Ilmu,<br>Menghidupkan Ummah.
                </p>
                <p class="mt-6 leading-relaxed text-ink-soft text-pretty">
                    Sertai gerakan yang menghubungkan BeDaie dengan masjid, surau, sekolah,
                    tahfiz dan komuniti di seluruh Malaysia.
                </p>
                <p class="mt-9 inline-flex items-center gap-2 border-t border-clay-300 pt-4
                          text-sm font-medium text-clay-700">
                    {{ config('jelajah.motto') }}
                </p>
            </div>

            <p class="relative text-xs text-ink-muted">&copy; {{ date('Y') }} {{ config('jelajah.org') }}</p>
        </div>

        {{-- Borang --}}
        <div class="flex flex-col justify-center px-5 py-10 sm:px-10 lg:px-16">
            <div class="mx-auto w-full max-w-md">
                <a href="{{ route('home') }}" class="mb-8 inline-block rounded-xl lg:hidden"><x-brand.logo /></a>

                @if ($heading)
                    <h1 class="font-display text-3xl leading-tight text-ink sm:text-4xl">{{ $heading }}</h1>
                @endif
                @if ($subheading)
                    <p class="mt-3 leading-relaxed text-ink-soft text-pretty">{{ $subheading }}</p>
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
