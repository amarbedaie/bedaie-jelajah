<x-layouts.public title="Peta Jelajah — BeDaie Jelajah"
                  description="Lihat ke mana BeDaie telah sampai, dan kawasan yang masih menanti.">

    <x-jelajah.page-hero
        eyebrow="Peta Jelajah"
        title="Ke Mana BeDaie Telah Sampai"
        lead="Klik mana-mana negeri untuk melihat rekod jelajah, program akan datang dan jumlah peserta.">
        <x-ui.button :href="route('minat')" variant="primary" icon="heart">
            Saya Mahu BeDaie Datang ke Sini
        </x-ui.button>
    </x-jelajah.page-hero>

    <section class="border-b border-hairline bg-surface">
        <div class="jelajah-container py-8">
            <dl class="grid grid-cols-2 gap-x-4 gap-y-6 sm:grid-cols-3 lg:grid-cols-5">
                @foreach ([
                    ['negeri', 'Negeri Dijelajahi'], ['daerah', 'Daerah Dikunjungi'],
                    ['program', 'Program Dilaksanakan'], ['peserta', 'Peserta Disantuni'],
                    ['rakan', 'Rakan Masjid & Organisasi'],
                ] as [$key, $label])
                    <div>
                        <dd class="font-display text-3xl text-ink">{{ number_format($headline[$key]) }}</dd>
                        <dt class="mt-0.5 text-sm text-ink-soft text-pretty">{{ $label }}</dt>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    <section class="jelajah-container py-12 sm:py-16">
        <x-jelajah.map :states="$states" />
    </section>
</x-layouts.public>
