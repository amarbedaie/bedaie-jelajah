<x-layouts.public :title="$pageTitle.' — BeDaie Jelajah'">
    <x-jelajah.page-hero eyebrow="Rasmi" :title="$pageTitle" :dark="false" />

    <section class="jelajah-container py-12 sm:py-16">
        <article class="prose-jelajah mx-auto max-w-3xl">
            @if ($updated)
                <p class="text-sm text-ink-muted">
                    Kemas kini terakhir: {{ \Illuminate\Support\Carbon::parse($updated)->translatedFormat('j F Y') }}
                </p>
            @endif

            {!! \Illuminate\Support\Str::markdown($body) !!}
        </article>

        <p class="mx-auto mt-10 max-w-3xl text-sm text-ink-muted">
            Ada soalan tentang {{ mb_strtolower($pageTitle) }}? Hubungi kami di
            <a href="mailto:{{ config('jelajah.support.email') }}" class="font-medium text-brand-600 hover:underline">
                {{ config('jelajah.support.email') }}
            </a>.
        </p>
    </section>
</x-layouts.public>
