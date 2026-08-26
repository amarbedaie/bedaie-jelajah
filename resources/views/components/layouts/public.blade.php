@props(['title' => null, 'description' => null, 'ogImage' => null, 'transparentHeader' => false])

<!DOCTYPE html>
<html lang="ms" class="scroll-pt-24">
<head>
    @include('partials.head', ['title' => $title, 'description' => $description, 'ogImage' => $ogImage])
</head>
<body class="min-h-screen bg-cream antialiased">
    <a href="#kandungan" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[60]
              focus:rounded-full focus:bg-navy-900 focus:px-5 focus:py-2.5 focus:text-white">
        Langkau ke kandungan utama
    </a>

    @include('partials.public-header', ['transparent' => $transparentHeader])

    <main id="kandungan">
        {{ $slot }}
    </main>

    @include('partials.public-footer')

    @livewireScripts
</body>
</html>
