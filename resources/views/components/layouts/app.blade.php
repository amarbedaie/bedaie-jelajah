@props(['title' => null, 'nav' => 'penggerak', 'heading' => null, 'subheading' => null, 'wide' => false])

@php
    $user = auth()->user();

    $menus = [
        'penggerak' => [
            ['label' => 'Ringkasan',       'route' => 'penggerak.dashboard',   'icon' => 'home'],
            ['label' => 'Permohonan Saya', 'route' => 'penggerak.permohonan',  'icon' => 'clipboard'],
            ['label' => 'Program Saya',    'route' => 'penggerak.program',     'icon' => 'calendar'],
            ['label' => 'Peserta',         'route' => 'penggerak.peserta',     'icon' => 'users'],
            ['label' => 'Sijil & Laporan', 'route' => 'penggerak.sijil',       'icon' => 'certificate'],
            ['label' => 'Panduan',         'route' => 'penggerak.panduan',     'icon' => 'info'],
            ['label' => 'Profil',          'route' => 'penggerak.profil',      'icon' => 'user'],
        ],
        'peserta' => [
            ['label' => 'Pasport Ilmu',   'route' => 'peserta.dashboard', 'icon' => 'book'],
            ['label' => 'Program Saya',   'route' => 'peserta.program',   'icon' => 'ticket'],
            ['label' => 'Sijil Saya',     'route' => 'peserta.sijil',     'icon' => 'certificate'],
            ['label' => 'Profil',         'route' => 'peserta.profil',    'icon' => 'user'],
        ],
    ];

    $items = $menus[$nav] ?? [];
    $unread = $user?->unreadNotifications()->count() ?? 0;
@endphp

<!DOCTYPE html>
<html lang="ms">
<head>
    @include('partials.head', ['title' => $title])
</head>
<body class="min-h-screen bg-cream antialiased">
    <a href="#kandungan" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[60]
              focus:rounded-full focus:bg-navy-900 focus:px-5 focus:py-2.5 focus:text-white">
        Langkau ke kandungan
    </a>

    <header class="sticky top-0 z-40 border-b border-hairline bg-surface/95 backdrop-blur-md">
        <div class="jelajah-container">
            <div class="flex h-16 items-center justify-between gap-4">
                <a href="{{ route('home') }}" class="shrink-0 rounded-xl" aria-label="BeDaie Jelajah">
                    <x-brand.logo />
                </a>

                <div class="flex items-center gap-1.5">
                    <a href="{{ route($nav.'.notifikasi') }}"
                       class="tap-target relative grid place-items-center rounded-xl text-ink-soft transition hover:bg-mist hover:text-navy-900"
                       aria-label="Pemberitahuan{{ $unread ? " ($unread belum dibaca)" : '' }}">
                        <x-ui.icon name="bell" class="h-5 w-5" />
                        @if ($unread)
                            <span class="absolute right-1.5 top-1.5 grid h-4 min-w-4 place-items-center rounded-full
                                         bg-danger px-1 text-[0.6rem] font-bold text-white">{{ min($unread, 9) }}</span>
                        @endif
                    </a>

                    <div x-data="{ open: false }" class="relative">
                        <button type="button" x-on:click="open = !open" x-bind:aria-expanded="open.toString()"
                                class="tap-target flex items-center gap-2 rounded-full border border-hairline bg-surface px-2.5 py-1.5
                                       transition hover:border-brand-300 hover:bg-brand-50">
                            <span class="grid h-7 w-7 place-items-center rounded-full bg-brand-action text-xs font-semibold text-white">
                                {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                            </span>
                            <span class="hidden max-w-28 truncate text-sm font-medium text-navy-900 sm:block">
                                {{ $user->firstName() }}
                            </span>
                            <x-ui.icon name="chevron-down" class="h-4 w-4 text-ink-muted" />
                        </button>

                        <div x-show="open" x-cloak x-on:click.outside="open = false" x-transition.origin.top.right
                             class="absolute right-0 z-50 mt-2 w-60 overflow-hidden rounded-2xl border border-hairline bg-surface shadow-lift">
                            <div class="border-b border-hairline bg-mist/60 px-4 py-3">
                                <p class="truncate text-sm font-semibold text-navy-900">{{ $user->name }}</p>
                                <p class="mt-0.5 text-xs text-ink-muted">{{ $user->role->label() }}</p>
                            </div>
                            <div class="p-1.5">
                                @if ($user->isAdmin())
                                    <a href="{{ route('admin.dashboard') }}"
                                       class="tap-target flex items-center gap-2.5 rounded-xl px-3 text-sm text-navy-900 hover:bg-mist">
                                        <x-ui.icon name="settings" class="h-4 w-4 text-ink-muted" /> Panel Admin
                                    </a>
                                @endif
                                @if ($user->canAccessPenggerak() && $nav !== 'penggerak')
                                    <a href="{{ route('penggerak.dashboard') }}"
                                       class="tap-target flex items-center gap-2.5 rounded-xl px-3 text-sm text-navy-900 hover:bg-mist">
                                        <x-ui.icon name="map" class="h-4 w-4 text-ink-muted" /> Ruang Penggerak
                                    </a>
                                @endif
                                @if ($nav !== 'peserta')
                                    <a href="{{ route('peserta.dashboard') }}"
                                       class="tap-target flex items-center gap-2.5 rounded-xl px-3 text-sm text-navy-900 hover:bg-mist">
                                        <x-ui.icon name="book" class="h-4 w-4 text-ink-muted" /> Pasport Ilmu
                                    </a>
                                @endif
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                            class="tap-target flex w-full items-center gap-2.5 rounded-xl px-3 text-left text-sm text-danger hover:bg-danger-soft">
                                        <x-ui.icon name="logout" class="h-4 w-4" /> Log Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Navigasi mendatar — sama pada mobile & desktop, sengaja minimum --}}
        <nav class="border-t border-hairline" aria-label="Navigasi ruang saya">
            <div class="jelajah-container">
                <div class="no-scrollbar -mx-1 flex gap-1 overflow-x-auto py-2">
                    @foreach ($items as $item)
                        @php $active = request()->routeIs($item['route']) || request()->routeIs($item['route'].'.*'); @endphp
                        <a href="{{ route($item['route']) }}"
                           @if($active) aria-current="page" @endif
                           class="tap-target flex shrink-0 items-center gap-2 rounded-full px-4 text-sm font-medium transition
                                  {{ $active ? 'bg-navy-900 text-white' : 'text-ink-soft hover:bg-mist hover:text-navy-900' }}">
                            <x-ui.icon :name="$item['icon']" class="h-4 w-4" />
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </nav>
    </header>

    <main id="kandungan" class="jelajah-container py-7 sm:py-9 {{ $wide ? '!max-w-[90rem]' : '' }}">
        @if ($heading)
            <div class="mb-6">
                <h1 class="text-2xl font-semibold tracking-tight text-navy-900 sm:text-3xl">{{ $heading }}</h1>
                @if ($subheading)
                    <p class="mt-1.5 text-ink-soft text-pretty">{{ $subheading }}</p>
                @endif
            </div>
        @endif

        @include('partials.flash')

        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
