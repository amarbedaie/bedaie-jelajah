<x-layouts.public title="Permohonan Diterima — BeDaie Jelajah">
    <section class="jelajah-container py-16 sm:py-24">
        <div class="mx-auto max-w-2xl text-center">
            <div class="mx-auto grid h-16 w-16 place-items-center rounded-3xl bg-brand-50">
                <x-ui.icon name="check-circle" class="h-8 w-8 text-brand-700" />
            </div>

            <h1 class="mt-6 font-display text-3xl text-ink sm:text-4xl text-pretty">
                Permohonan Anda Telah Kami Terima
            </h1>

            <p class="mt-4 text-ink-soft text-pretty">
                Terima kasih, {{ \Illuminate\Support\Str::before($application->applicant_name, ' ') }}.
                Pasukan BeDaie akan menyemak permohonan ini dan menghubungi anda melalui WhatsApp.
            </p>

            <div class="mt-8 rounded-card border border-hairline bg-surface p-6 text-left shadow-soft">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-brand-600">Nombor Rujukan</p>
                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <p class="font-display text-2xl text-ink">{{ $application->reference_no }}</p>
                    <x-ui.copy-button :text="$application->reference_no" label="Salin" size="sm" />
                </div>

                <dl class="mt-5 grid gap-3 border-t border-hairline pt-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs text-ink-muted">Lokasi dicadangkan</dt>
                        <dd class="mt-0.5 text-sm text-ink text-pretty">{{ $application->venue_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-ink-muted">Kawasan</dt>
                        <dd class="mt-0.5 text-sm text-ink">
                            {{ $application->district?->name ? $application->district->name.', ' : '' }}{{ $application->state?->name }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-ink-muted">Jenis program</dt>
                        <dd class="mt-0.5 text-sm text-ink">{{ $application->category?->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-ink-muted">Status</dt>
                        <dd class="mt-1">
                            <x-ui.badge :color="$application->status->color()" dot>
                                {{ $application->status->label() }}
                            </x-ui.badge>
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="mt-8 rounded-card bg-brand-50 p-6 text-left">
                <h2 class="font-semibold text-ink">Apa yang berlaku seterusnya?</h2>
                <ol class="mt-3 space-y-2.5">
                    @foreach ([
                        'Kami semak permohonan dan kesesuaian lokasi.',
                        'Pasukan BeDaie hubungi anda melalui WhatsApp untuk berbincang.',
                        'Tarikh, penceramah dan kapasiti ditetapkan bersama.',
                        'Program disahkan — halaman program, link, QR dan poster dijana automatik untuk anda sebarkan.',
                    ] as $i => $text)
                        <li class="flex gap-3 text-sm text-ink-soft text-pretty">
                            <span class="grid h-5 w-5 shrink-0 place-items-center rounded-full bg-brand-600
                                         text-[0.65rem] font-bold text-white">{{ $i + 1 }}</span>
                            {{ $text }}
                        </li>
                    @endforeach
                </ol>
            </div>

            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                @auth
                    <x-ui.button :href="route('penggerak.permohonan.show', $application)" variant="primary" icon="clipboard">
                        Jejak Status Permohonan
                    </x-ui.button>
                @else
                    <x-ui.button :href="route('login')" variant="primary" icon="user">
                        Log Masuk untuk Jejak Status
                    </x-ui.button>
                @endauth
                <x-ui.button :href="route('home')" variant="outline">Kembali ke Utama</x-ui.button>
            </div>
        </div>
    </section>
</x-layouts.public>
