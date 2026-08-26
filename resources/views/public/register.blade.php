<x-layouts.public :title="'Daftar — '.$event->title">
    <section class="border-b border-hairline bg-surface">
        <div class="jelajah-container py-8">
            <a href="{{ $event->publicUrl() }}"
               class="inline-flex items-center gap-1.5 text-sm font-medium text-ink-soft hover:text-navy-900">
                <x-ui.icon name="arrow-left" class="h-4 w-4" /> Kembali ke halaman program
            </a>
            <h1 class="mt-3 font-display text-2xl text-navy-900 sm:text-3xl text-pretty">
                Pendaftaran Peserta
            </h1>
        </div>
    </section>

    <section class="jelajah-container py-10 sm:py-14">
        @include('partials.flash')
        <livewire:public.registration-form :event="$event" />
    </section>
</x-layouts.public>
