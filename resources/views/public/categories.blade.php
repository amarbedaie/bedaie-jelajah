<x-layouts.public title="Pilihan Program — BeDaie Jelajah"
                  description="Sepuluh jenis program yang boleh anda mohon untuk komuniti anda.">

    <x-jelajah.page-hero
        eyebrow="Pilihan Program"
        title="Program Yang Boleh Anda Mohon"
        lead="Pilih yang paling sesuai dengan keperluan komuniti anda. Pasukan BeDaie akan menentukan penceramah dan pengisian." />

    <section class="jelajah-container py-12 sm:py-16">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($categories as $category)
                <div class="flex h-full flex-col rounded-card border border-hairline bg-surface p-6 shadow-soft">
                    <div class="flex items-start justify-between gap-3">
                        <div class="grid h-12 w-12 place-items-center rounded-2xl bg-clay-50">
                            <x-ui.icon :name="$category->icon ?? 'book'" class="h-6 w-6 text-clay-600" />
                        </div>
                        @if ($category->events_count)
                            <x-ui.badge color="grey">{{ $category->events_count }} program</x-ui.badge>
                        @endif
                    </div>

                    <h2 class="mt-4 text-lg font-semibold text-ink">{{ $category->name }}</h2>
                    <p class="mt-2 flex-1 leading-relaxed text-ink-soft text-pretty">{{ $category->description }}</p>

                    <x-ui.button :href="route('jemput', ['kategori' => $category->slug])"
                                 variant="outline" block class="mt-5" iconAfter="arrow-right">
                        Mohon Program Ini
                    </x-ui.button>
                </div>
            @endforeach
        </div>
    </section>
</x-layouts.public>
