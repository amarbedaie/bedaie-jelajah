@php
    $typeLabels = [
        'rakan' => 'Rakan Kerjasama',
        'penaja' => 'Penaja',
        'masjid' => 'Rakan Masjid & Surau',
        'media' => 'Rakan Media',
    ];
@endphp

<x-layouts.public title="Rakan & Penaja — BeDaie Jelajah"
                  description="Organisasi, masjid dan penaja yang menggerakkan BeDaie Jelajah bersama kami.">

    <x-jelajah.page-hero
        eyebrow="Rakan Jelajah"
        title="Bersama Menghidupkan Ummah"
        lead="Setiap program yang berjaya lahir daripada kerjasama — masjid yang membuka pintu, organisasi yang menaja, komuniti yang menggerakkan.">
        <x-ui.button :href="'https://wa.me/'.config('jelajah.support.phone')" target="_blank" rel="noopener"
                     variant="primary" icon="handshake">
            Jadi Rakan Jelajah BeDaie
        </x-ui.button>
    </x-jelajah.page-hero>

    <section class="jelajah-container py-12 sm:py-16">
        @if ($partners->isEmpty())
            <x-ui.empty-state icon="handshake" title="Senarai rakan sedang dikemas kini"
                description="Hubungi kami jika organisasi anda ingin menjadi rakan atau penaja jelajah.">
                <x-ui.button :href="'https://wa.me/'.config('jelajah.support.phone')" target="_blank"
                             rel="noopener" variant="whatsapp" icon="whatsapp" class="mt-5">
                    Hubungi Kami
                </x-ui.button>
            </x-ui.empty-state>
        @else
            <div class="space-y-12">
                @foreach ($partners as $type => $group)
                    <div>
                        <h2 class="text-xl font-semibold text-ink">
                            {{ $typeLabels[$type] ?? \Illuminate\Support\Str::headline($type) }}
                        </h2>
                        <ul class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach ($group as $partner)
                                <li class="flex h-full flex-col items-center gap-3 rounded-card
                                           border border-hairline bg-surface p-6 text-center">
                                    @if ($partner->logo_path)
                                        <img src="{{ Storage::url($partner->logo_path) }}" alt="{{ $partner->name }}"
                                             loading="lazy" class="h-12 w-auto" />
                                    @else
                                        <span class="grid h-12 w-12 place-items-center rounded-2xl bg-brand-50">
                                            <x-ui.icon name="building" class="h-6 w-6 text-brand-600" />
                                        </span>
                                    @endif
                                    <p class="font-medium text-ink text-pretty">{{ $partner->name }}</p>
                                    @if ($partner->website_url)
                                        <a href="{{ $partner->website_url }}" target="_blank" rel="noopener"
                                           class="text-sm text-brand-600 hover:underline">Lawati laman</a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.public>
