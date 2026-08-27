@php
    $waPhone = config('jelajah.support.phone');
@endphp

<footer class="relative mt-24 overflow-hidden border-t border-hairline bg-cream text-ink-soft">
    <div class="motif-girih pointer-events-none absolute inset-0 opacity-30" aria-hidden="true"></div>

    <div class="jelajah-container relative py-14 sm:py-16">
        <div class="grid gap-10 md:grid-cols-12">
            <div class="md:col-span-5">
                <x-brand.logo />
                <p class="mt-5 max-w-sm text-sm leading-relaxed text-pretty">
                    BeDaie Jelajah membawa ilmu ke masjid, surau, sekolah, tahfiz dan komuniti
                    di seluruh Malaysia — dari hati ke hati.
                </p>
                <p class="mt-6 font-display text-xl italic text-clay-700">
                    "{{ config('jelajah.slogan') }}"
                </p>

                <div class="mt-6 flex flex-wrap gap-2.5">
                    <x-ui.button :href="'https://wa.me/'.$waPhone" variant="whatsapp" size="sm" icon="whatsapp"
                                 target="_blank" rel="noopener">
                        WhatsApp Kami
                    </x-ui.button>
                    <x-ui.button :href="'mailto:'.config('jelajah.support.email')" variant="outline" size="sm"
                                 icon="mail" class="!border-hairline !bg-surface !text-ink hover:!bg-surface">
                        Emel
                    </x-ui.button>
                </div>
            </div>

            <div class="md:col-span-2">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-ink">Jelajah</h3>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="{{ route('peta') }}" class="transition hover:text-clay-700">Peta Jelajah</a></li>
                    <li><a href="{{ route('program.index') }}" class="transition hover:text-clay-700">Program Akan Datang</a></li>
                    <li><a href="{{ route('jejak') }}" class="transition hover:text-clay-700">Jejak Jelajah</a></li>
                    <li><a href="{{ route('kategori') }}" class="transition hover:text-clay-700">Pilihan Program</a></li>
                    <li><a href="{{ route('galeri') }}" class="transition hover:text-clay-700">Galeri Impak</a></li>
                </ul>
            </div>

            <div class="md:col-span-2">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-ink">Sertai</h3>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="{{ route('jemput') }}" class="transition hover:text-clay-700">Jemput BeDaie</a></li>
                    <li><a href="{{ route('minat') }}" class="transition hover:text-clay-700">Bawa Ke Kawasan Saya</a></li>
                    <li><a href="{{ route('rakan') }}" class="transition hover:text-clay-700">Rakan & Penaja</a></li>
                    <li><a href="{{ route('sijil.semak') }}" class="transition hover:text-clay-700">Semakan Sijil</a></li>
                    <li><a href="{{ route('login') }}" class="transition hover:text-clay-700">Log Masuk</a></li>
                </ul>
            </div>

            <div class="md:col-span-3">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-ink">Hubungi</h3>
                <ul class="mt-4 space-y-3 text-sm">
                    <li class="flex items-start gap-2.5">
                        <x-ui.icon name="whatsapp" class="mt-0.5 h-4 w-4 shrink-0 text-clay-700" />
                        <a href="https://wa.me/{{ $waPhone }}" class="transition hover:text-clay-700">+{{ $waPhone }}</a>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <x-ui.icon name="mail" class="mt-0.5 h-4 w-4 shrink-0 text-clay-700" />
                        <a href="mailto:{{ config('jelajah.support.email') }}" class="break-all transition hover:text-clay-700">
                            {{ config('jelajah.support.email') }}
                        </a>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <x-ui.icon name="globe" class="mt-0.5 h-4 w-4 shrink-0 text-clay-700" />
                        <a href="{{ config('jelajah.website') }}" target="_blank" rel="noopener"
                           class="transition hover:text-clay-700">bedaie.com.my</a>
                    </li>
                </ul>

                <p class="mt-5 inline-flex items-center gap-2 rounded-full bg-surface px-3.5 py-1.5 text-xs font-medium text-clay-700 ring-1 ring-white/10">
                    <x-ui.icon name="home" class="h-3.5 w-3.5" />
                    {{ config('jelajah.motto') }}
                </p>
            </div>
        </div>

        <div class="mt-12 flex flex-col gap-4 border-t border-hairline pt-6 text-xs sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ date('Y') }} {{ config('jelajah.org') }}. Hak cipta terpelihara.</p>
            <div class="flex flex-wrap gap-x-5 gap-y-2">
                <a href="{{ route('privasi') }}" class="transition hover:text-clay-700">Polisi Privasi</a>
                <a href="{{ route('terma') }}" class="transition hover:text-clay-700">Terma Penggunaan</a>
                <a href="{{ route('tentang') }}" class="transition hover:text-clay-700">Tentang Kami</a>
            </div>
        </div>
    </div>
</footer>
