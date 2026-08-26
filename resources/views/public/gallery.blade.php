<x-layouts.public title="Galeri Impak — BeDaie Jelajah"
                  description="Wajah, suasana dan detik dari setiap program BeDaie Jelajah.">

    <x-jelajah.page-hero
        eyebrow="Galeri Impak"
        title="Wajah Gerakan Ini"
        :lead="'Daripada '.number_format($headline['program']).' program di '.$headline['negeri'].' negeri — inilah suasana yang kami temui.'" />

    <section class="jelajah-container py-12 sm:py-16">
        @if ($photos->isEmpty())
            <x-ui.empty-state icon="image" title="Galeri sedang dikemas kini"
                description="Gambar program dimuat naik selepas disemak dan diluluskan oleh pasukan BeDaie." />
        @else
            <div class="columns-2 gap-4 sm:columns-3 lg:columns-4 [&>*]:mb-4">
                @foreach ($photos as $photo)
                    <figure class="break-inside-avoid overflow-hidden rounded-xl border border-hairline bg-surface">
                        <img src="{{ Storage::url($photo->image_path) }}"
                             alt="{{ $photo->caption ?? $photo->event?->title }}" loading="lazy"
                             class="w-full object-cover" />
                        <figcaption class="p-3">
                            <p class="text-sm text-navy-900 text-pretty">{{ $photo->caption ?? $photo->event?->title }}</p>
                            @if ($photo->event)
                                <p class="mt-0.5 text-xs text-ink-muted">
                                    {{ $photo->event->venue?->name ?? $photo->event->state?->name }}
                                </p>
                            @endif
                        </figcaption>
                    </figure>
                @endforeach
            </div>

            <div class="mt-10">{{ $photos->links() }}</div>
        @endif
    </section>
</x-layouts.public>
