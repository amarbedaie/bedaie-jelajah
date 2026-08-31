<x-layouts.app title="Sijil Saya" nav="peserta" heading="Sijil Saya"
               subheading="Semua sijil digital yang anda peroleh melalui BeDaie Jelajah.">

    @if ($certificates->isEmpty())
        <x-ui.empty-state icon="certificate" title="Belum ada sijil"
            description="Sijil dijana automatik selepas kehadiran anda direkodkan melalui QR pada hari program.">
            <x-ui.button :href="route('program.index')" variant="primary" class="mt-5">Sertai Program</x-ui.button>
        </x-ui.empty-state>
    @else
        <ul class="grid gap-5 sm:grid-cols-2">
            @foreach ($certificates as $certificate)
                <li class="flex h-full flex-col overflow-hidden rounded-card border border-hairline bg-surface">
                    <div class="relative border-b border-hairline bg-cream p-5">
                        <div class="motif-girih absolute inset-0 opacity-50" aria-hidden="true"></div>
                        <div class="relative">
                            <p class="text-[0.65rem] font-semibold uppercase tracking-[0.18em] text-brand-700">
                                {{ $certificate->type->label() }}
                            </p>
                            <p class="mt-2 font-medium leading-snug text-ink text-pretty">
                                {{ $certificate->event_title }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-1 flex-col p-5">
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between gap-3">
                                <dt class="text-ink-muted">Nombor</dt>
                                <dd class="font-mono text-xs text-ink">{{ $certificate->certificate_number }}</dd>
                            </div>
                            @if ($certificate->event_date)
                                <div class="flex justify-between gap-3">
                                    <dt class="text-ink-muted">Tarikh program</dt>
                                    <dd class="text-ink">
                                        {{ \Illuminate\Support\Carbon::parse($certificate->event_date)->translatedFormat('j M Y') }}
                                    </dd>
                                </div>
                            @endif
                            @if ($certificate->learning_hours)
                                <div class="flex justify-between gap-3">
                                    <dt class="text-ink-muted">Jam pembelajaran</dt>
                                    <dd class="text-ink">
                                        {{ rtrim(rtrim(number_format((float) $certificate->learning_hours, 1), '0'), '.') }} jam
                                    </dd>
                                </div>
                            @endif
                            <div class="flex justify-between gap-3">
                                <dt class="text-ink-muted">Status</dt>
                                <dd>
                                    <x-ui.badge :color="$certificate->isValid() ? 'success' : 'danger'">
                                        {{ $certificate->status->label() }}
                                    </x-ui.badge>
                                </dd>
                            </div>
                        </dl>

                        <div class="mt-auto flex flex-wrap gap-2 pt-5">
                            @if ($certificate->isValid())
                                <x-ui.button :href="$certificate->downloadUrl()" target="_blank"
                                             variant="primary" size="sm" icon="download">
                                    Muat Turun
                                </x-ui.button>
                            @endif
                            <x-ui.button :href="$certificate->verificationUrl()" target="_blank"
                                         variant="outline" size="sm" icon="shield">
                                Pautan Pengesahan
                            </x-ui.button>
                            <x-ui.copy-button :text="$certificate->verificationUrl()" label="Salin" size="sm" variant="ghost" />
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>

        <div class="mt-8">{{ $certificates->links() }}</div>
    @endif
</x-layouts.app>
