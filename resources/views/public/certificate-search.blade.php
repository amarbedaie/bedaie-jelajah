<x-layouts.public title="Semakan Sijil — BeDaie Jelajah"
                  description="Sahkan kesahihan sijil BeDaie Jelajah menggunakan nombor siri.">

    <x-jelajah.page-hero
        eyebrow="Semakan Sijil"
        title="Sahkan Sijil BeDaie"
        lead="Masukkan nombor siri sijil untuk mengesahkan kesahihannya. Anda juga boleh mengimbas kod QR pada sijil." />

    <section class="jelajah-container py-12 sm:py-16">
        <div class="mx-auto max-w-lg">
            <x-ui.card class="sm:p-8">
                <form method="GET" action="{{ route('sijil.semak') }}" class="space-y-5">
                    <x-ui.field label="Nombor sijil" for="no" required
                                hint="Contoh: BDJ-2026-KEL-000123">
                        <x-ui.input id="no" name="no" :value="request('no')" icon="certificate"
                                    class="uppercase" placeholder="BDJ-2026-KEL-000123" autofocus required />
                    </x-ui.field>

                    <x-ui.button type="submit" variant="primary" block size="lg" icon="search">
                        Semak Sijil
                    </x-ui.button>
                </form>
            </x-ui.card>

            <p class="mt-6 text-center text-sm text-ink-muted text-pretty">
                Peserta yang telah log masuk boleh melihat semua sijil dalam
                <a href="{{ route('peserta.sijil') }}" class="font-medium text-clay-600 hover:underline">Pasport Ilmu</a>.
            </p>
        </div>
    </section>
</x-layouts.public>
