<x-layouts.app title="Program Saya" nav="peserta" heading="Program Saya"
               subheading="Semua program yang anda daftarkan.">

    @if ($registrations->isEmpty())
        <x-ui.empty-state icon="ticket" title="Belum ada pendaftaran"
            description="Jelajah program yang dibuka dan daftar untuk mula membina Pasport Ilmu anda.">
            <x-ui.button :href="route('program.index')" variant="primary" class="mt-5">Lihat Program</x-ui.button>
        </x-ui.empty-state>
    @else
        <ul class="space-y-4">
            @foreach ($registrations as $registration)
                @php $event = $registration->event; @endphp
                <li class="rounded-card border border-hairline bg-surface p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-medium text-navy-900 text-pretty">{{ $event->title }}</p>
                            <p class="mt-0.5 text-sm text-ink-muted text-pretty">
                                {{ $event->dateLabel() }} &middot; {{ $event->timeLabel() }}<br>
                                {{ $event->venue?->name ?? $event->locationLabel() }}
                            </p>
                            <p class="mt-1.5 font-mono text-xs text-ink-muted">{{ $registration->reference_no }}</p>
                        </div>

                        <div class="flex flex-col items-end gap-2">
                            <x-ui.badge :color="$registration->status->color()" dot>
                                {{ $registration->status->label() }}
                            </x-ui.badge>
                            @if ($registration->hasAttended())
                                <x-ui.badge color="success" icon="check-circle">Hadir</x-ui.badge>
                            @endif
                            @if ($registration->payment && $registration->payment->amount > 0)
                                <x-ui.badge :color="$registration->payment->status->color()">
                                    {{ $registration->payment->status->label() }}
                                </x-ui.badge>
                            @endif
                        </div>
                    </div>

                    @if ($registration->guests->isNotEmpty())
                        <p class="mt-3 text-sm text-ink-soft text-pretty">
                            Bersama: {{ $registration->guests->pluck('name')->join(', ') }}
                        </p>
                    @endif

                    <div class="mt-4 flex flex-wrap gap-2.5 border-t border-hairline pt-4">
                        <x-ui.button :href="route('tiket.show', $registration->public_token)"
                                     variant="navy" size="sm" icon="qr">
                            Tiket & QR
                        </x-ui.button>
                        <x-ui.button :href="$event->publicUrl()" variant="outline" size="sm">
                            Halaman Program
                        </x-ui.button>
                        @if ($event->hasEnded() && $registration->hasAttended() && ! $registration->feedback)
                            <x-ui.button :href="route('maklum-balas.show', $registration->public_token)"
                                         variant="outline" size="sm" icon="star">
                                Beri Maklum Balas
                            </x-ui.button>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>

        <div class="mt-8">{{ $registrations->links() }}</div>
    @endif
</x-layouts.app>
