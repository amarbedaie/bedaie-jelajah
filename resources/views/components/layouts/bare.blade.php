@props(['title' => null, 'dark' => false])

<!DOCTYPE html>
<html lang="ms">
<head>
    @include('partials.head', ['title' => $title])
</head>
<body class="min-h-screen antialiased {{ $dark ? 'bg-char-900' : 'bg-cream' }}">
    {{ $slot }}
    @livewireScripts
</body>
</html>
