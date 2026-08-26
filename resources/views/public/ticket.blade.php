@php
    $status = $registration->status;
    $attended = $registration->hasAttended();
    $payment = $registration->payment;
@endphp

<x-layouts.public :title="'Tiket — '.$event->title">
    <section class="jelajah-container py-10 sm:py-14">
        @include('partials.flash')

        <div class="mx-auto max-w-xl">
            {{-- ── Status ────────────────────────────────────── --}}
            <div class="text-center">
                @if ($status === \App\Enums\RegistrationStatus::Disahkan)
                    <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-success-soft">
                        <x-ui.icon name="check-circle" class="h-7 w-7 text-success" />
                    </div>
                    <h1 class="mt-5 font-display text-2xl text-navy-900 sm:text-3xl">Pendaftaran Disahkan</h1>
                    <p class="mt-2 text-ink-soft text-pretty">
                        Tunjukkan QR di bawah pada pintu masuk untuk merekod kehadiran anda.
                    </p>
                @elseif ($status === \App\Enums\RegistrationStatus::SenaraiMenunggu)
                    <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-warning-soft">
                        <x-ui.icon name="clock" class="h-7 w-7 text-warning" />
                    </div>
                    <h1 class="mt-5 font-display text-2xl text-navy-900 sm:text-3xl">Anda Dalam Senarai Menunggu</h1>
                    <p class="mt-2 text-ink-soft text-pretty">
                        Tempat telah penuh buat masa ini. Kami akan memaklumkan melalui WhatsApp
                        sebaik sahaja ada tempat kosong.
                    </p>
                @elseif ($status === \App\Enums\RegistrationStatus::MenungguPengesahan)
                    <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-warning-soft">
                        <x-ui.icon name="clock" class="h-7 w-7 text-warning" />
                    </div>
                    <h1 class="mt-5 font-display text-2xl text-navy-900 sm:text-3xl">Menunggu Pengesahan</h1>
                    <p class="mt-2 text-ink-soft text-pretty">
                        @if ($payment && $payment->status === \App\Enums\PaymentStatus::BelumBayar)
                            Pendaftaran anda akan disahkan sebaik pembayaran diterima.
                        @else
                            Pendaftaran anda sedang disemak oleh penganjur.
                        @endif
                    </p>
                @else
                    <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-mist">
                        <x-ui.icon name="x-circle" class="h-7 w-7 text-ink-muted" />
                    </div>
                    <h1 class="mt-5 font-display text-2xl text-navy-900 sm:text-3xl">Pendaftaran Dibatalkan</h1>
                    <p class="mt-2 text-ink-soft text-pretty">
                        Tempat anda telah dilepaskan. Anda boleh mendaftar semula jika masih ada tempat.
                    </p>
                @endif
            </div>

            {{-- ── Tiket ─────────────────────────────────────── --}}
            <div class="mt-8 overflow-hidden rounded-[--radius-card-lg] border border-hairline bg-surface shadow-lift">
                <div class="relative bg-navy-900 p-6">
                    <div class="motif-girih-dark absolute inset-0 opacity-60" aria-hidden="true"></div>
                    <div class="relative">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-brand-300">
                            {{ $event->category?->name ?? 'BeDaie Jelajah' }}
                        </p>
                        <h2 class="mt-2 font-semibold leading-snug text-white text-pretty">{{ $event->title }}</h2>
                        <p class="mt-3 font-mono text-xs text-white/50">{{ $registration->reference_no }}</p>
                    </div>
                </div>

                @if ($qrSvg && $status !== \App\Enums\RegistrationStatus::Dibatalkan)
                    <div class="border-b border-dashed border-hairline p-6 text-center">
                        <div class="mx-auto inline-block rounded-2xl bg-white p-3 ring-1 ring-hairline">
                            {!! $qrSvg !!}
                        </div>
                        @if ($attended)
                            <p class="mt-4 inline-flex items-center gap-2 rounded-full bg-success-soft px-4 py-1.5
                                      text-sm font-medium text-[#0A5537]">
                                <x-ui.icon name="check-circle" class="h-4 w-4" />
                                Kehadiran direkod {{ $registration->attendance?->checked_in_at?->format('g:ia, j M Y') }}
                            </p>
                        @else
                            <p class="mt-4 text-sm text-ink-muted text-pretty">
                                Imbas kod ini di pintu masuk. Jangan kongsi tiket ini dengan orang lain.
                            </p>
                        @endif
                    </div>
                @endif

                <div class="p-6">
                    <dl class="space-y-3.5">
                        <div class="flex gap-3">
                            <dt class="w-28 shrink-0 text-sm text-ink-muted">Peserta</dt>
                            <dd class="text-sm font-medium text-navy-900 text-pretty">{{ $registration->name }}</dd>
                        </div>

                        @if ($registration->guests->isNotEmpty())
                            <div class="flex gap-3">
                                <dt class="w-28 shrink-0 text-sm text-ink-muted">Bersama</dt>
                                <dd class="text-sm text-ink text-pretty">
                                    {{ $registration->guests->pluck('name')->join(', ') }}
                                </dd>
                            </div>
                        @endif

                        <div class="flex gap-3">
                            <dt class="w-28 shrink-0 text-sm text-ink-muted">Jumlah tempat</dt>
                            <dd class="text-sm text-ink">{{ $registration->seats() }}</dd>
                        </div>

                        <div class="flex gap-3">
                            <dt class="w-28 shrink-0 text-sm text-ink-muted">Tarikh</dt>
                            <dd class="text-sm text-ink">{{ $event->dateLabel() }}</dd>
                        </div>

                        <div class="flex gap-3">
                            <dt class="w-28 shrink-0 text-sm text-ink-muted">Masa</dt>
                            <dd class="text-sm text-ink">
                                {{ $event->timeLabel() }}
                                @if ($event->doors_open_at)
                                    <span class="block text-xs text-ink-muted">
                                        Pendaftaran dibuka {{ $event->doors_open_at->format('g:ia') }}
                                    </span>
                                @endif
                            </dd>
                        </div>

                        <div class="flex gap-3">
                            <dt class="w-28 shrink-0 text-sm text-ink-muted">Lokasi</dt>
                            <dd class="text-sm text-ink text-pretty">
                                {{ $event->venue?->name ?? $event->locationLabel() }}
                                @if ($event->venue?->address)
                                    <span class="block text-xs text-ink-muted">{{ $event->venue->address }}</span>
                                @endif
                            </dd>
                        </div>

                        @if ($payment && $payment->amount > 0)
                            <div class="flex gap-3">
                                <dt class="w-28 shrink-0 text-sm text-ink-muted">Bayaran</dt>
                                <dd class="text-sm">
                                    <x-ui.badge :color="$payment->status->color()">
                                        {{ $payment->status->label() }}
                                    </x-ui.badge>
                                    <span class="ml-1.5 text-ink">RM {{ number_format((float) $payment->amount, 2) }}</span>
                                </dd>
                            </div>
                        @endif
                    </dl>

                    @if ($event->parking_info)
                        <div class="mt-5 rounded-xl bg-mist p-3.5">
                            <p class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Parkir & Arahan</p>
                            <p class="mt-1.5 text-sm text-ink-soft text-pretty">{{ $event->parking_info }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ── Tindakan ──────────────────────────────────── --}}
            @if ($status !== \App\Enums\RegistrationStatus::Dibatalkan)
                <div class="mt-6 grid gap-2.5 sm:grid-cols-2">
                    @if ($payment && $payment->status === \App\Enums\PaymentStatus::BelumBayar)
                        <x-ui.button :href="route('bayaran.show', $payment->public_id)" variant="primary"
                                     block icon="ticket" class="sm:col-span-2">
                            Selesaikan Pembayaran
                        </x-ui.button>
                    @endif

                    <x-ui.button :href="route('tiket.kalendar', $registration->public_token)"
                                 variant="outline" block icon="calendar">
                        Tambah ke Kalendar
                    </x-ui.button>

                    @if ($event->venue?->google_maps_url)
                        <x-ui.button :href="$event->venue->google_maps_url" target="_blank" rel="noopener"
                                     variant="outline" block icon="pin">
                            Buka di Google Maps
                        </x-ui.button>
                    @endif

                    <x-ui.button :href="$event->whatsappShareUrl()" target="_blank" rel="noopener"
                                 variant="whatsapp" block icon="whatsapp">
                        Ajak Rakan
                    </x-ui.button>

                    <x-ui.copy-button :text="$registration->ticketUrl()" label="Salin Link Tiket"
                                      variant="outline" block />
                </div>

                @if ($attended && $registration->certificate)
                    <div class="mt-6 rounded-[--radius-card] border border-brand-200 bg-brand-50 p-5">
                        <div class="flex items-start gap-3">
                            <x-ui.icon name="certificate" class="mt-0.5 h-5 w-5 shrink-0 text-brand-600" />
                            <div class="min-w-0 flex-1">
                                <h3 class="font-semibold text-navy-900">Sijil Anda Telah Sedia</h3>
                                <p class="mt-1 font-mono text-xs text-ink-muted">
                                    {{ $registration->certificate->certificate_number }}
                                </p>
                                <x-ui.button :href="$registration->certificate->downloadUrl()" target="_blank"
                                             variant="primary" size="sm" class="mt-3" icon="download">
                                    Muat Turun Sijil
                                </x-ui.button>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($event->recordings()->published()->exists())
                    <div class="mt-6 rounded-[--radius-card] border border-brand-200 bg-brand-50 p-5">
                        <div class="flex items-start gap-3">
                            <x-ui.icon name="play" class="mt-0.5 h-5 w-5 shrink-0 text-brand-700" />
                            <div class="min-w-0 flex-1">
                                <h3 class="font-semibold text-navy-900">Rakaman Program Tersedia</h3>
                                <p class="mt-1.5 text-sm text-ink-soft text-pretty">
                                    Tonton semula sesi ini bila-bila masa.
                                </p>
                                <x-ui.button :href="route('rakaman.index', $registration->public_token)"
                                             variant="primary" size="sm" class="mt-3" icon="play">
                                    Buka Rakaman
                                </x-ui.button>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($event->hasEnded() && ! $registration->feedback && $attended)
                    <div class="mt-6 rounded-[--radius-card] border border-hairline bg-surface p-5">
                        <h3 class="font-semibold text-navy-900">Bagaimana pengalaman anda?</h3>
                        <p class="mt-1.5 text-sm text-ink-soft text-pretty">
                            Maklum balas anda membantu kami memperbaiki program akan datang.
                        </p>
                        <x-ui.button :href="route('maklum-balas.show', $registration->public_token)"
                                     variant="outline" size="sm" class="mt-3" icon="star">
                            Beri Maklum Balas
                        </x-ui.button>
                    </div>
                @endif

                @unless ($attended || $event->hasEnded())
                    <p class="mt-6 text-center text-sm text-ink-muted">
                        Tidak dapat hadir?
                        <a href="{{ route('tiket.cancel', $registration->public_token) }}"
                           class="font-medium text-danger hover:underline">Batalkan pendaftaran</a>
                        supaya tempat dapat diberikan kepada orang lain.
                    </p>
                @endunless
            @else
                <div class="mt-6">
                    <x-ui.button :href="$event->publicUrl()" variant="primary" block>
                        Lihat Halaman Program
                    </x-ui.button>
                </div>
            @endif
        </div>
    </section>
</x-layouts.public>
