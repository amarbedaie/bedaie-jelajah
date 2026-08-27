<x-layouts.public title="Tiada Sambungan">
    <section class="jelajah-container py-16 sm:py-24">
        <div class="mx-auto max-w-lg text-center">
            <div class="mx-auto grid h-16 w-16 place-items-center rounded-3xl bg-mist">
                <x-ui.icon name="globe" class="h-8 w-8 text-ink-muted" />
            </div>

            <h1 class="mt-6 font-display text-3xl text-ink">Tiada Sambungan Internet</h1>

            <p class="mt-4 text-ink-soft text-pretty">
                Halaman ini memerlukan sambungan. Sambungan di dalam masjid kadangkala lemah —
                cuba berdiri berhampiran pintu atau tunggu sebentar.
            </p>

            <div class="mt-8 rounded-card bg-clay-50 p-5 text-left">
                <p class="text-sm font-medium text-ink">Jika anda sedang mendaftar masuk peserta</p>
                <p class="mt-1.5 text-sm text-ink-soft text-pretty">
                    Catat nama dan nombor telefon peserta secara manual dahulu. Anda boleh
                    merekodkannya sebagai walk-in sebaik sambungan pulih.
                </p>
            </div>

            <x-ui.button onclick="window.location.reload()" variant="primary" class="mt-8" icon="refresh">
                Cuba Semula
            </x-ui.button>
        </div>
    </section>
</x-layouts.public>
