<x-layouts.admin :title="$user->name" :heading="$user->name">
    <a href="{{ route('admin.penggerak') }}"
       class="inline-flex items-center gap-1.5 text-sm font-medium text-ink-soft hover:text-ink">
        <x-ui.icon name="arrow-left" class="h-4 w-4" /> Semua penggerak
    </a>

    <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.6fr)]">
        <x-ui.card class="lg:sticky lg:top-24 lg:self-start">
            <div class="flex items-center gap-4">
                <span class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-brand-600
                             font-display text-xl text-white">
                    {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                </span>
                <div class="min-w-0">
                    <p class="font-semibold text-ink text-pretty">{{ $user->name }}</p>
                    <p class="mt-0.5 text-sm text-ink-muted">{{ $user->role->label() }}</p>
                </div>
            </div>

            <dl class="mt-6 space-y-3 text-sm">
                @foreach ([
                    'WhatsApp' => $user->phone,
                    'E-mel' => $user->email,
                    'Kawasan' => trim(($user->district?->name ? $user->district->name.', ' : '').($user->state?->name ?? '')),
                    'Organisasi' => $user->mobilizerProfile?->organization_name,
                    'Latar belakang' => $user->mobilizerProfile?->background?->label(),
                    'Ahli sejak' => $user->created_at->translatedFormat('F Y'),
                ] as $label => $value)
                    @if ($value)
                        <div class="flex justify-between gap-3">
                            <dt class="text-ink-muted">{{ $label }}</dt>
                            <dd class="text-right text-ink text-pretty">
                                @if ($label === 'WhatsApp')
                                    <a href="https://wa.me/{{ $value }}" target="_blank" rel="noopener"
                                       class="font-medium text-brand-600 hover:underline">{{ $value }}</a>
                                @else
                                    {{ $value }}
                                @endif
                            </dd>
                        </div>
                    @endif
                @endforeach
            </dl>

            @if ($user->mobilizerProfile?->about)
                <p class="mt-5 rounded-xl bg-mist p-3.5 text-sm text-ink-soft text-pretty">
                    {{ $user->mobilizerProfile->about }}
                </p>
            @endif

            <x-ui.button :href="'https://wa.me/'.$user->phone" target="_blank" rel="noopener"
                         variant="whatsapp" block class="mt-5" icon="whatsapp">
                Hubungi di WhatsApp
            </x-ui.button>

            {{-- Akaun Penggerak dicipta automatik tanpa kata laluan yang
                 diketahui — ini selalunya satu-satunya jalan masuk mereka. --}}
            <form method="POST" action="{{ route('admin.penggerak.pautan-masuk', $user) }}" class="mt-2.5">
                @csrf
                <x-ui.button type="submit" variant="outline" block icon="lock">
                    Hantar Pautan Log Masuk
                </x-ui.button>
            </form>
            <p class="mt-2 text-[0.8125rem] text-ink-muted text-pretty">
                Pautan sekali-guna, sah 30 minit. Guna ini jika Penggerak tidak dapat masuk.
            </p>
        </x-ui.card>

        <div class="space-y-6">
            <x-ui.card>
                <h2 class="font-semibold text-ink">Permohonan</h2>
                @if ($user->applications->isEmpty())
                    <p class="mt-3 text-sm text-ink-muted">Belum ada permohonan.</p>
                @else
                    <ul class="mt-4 divide-y divide-hairline">
                        @foreach ($user->applications as $application)
                            <li class="flex flex-wrap items-center justify-between gap-3 py-3">
                                <div class="min-w-0">
                                    <a href="{{ route('admin.permohonan.show', $application) }}"
                                       class="font-medium text-ink hover:text-brand-700">
                                        {{ $application->venue_name }}
                                    </a>
                                    <p class="text-[0.8125rem] text-ink-muted">
                                        {{ $application->reference_no }} &middot; {{ $application->state?->name }}
                                    </p>
                                </div>
                                <x-ui.badge :color="$application->status->color()">
                                    {{ $application->status->label() }}
                                </x-ui.badge>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>

            <x-ui.card>
                <h2 class="font-semibold text-ink">Program Digerakkan</h2>
                @if ($user->mobilizedEvents->isEmpty())
                    <p class="mt-3 text-sm text-ink-muted">Belum ada program.</p>
                @else
                    <ul class="mt-4 divide-y divide-hairline">
                        @foreach ($user->mobilizedEvents as $event)
                            <li class="flex flex-wrap items-center justify-between gap-3 py-3">
                                <div class="min-w-0">
                                    <a href="{{ route('admin.program.show', $event) }}"
                                       class="font-medium text-ink hover:text-brand-700">{{ $event->title }}</a>
                                    <p class="text-[0.8125rem] text-ink-muted">
                                        {{ $event->dateLabel() }} &middot; {{ $event->venue?->name ?? $event->state?->name }}
                                    </p>
                                </div>
                                <x-ui.badge color="grey">{{ number_format($event->attended_count) }} hadir</x-ui.badge>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>
        </div>
    </div>
</x-layouts.admin>
