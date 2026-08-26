<div class="relative w-full max-w-sm" x-data="{ fokus: false }" x-on:click.outside="fokus = false; $wire.close()">
    <label for="carian-global" class="sr-only">Carian global</label>

    <div class="relative">
        <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-ink-muted">
            <x-ui.icon name="search" class="h-4 w-4" />
        </span>
        <input id="carian-global" type="search"
               wire:model.live.debounce.350ms="q"
               x-on:focus="fokus = true"
               placeholder="Cari permohonan, program, peserta…"
               class="tap-target w-full rounded-full border border-hairline bg-mist/60 pl-10 pr-4 text-sm
                      text-ink placeholder:text-ink-muted transition focus:border-brand-400 focus:bg-surface
                      focus:outline-none focus:ring-4 focus:ring-brand-500/15" />
    </div>

    @if ($open)
        <div class="absolute right-0 z-50 mt-2 w-[min(92vw,26rem)] overflow-hidden rounded-2xl
                    border border-hairline bg-surface shadow-lift">
            @if (empty($groups))
                <p class="px-5 py-6 text-center text-sm text-ink-muted">
                    Tiada hasil untuk "{{ $q }}".
                </p>
            @else
                <div class="max-h-[70vh] overflow-y-auto">
                    @foreach ($groups as $label => $rows)
                        <p class="border-b border-hairline bg-mist/60 px-4 py-1.5 text-[0.68rem]
                                  font-semibold uppercase tracking-[0.14em] text-ink-muted">
                            {{ $label }}
                        </p>
                        <ul class="divide-y divide-hairline">
                            @foreach ($rows as $row)
                                <li>
                                    <a href="{{ $row['url'] }}" class="flex items-start gap-3 px-4 py-3 hover:bg-mist/60">
                                        <x-ui.icon :name="$row['icon']" class="mt-0.5 h-4 w-4 shrink-0 text-brand-500" />
                                        <span class="min-w-0">
                                            <span class="block truncate text-sm font-medium text-navy-900">{{ $row['title'] }}</span>
                                            <span class="block truncate text-xs text-ink-muted">{{ $row['meta'] }}</span>
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</div>
