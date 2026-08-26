<x-layouts.public :title="'Rakaman — '.$event->title">
    <section class="border-b border-hairline bg-surface">
        <div class="jelajah-container py-8">
            <a href="{{ route('tiket.show', $registration->public_token) }}"
               class="inline-flex items-center gap-1.5 text-sm font-medium text-ink-soft hover:text-navy-900">
                <x-ui.icon name="arrow-left" class="h-4 w-4" /> Kembali ke tiket
            </a>
            <p class="mt-4 text-xs font-semibold uppercase tracking-[0.16em] text-brand-700">Rakaman Program</p>
            <h1 class="mt-2 font-display text-2xl text-navy-900 sm:text-3xl text-pretty">{{ $event->title }}</h1>
            <p class="mt-1.5 text-sm text-ink-muted">
                {{ $event->dateLabel() }} &middot; {{ $event->locationLabel() }}
            </p>
        </div>
    </section>

    <section class="jelajah-container py-10 sm:py-14">
        @if ($recordings->isEmpty())
            <x-ui.empty-state icon="play" title="Rakaman belum tersedia"
                description="Rakaman program ini sedang disediakan. Kami akan maklumkan melalui WhatsApp sebaik ia siap.">
                <x-ui.button :href="route('tiket.show', $registration->public_token)"
                             variant="outline" class="mt-5">Kembali ke Tiket</x-ui.button>
            </x-ui.empty-state>
        @else
            @unless ($registration->hasAttended())
                <x-ui.alert variant="info" icon="info" class="mb-6" title="Sebahagian rakaman untuk peserta yang hadir">
                    Rekod kehadiran anda tidak dijumpai untuk program ini. Rakaman yang ditandakan
                    "peserta hadir sahaja" tidak boleh dibuka. Jika anda hadir tetapi QR tidak sempat
                    diimbas, hubungi pasukan BeDaie.
                </x-ui.alert>
            @endunless

            <ul class="grid gap-5 sm:grid-cols-2">
                @foreach ($recordings as $recording)
                    @php
                        $locked = ! $recording->viewableBy($registration);
                        $reason = $recording->lockedReason($registration);
                    @endphp
                    <li class="relative flex h-full flex-col rounded-[--radius-card] border border-hairline
                               bg-surface p-5 {{ $locked ? '' : 'transition hover:border-brand-200 hover:shadow-soft' }}">
                        <div class="flex items-start gap-3">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl
                                         {{ $locked ? 'bg-mist' : 'bg-brand-50' }}">
                                <x-ui.icon :name="$locked ? 'lock' : $recording->type->icon()"
                                           class="h-5 w-5 {{ $locked ? 'text-ink-muted' : 'text-brand-700' }}" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <h2 class="font-semibold text-navy-900 text-pretty">
                                    @if ($locked)
                                        {{ $recording->title }}
                                    @else
                                        <a href="{{ route('rakaman.show', [$registration->public_token, $recording]) }}"
                                           class="after:absolute after:inset-0 hover:text-brand-700">
                                            {{ $recording->title }}
                                        </a>
                                    @endif
                                </h2>
                                <p class="mt-0.5 text-xs text-ink-muted">
                                    {{ $recording->type->label() }}
                                    @if ($recording->durationLabel())
                                        &middot; {{ $recording->durationLabel() }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        @if ($recording->description)
                            <p class="mt-3 flex-1 text-sm leading-relaxed text-ink-soft text-pretty">
                                {{ $recording->summary() }}
                            </p>
                        @endif

                        <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-hairline pt-4">
                            <x-ui.badge :color="$recording->visibility->color()">
                                {{ $recording->visibility->label() }}
                            </x-ui.badge>

                            @if ($locked)
                                <span class="text-xs text-ink-muted text-pretty">{{ $reason }}</span>
                            @else
                                <span class="ml-auto inline-flex items-center gap-1.5 text-sm font-medium text-brand-700">
                                    Tonton <x-ui.icon name="arrow-right" class="h-4 w-4" />
                                </span>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</x-layouts.public>
