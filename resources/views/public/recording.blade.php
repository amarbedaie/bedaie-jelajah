<x-layouts.public :title="$recording->title.' — '.$event->title">
    <section class="jelajah-container py-8 sm:py-12">
        <a href="{{ route('rakaman.index', $registration->public_token) }}"
           class="inline-flex items-center gap-1.5 text-sm font-medium text-ink-soft hover:text-ink">
            <x-ui.icon name="arrow-left" class="h-4 w-4" /> Semua rakaman
        </a>

        <div class="mt-5 grid gap-8 lg:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)]">
            <div>
                @if ($recording->embedUrl())
                    <div class="overflow-hidden rounded-card-lg border border-hairline bg-char-900">
                        <div class="aspect-video">
                            <iframe src="{{ $recording->embedUrl() }}"
                                    title="{{ $recording->title }}"
                                    class="h-full w-full"
                                    loading="lazy"
                                    referrerpolicy="strict-origin-when-cross-origin"
                                    allow="accelerometer; encrypted-media; picture-in-picture; fullscreen"
                                    allowfullscreen></iframe>
                        </div>
                    </div>
                @elseif ($recording->downloadUrl())
                    <div class="rounded-card-lg border border-hairline bg-surface p-8 text-center">
                        <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-clay-50">
                            <x-ui.icon :name="$recording->type->icon()" class="h-7 w-7 text-clay-700" />
                        </div>
                        <p class="mt-4 font-medium text-ink">{{ $recording->type->label() }}</p>
                        <x-ui.button :href="$recording->downloadUrl()" target="_blank" rel="noopener"
                                     variant="primary" class="mt-5" icon="external">
                            Buka {{ $recording->type->label() }}
                        </x-ui.button>
                    </div>
                @endif

                <h1 class="mt-6 font-display text-2xl text-ink sm:text-3xl text-pretty">
                    {{ $recording->title }}
                </h1>

                <p class="mt-2 text-sm text-ink-muted">
                    {{ $event->title }} &middot; {{ $event->dateLabel() }}
                    @if ($recording->durationLabel())
                        &middot; {{ $recording->durationLabel() }}
                    @endif
                </p>

                @if ($recording->description)
                    <div class="mt-5 space-y-3 leading-relaxed text-ink-soft">
                        @foreach (preg_split('/\n\s*\n/', trim($recording->description)) as $paragraph)
                            <p class="text-pretty">{{ $paragraph }}</p>
                        @endforeach
                    </div>
                @endif
            </div>

            <aside class="space-y-5">
                <x-ui.card>
                    <h2 class="font-semibold text-ink">Program Ini</h2>
                    <p class="mt-2 text-sm text-ink-soft text-pretty">{{ $event->title }}</p>
                    <dl class="mt-4 space-y-2.5 text-sm">
                        <div class="flex justify-between gap-3">
                            <dt class="text-ink-muted">Tarikh</dt>
                            <dd class="text-right text-ink">{{ $event->dateLabel() }}</dd>
                        </div>
                        @if ($event->speaker)
                            <div class="flex justify-between gap-3">
                                <dt class="text-ink-muted">Penceramah</dt>
                                <dd class="text-right text-ink">{{ $event->speaker->name }}</dd>
                            </div>
                        @endif
                    </dl>

                    <x-ui.button :href="route('tiket.show', $registration->public_token)"
                                 variant="outline" size="sm" block class="mt-4">
                        Tiket & Sijil Saya
                    </x-ui.button>
                </x-ui.card>

                @if ($others->isNotEmpty())
                    <x-ui.card>
                        <h2 class="font-semibold text-ink">Rakaman Lain</h2>
                        <ul class="mt-4 space-y-3">
                            @foreach ($others as $other)
                                @php $locked = ! $other->viewableBy($registration); @endphp
                                <li>
                                    @if ($locked)
                                        <span class="flex items-start gap-2.5 text-sm text-ink-muted">
                                            <x-ui.icon name="lock" class="mt-0.5 h-4 w-4 shrink-0" />
                                            {{ $other->title }}
                                        </span>
                                    @else
                                        <a href="{{ route('rakaman.show', [$registration->public_token, $other]) }}"
                                           class="flex items-start gap-2.5 text-sm text-ink hover:text-clay-700">
                                            <x-ui.icon :name="$other->type->icon()" class="mt-0.5 h-4 w-4 shrink-0 text-clay-700" />
                                            <span class="text-pretty">{{ $other->title }}</span>
                                        </a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </x-ui.card>
                @endif
            </aside>
        </div>
    </section>
</x-layouts.public>
