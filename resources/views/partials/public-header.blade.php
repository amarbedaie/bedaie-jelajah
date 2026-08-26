@php
    $transparent = $transparent ?? false;
    $nav = [
        ['label' => 'Utama',        'route' => 'home'],
        ['label' => 'Peta Jelajah', 'route' => 'peta'],
        ['label' => 'Program',      'route' => 'program.index'],
        ['label' => 'Jejak Jelajah','route' => 'jejak'],
        ['label' => 'Tentang',      'route' => 'tentang'],
    ];
@endphp

<header x-data="{ open: false, scrolled: false }"
        x-on:scroll.window="scrolled = window.scrollY > 12"
        class="sticky top-0 z-50 transition-all duration-300"
        x-bind:class="scrolled || open
            ? 'bg-surface/95 backdrop-blur-md border-b border-hairline shadow-soft'
            : '{{ $transparent ? 'bg-transparent' : 'bg-surface border-b border-hairline' }}'">
    <div class="jelajah-container">
        <div class="flex h-[72px] items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="shrink-0 rounded-xl" aria-label="BeDaie Jelajah — Utama">
                <x-brand.logo x-bind:light="false" :light="false" />
            </a>

            {{-- Navigasi desktop --}}
            <nav class="hidden lg:flex items-center gap-1" aria-label="Navigasi utama">
                @foreach ($nav as $item)
                    @php $active = request()->routeIs($item['route']) || request()->routeIs($item['route'].'.*'); @endphp
                    <a href="{{ route($item['route']) }}"
                       @if($active) aria-current="page" @endif
                       class="rounded-full px-4 py-2 text-sm font-medium transition
                              {{ $active ? 'bg-brand-50 text-brand-700' : 'text-ink-soft hover:bg-mist hover:text-navy-900' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="flex items-center gap-2">
                {{-- Tindakan desktop dibungkus: menyembunyikan butang secara langsung
                     gagal kerana kelas asasnya sudah mengandungi inline-flex. --}}
                <div class="hidden items-center gap-2 sm:flex">
                    <x-ui.button :href="route('jemput')" variant="primary" size="sm">
                        Jemput BeDaie
                    </x-ui.button>

                    @auth
                        <x-ui.button :href="route(auth()->user()->homeRoute())" variant="outline" size="sm"
                                     icon="user">
                            {{ auth()->user()->firstName() }}
                        </x-ui.button>
                    @else
                        <a href="{{ route('login') }}"
                           class="tap-target inline-flex items-center rounded-full px-4 py-2 text-sm font-medium
                                  text-ink-soft transition hover:bg-mist hover:text-navy-900">
                            Log Masuk
                        </a>
                    @endauth
                </div>

                <button type="button" x-on:click="open = !open"
                        class="tap-target lg:hidden -mr-2 grid place-items-center rounded-xl text-navy-900 hover:bg-mist"
                        x-bind:aria-expanded="open.toString()" aria-controls="menu-mobil" aria-label="Buka menu">
                    <x-ui.icon name="menu" x-show="!open" class="w-6 h-6" />
                    <x-ui.icon name="x" x-show="open" x-cloak class="w-6 h-6" />
                </button>
            </div>
        </div>
    </div>

    {{-- Menu mobile — ringkas, sasaran sentuh besar --}}
    <div id="menu-mobil" x-show="open" x-cloak x-transition.origin.top
         class="lg:hidden border-t border-hairline bg-surface">
        <nav class="jelajah-container space-y-1 py-4" aria-label="Navigasi mobile">
            @foreach ($nav as $item)
                @php $active = request()->routeIs($item['route']) || request()->routeIs($item['route'].'.*'); @endphp
                <a href="{{ route($item['route']) }}"
                   class="tap-target flex items-center rounded-xl px-4 text-base font-medium
                          {{ $active ? 'bg-brand-50 text-brand-700' : 'text-navy-900 hover:bg-mist' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach

            <div class="grid gap-2.5 pt-3">
                <x-ui.button :href="route('jemput')" variant="primary" block>Jemput BeDaie</x-ui.button>
                @auth
                    <x-ui.button :href="route(auth()->user()->homeRoute())" variant="outline" icon="user" block>
                        Ruang Saya
                    </x-ui.button>
                @else
                    <x-ui.button :href="route('login')" variant="outline" block>Log Masuk / Daftar</x-ui.button>
                @endauth
            </div>
        </nav>
    </div>
</header>
