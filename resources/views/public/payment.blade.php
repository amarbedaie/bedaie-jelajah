<x-layouts.public :title="'Pembayaran — '.$event->title">
    <section class="jelajah-container py-10 sm:py-16">
        @include('partials.flash')

        <div class="mx-auto max-w-xl">
            <div class="text-center">
                <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-clay-50">
                    <x-ui.icon name="ticket" class="h-7 w-7 text-clay-600" />
                </div>
                <h1 class="mt-5 font-display text-2xl text-ink sm:text-3xl">Selesaikan Pembayaran</h1>
                <p class="mt-2 text-ink-soft text-pretty">
                    Pendaftaran anda disimpan. Tempat disahkan sebaik pembayaran diterima.
                </p>
            </div>

            <x-ui.card class="mt-8">
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-3">
                        <dt class="text-ink-muted">Program</dt>
                        <dd class="text-right font-medium text-ink text-pretty">{{ $event->title }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-ink-muted">Peserta</dt>
                        <dd class="text-ink">{{ $registration->name }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-ink-muted">Bilangan tempat</dt>
                        <dd class="text-ink">{{ $registration->seats() }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-ink-muted">Rujukan</dt>
                        <dd class="font-mono text-xs text-ink">{{ $registration->reference_no }}</dd>
                    </div>
                    <div class="flex items-baseline justify-between gap-3 border-t border-hairline pt-3">
                        <dt class="font-medium text-ink">Jumlah</dt>
                        <dd class="font-display text-2xl text-ink">
                            RM {{ number_format((float) $payment->amount, 2) }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-ink-muted">Status</dt>
                        <dd><x-ui.badge :color="$payment->status->color()">{{ $payment->status->label() }}</x-ui.badge></dd>
                    </div>
                </dl>
            </x-ui.card>

            @if ($payment->status === \App\Enums\PaymentStatus::Berjaya)
                <x-ui.alert variant="success" icon="check-circle" class="mt-6" title="Pembayaran diterima">
                    Tiket dan QR kehadiran anda sudah sedia.
                </x-ui.alert>
                <x-ui.button :href="route('tiket.show', $registration->public_token)"
                             variant="primary" size="lg" block class="mt-5">
                    Lihat Tiket & QR
                </x-ui.button>
            @elseif ($intent && $intent->redirectUrl)
                <x-ui.button :href="$intent->redirectUrl" variant="primary" size="lg" block class="mt-6">
                    Bayar Melalui {{ $gateway->label() }}
                </x-ui.button>
                <p class="mt-3 text-center text-xs text-ink-muted">
                    Anda akan dibawa ke halaman pembayaran selamat.
                </p>
            @else
                {{-- Pembayaran manual: arahan pindahan bank --}}
                <x-ui.card class="mt-6">
                    <h2 class="font-semibold text-ink">Arahan Pembayaran</h2>
                    <p class="mt-1.5 text-sm text-ink-soft text-pretty">
                        Sila buat pindahan ke akaun di bawah, kemudian hantar resit kepada penganjur
                        melalui WhatsApp untuk pengesahan.
                    </p>

                    @php $bank = config('jelajah.payments.gateways.manual'); @endphp
                    <dl class="mt-5 space-y-3 rounded-xl bg-mist p-4 text-sm">
                        <div class="flex justify-between gap-3">
                            <dt class="text-ink-muted">Bank</dt>
                            <dd class="font-medium text-ink">{{ $bank['bank_name'] }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-ink-muted">No. Akaun</dt>
                            <dd class="flex items-center gap-2">
                                <span class="font-mono font-medium text-ink">{{ $bank['account_no'] }}</span>
                                <x-ui.copy-button :text="$bank['account_no']" label="Salin" size="sm" variant="ghost" />
                            </dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-ink-muted">Nama Akaun</dt>
                            <dd class="text-right font-medium text-ink text-pretty">{{ $bank['account_name'] }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 border-t border-hairline pt-3">
                            <dt class="text-ink-muted">Rujukan bayaran</dt>
                            <dd class="font-mono text-ink">{{ $registration->reference_no }}</dd>
                        </div>
                    </dl>

                    <x-ui.button
                        :href="'https://wa.me/'.($event->contact_phone ?: config('jelajah.support.phone')).'?text='.rawurlencode('Assalamualaikum. Saya telah membuat pembayaran untuk '.$event->title.'. Rujukan: '.$registration->reference_no)"
                        target="_blank" rel="noopener" variant="whatsapp" block class="mt-5" icon="whatsapp">
                        Hantar Resit di WhatsApp
                    </x-ui.button>
                </x-ui.card>

                <x-ui.button :href="route('tiket.show', $registration->public_token)"
                             variant="outline" block class="mt-4">
                    Lihat Pendaftaran Saya
                </x-ui.button>
            @endif
        </div>
    </section>
</x-layouts.public>
