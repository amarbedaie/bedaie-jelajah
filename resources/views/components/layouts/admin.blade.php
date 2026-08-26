@props(['title' => null, 'heading' => null, 'subheading' => null])

@php
    $user = auth()->user();

    $groups = [
        'Operasi' => [
            ['label' => 'Dashboard',        'route' => 'admin.dashboard',   'icon' => 'chart'],
            ['label' => 'Permohonan',       'route' => 'admin.permohonan',  'icon' => 'inbox'],
            ['label' => 'Sasaran Jelajah',  'route' => 'admin.sasaran',     'icon' => 'pin'],
            ['label' => 'Program',          'route' => 'admin.program',     'icon' => 'calendar'],
            ['label' => 'Kalendar',         'route' => 'admin.kalendar',    'icon' => 'calendar'],
        ],
        'Manusia' => [
            ['label' => 'Penggerak',        'route' => 'admin.penggerak',   'icon' => 'map'],
            ['label' => 'Peserta',          'route' => 'admin.peserta',     'icon' => 'users'],
            ['label' => 'Kehadiran',        'route' => 'admin.kehadiran',   'icon' => 'qr'],
            ['label' => 'Sijil',            'route' => 'admin.sijil',       'icon' => 'certificate'],
            ['label' => 'Pembayaran',       'route' => 'admin.pembayaran',  'icon' => 'ticket'],
        ],
        'Jangkauan' => [
            ['label' => 'Negeri & Daerah',  'route' => 'admin.negeri',      'icon' => 'pin'],
            ['label' => 'Permintaan Kawasan','route' => 'admin.permintaan', 'icon' => 'heart'],
            ['label' => 'Penceramah',       'route' => 'admin.penceramah',  'icon' => 'user'],
            ['label' => 'Kategori Program', 'route' => 'admin.kategori',    'icon' => 'list'],
        ],
        'Kandungan' => [
            ['label' => 'Galeri & Testimoni','route' => 'admin.galeri',     'icon' => 'image'],
            ['label' => 'Rakan & Penaja',   'route' => 'admin.rakan',       'icon' => 'handshake'],
            ['label' => 'Laporan',          'route' => 'admin.laporan',     'icon' => 'chart'],
            ['label' => 'Kandungan Website','route' => 'admin.kandungan',   'icon' => 'globe'],
        ],
        'Sistem' => [
            ['label' => 'Template Notifikasi','route' => 'admin.template',  'icon' => 'mail'],
            ['label' => 'Tetapan',          'route' => 'admin.tetapan',     'icon' => 'settings'],
        ],
    ];
@endphp

<!DOCTYPE html>
<html lang="ms">
<head>
    @include('partials.head', ['title' => $title ? $title.' — Admin' : 'Panel Admin'])
</head>
<body class="min-h-screen bg-cream antialiased" x-data="{ sidebar: false }">
    <a href="#kandungan" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[60]
              focus:rounded-full focus:bg-navy-900 focus:px-5 focus:py-2.5 focus:text-white">
        Langkau ke kandungan
    </a>

    {{-- Sidebar --}}
    <div x-show="sidebar" x-cloak x-transition.opacity x-on:click="sidebar = false"
         class="fixed inset-0 z-40 bg-navy-900/50 backdrop-blur-sm lg:hidden" aria-hidden="true"></div>

    <aside class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col bg-navy-900 text-white/70 transition-transform duration-300
                  lg:translate-x-0"
           x-bind:class="sidebar ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           aria-label="Navigasi admin">
        <div class="flex h-16 shrink-0 items-center justify-between border-b border-white/10 px-5">
            <a href="{{ route('admin.dashboard') }}" class="rounded-xl"><x-brand.logo :light="true" /></a>
            <button type="button" x-on:click="sidebar = false"
                    class="tap-target -mr-2 grid place-items-center rounded-xl text-white/60 hover:bg-white/10 hover:text-white lg:hidden"
                    aria-label="Tutup menu">
                <x-ui.icon name="x" class="h-5 w-5" />
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 py-4">
            @foreach ($groups as $group => $items)
                <p class="px-3 pb-1.5 pt-4 text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-white/35 first:pt-0">
                    {{ $group }}
                </p>
                <ul class="space-y-0.5">
                    @foreach ($items as $item)
                        @php $active = request()->routeIs($item['route']) || request()->routeIs($item['route'].'.*'); @endphp
                        <li>
                            <a href="{{ route($item['route']) }}"
                               @if($active) aria-current="page" @endif
                               class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition
                                      {{ $active ? 'bg-brand-500 font-medium text-white shadow-brand' : 'hover:bg-white/8 hover:text-white' }}">
                                <x-ui.icon :name="$item['icon']" class="h-4 w-4 shrink-0" />
                                <span class="truncate">{{ $item['label'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endforeach
        </nav>

        <div class="shrink-0 border-t border-white/10 p-3">
            <div class="flex items-center gap-3 rounded-xl px-2 py-2">
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-brand-500 text-sm font-semibold text-white">
                    {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                </span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-white">{{ $user->name }}</p>
                    <p class="truncate text-xs text-white/50">{{ $user->email }}</p>
                </div>
            </div>
            <div class="mt-1 grid gap-0.5">
                <a href="{{ route('home') }}" target="_blank" rel="noopener"
                   class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm transition hover:bg-white/8 hover:text-white">
                    <x-ui.icon name="external" class="h-4 w-4" /> Lihat Laman Awam
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-left text-sm text-red-300 transition hover:bg-white/8">
                        <x-ui.icon name="logout" class="h-4 w-4" /> Log Keluar
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Kandungan --}}
    <div class="lg:pl-72">
        <header class="sticky top-0 z-30 border-b border-hairline bg-surface/95 backdrop-blur-md">
            <div class="flex h-16 items-center gap-3 px-4 sm:px-6">
                <button type="button" x-on:click="sidebar = true"
                        class="tap-target -ml-2 grid place-items-center rounded-xl text-navy-900 hover:bg-mist lg:hidden"
                        aria-label="Buka menu">
                    <x-ui.icon name="menu" class="h-6 w-6" />
                </button>

                <div class="min-w-0 flex-1">
                    @if ($heading)
                        <h1 class="truncate text-base font-semibold text-navy-900 sm:text-lg">{{ $heading }}</h1>
                    @endif
                </div>

                <livewire:admin.global-search />
            </div>
        </header>

        <main id="kandungan" class="px-4 py-6 sm:px-6 sm:py-8">
            @if ($subheading)
                <p class="mb-5 text-ink-soft text-pretty">{{ $subheading }}</p>
            @endif

            @include('partials.flash')

            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>
