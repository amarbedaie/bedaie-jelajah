<x-layouts.bare :title="'Perancangan — '.$event->title">
    <div class="mx-auto max-w-3xl px-6 py-10 print:px-0 print:py-0">

        <div class="flex items-start justify-between gap-4 print:hidden">
            <x-ui.button :href="route('admin.program.show', $event)" variant="ghost" size="sm" icon="arrow-left">
                Kembali ke program
            </x-ui.button>
            <x-ui.button type="button" onclick="window.print()" variant="outline" size="sm" icon="printer">
                Cetak
            </x-ui.button>
        </div>

        {{-- Penanda kekal: helaian ini boleh tertinggal di atas meja masjid. --}}
        <p class="mt-6 flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-alert">
            <span class="h-px w-8 bg-alert"></span>
            Dokumen dalaman BeDaie — jangan edarkan
        </p>

        <h1 class="mt-5 font-display text-4xl leading-tight text-ink text-pretty">{{ $event->title }}</h1>

        <dl class="mt-6 grid gap-x-10 gap-y-2.5 border-y border-hairline py-5 text-sm sm:grid-cols-2">
            <div class="flex gap-3">
                <dt class="w-24 shrink-0 text-ink-muted">Tarikh</dt>
                <dd class="text-ink">{{ $event->dateLabel() }} &middot; {{ $event->timeLabel() }}</dd>
            </div>
            <div class="flex gap-3">
                <dt class="w-24 shrink-0 text-ink-muted">Lokasi</dt>
                <dd class="text-ink text-pretty">{{ $event->venue?->name ?? $event->locationLabel() }}</dd>
            </div>
            @if ($event->speaker)
                <div class="flex gap-3">
                    <dt class="w-24 shrink-0 text-ink-muted">Penceramah</dt>
                    <dd class="text-ink">{{ $event->speaker->name }}</dd>
                </div>
            @endif
            <div class="flex gap-3">
                <dt class="w-24 shrink-0 text-ink-muted">Kategori</dt>
                <dd class="text-ink">{{ $event->category?->name ?? '—' }}</dd>
            </div>
        </dl>

        @if (filled($event->objectives))
            <section class="mt-9">
                <h2 class="font-display text-2xl text-ink">Objektif</h2>
                <div class="prose-jelajah mt-3 text-ink-soft">
                    @foreach (preg_split('/\r?\n/', trim($event->objectives)) as $line)
                        @if (trim($line) !== '')
                            <p class="flex gap-3">
                                <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-brand-500"></span>
                                <span class="text-pretty">{{ $line }}</span>
                            </p>
                        @endif
                    @endforeach
                </div>
            </section>
        @endif

        @if ($event->flowSteps->isNotEmpty() || filled($event->flow_notes))
            <section class="mt-9">
                <h2 class="font-display text-2xl text-ink">Aliran Acara</h2>

                @if ($event->flowSteps->isNotEmpty())
                    <table class="mt-4 w-full border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-ink text-left text-xs font-semibold uppercase tracking-wider text-ink">
                                <th class="w-28 py-2 pr-4">Masa</th>
                                <th class="py-2 pr-4">Aktiviti</th>
                                <th class="w-40 py-2">Siapa</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($event->flowSteps as $step)
                                <tr class="border-b border-hairline align-top">
                                    <td class="py-3 pr-4 tabular-nums text-ink-soft">{{ $step->time_label ?: '—' }}</td>
                                    <td class="py-3 pr-4 text-ink text-pretty">{{ $step->activity }}</td>
                                    <td class="py-3 text-ink-soft">{{ $step->responsible ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                @if (filled($event->flow_notes))
                    <div class="mt-5 rounded-card border border-hairline bg-mist p-4">
                        <p class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Nota</p>
                        <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-ink-soft text-pretty">{{ $event->flow_notes }}</p>
                    </div>
                @endif
            </section>
        @endif

        @if ($event->packages->isNotEmpty())
            <section class="mt-9">
                <h2 class="font-display text-2xl text-ink">Pakej Jualan</h2>

                <div class="mt-4 space-y-4">
                    @foreach ($event->packages as $pakej)
                        <div class="rounded-card border p-4
                                    {{ $pakej->pivot->is_focus ? 'border-brand-500 bg-brand-50' : 'border-hairline' }}">
                            <div class="flex flex-wrap items-baseline justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="font-medium text-ink text-pretty">
                                        {{ $pakej->name }}
                                        @if ($pakej->pivot->is_focus)
                                            <span class="ml-2 text-xs font-semibold uppercase tracking-wider text-brand-700">
                                                Tumpuan utama
                                            </span>
                                        @endif
                                    </h3>
                                    @if ($pakej->tagline)
                                        <p class="mt-0.5 text-sm text-ink-soft text-pretty">{{ $pakej->tagline }}</p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <span class="font-display text-xl text-ink">{{ $pakej->priceLabel() }}</span>
                                    @if ($pakej->pivot->target_units)
                                        <span class="block text-xs text-ink-muted">
                                            sasaran {{ $pakej->pivot->target_units }} unit
                                        </span>
                                    @endif
                                </div>
                            </div>

                            @if ($pakej->itemLines())
                                <ul class="mt-3 grid gap-1 border-t border-hairline pt-3 text-sm text-ink-soft sm:grid-cols-2">
                                    @foreach ($pakej->itemLines() as $item)
                                        <li class="flex gap-2">
                                            <span class="mt-1.5 h-1 w-1 shrink-0 rounded-full bg-brand-400"></span>
                                            <span class="text-pretty">{{ $item }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            @if ($pakej->pitch)
                                <p class="mt-3 text-sm italic leading-relaxed text-ink-soft text-pretty">
                                    &ldquo;{{ $pakej->pitch }}&rdquo;
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($event->teamMembers->isNotEmpty())
            <section class="mt-9">
                <h2 class="font-display text-2xl text-ink">Pasukan Bertugas</h2>
                <table class="mt-4 w-full border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-ink text-left text-xs font-semibold uppercase tracking-wider text-ink">
                            <th class="w-48 py-2 pr-4">Peranan</th>
                            <th class="py-2 pr-4">Nama</th>
                            <th class="w-40 py-2">Nombor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($event->teamMembers as $ahli)
                            <tr class="border-b border-hairline align-top">
                                <td class="py-3 pr-4 text-ink-soft">{{ $ahli->role }}</td>
                                <td class="py-3 pr-4 text-ink text-pretty">{{ $ahli->name }}</td>
                                <td class="py-3 tabular-nums text-ink-soft">{{ $ahli->phone ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @endif

        @if (filled($event->sop))
            <section class="mt-9">
                <h2 class="font-display text-2xl text-ink">SOP</h2>
                <p class="mt-3 whitespace-pre-line leading-relaxed text-ink-soft text-pretty">{{ $event->sop }}</p>
            </section>
        @endif

        @unless ($event->hasInternalPlanning())
            <x-ui.empty-state icon="clipboard" title="Belum ada perancangan"
                              description="Objektif, aliran acara dan SOP belum diisi untuk program ini."
                              class="mt-10">
                <x-ui.button :href="route('admin.program.show', $event)" variant="primary" size="sm">
                    Isi Sekarang
                </x-ui.button>
            </x-ui.empty-state>
        @endunless

        <p class="mt-12 border-t border-hairline pt-4 text-xs text-ink-muted">
            {{ config('jelajah.org') }} &middot; dicetak {{ now()->translatedFormat('j F Y, g:ia') }}
        </p>
    </div>
</x-layouts.bare>
