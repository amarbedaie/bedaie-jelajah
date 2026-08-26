@php
    $outcome = $result['outcome'] ?? null;
    $tones = [
        'checked_in' => ['bg-success', 'check-circle', 'Berjaya'],
        'duplicate'  => ['bg-warning', 'alert', 'Sudah Check-in'],
        'fail'       => ['bg-danger', 'x-circle', 'Tidak Berjaya'],
    ];
    [$tone, $icon, $heading] = $tones[$outcome] ?? ['bg-navy-900', 'qr', ''];
@endphp

<div x-data="qrScanner($wire)"
     x-on:imbasan-selesai.window="maklum($event.detail.outcome)"
     class="min-h-screen bg-navy-900 pb-24">

    {{-- ── Kepala ─────────────────────────────────────────── --}}
    <header class="sticky top-0 z-30 border-b border-white/10 bg-navy-900/95 backdrop-blur">
        <div class="jelajah-container">
            <div class="flex h-16 items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="truncate text-sm font-medium text-white">{{ $event->title }}</p>
                    <p class="truncate text-xs text-white/50">{{ $event->venue?->name ?? $event->locationLabel() }}</p>
                </div>
                <a href="{{ auth()->user()->isAdmin() ? route('admin.kehadiran.show', $event) : route('penggerak.program.show', $event) }}"
                   class="tap-target grid shrink-0 place-items-center rounded-xl text-white/60 hover:bg-white/10 hover:text-white"
                   aria-label="Keluar dari pengimbas">
                    <x-ui.icon name="x" class="h-5 w-5" />
                </a>
            </div>
        </div>
    </header>

    {{-- ── Kiraan langsung ────────────────────────────────── --}}
    <div class="jelajah-container pt-5">
        <dl class="grid grid-cols-4 gap-2">
            @foreach ([
                ['hadir', 'Hadir', 'text-success'],
                ['belum_hadir', 'Belum Hadir', 'text-white'],
                ['berdaftar', 'Berdaftar', 'text-white'],
                ['walk_in', 'Walk-in', 'text-brand-300'],
            ] as [$key, $label, $colour])
                <div class="rounded-xl bg-white/8 p-3 text-center ring-1 ring-white/10">
                    <dd class="font-display text-xl {{ $colour }}">{{ number_format($stats[$key]) }}</dd>
                    <dt class="mt-0.5 text-xs uppercase tracking-wider text-white/45">{{ $label }}</dt>
                </div>
            @endforeach
        </dl>
    </div>

    {{-- ── Keputusan imbasan ──────────────────────────────── --}}
    @if ($result)
        <div class="jelajah-container pt-5" wire:key="result-{{ md5(json_encode($result)) }}">
            <div class="overflow-hidden rounded-card-lg {{ $tone }} p-5 text-white">
                <div class="flex items-start gap-4">
                    <x-ui.icon :name="$icon" class="mt-0.5 h-7 w-7 shrink-0" />
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold uppercase tracking-wider opacity-90">{{ $heading }}</p>

                        @if ($result['registration'])
                            <p class="mt-1.5 text-xl font-semibold leading-snug text-pretty">
                                {{ $result['registration']['name'] }}
                            </p>
                            <p class="mt-1 text-sm opacity-85">
                                {{ $result['registration']['reference_no'] }}
                                &middot; {{ $result['registration']['phone'] }}
                                @if ($result['registration']['guests'] > 0)
                                    &middot; +{{ $result['registration']['guests'] }} ahli keluarga
                                @endif
                            </p>
                        @endif

                        @if ($result['message'])
                            <p class="mt-2 text-sm opacity-90 text-pretty">{{ $result['message'] }}</p>
                        @endif

                        @if ($result['checked_in_at'])
                            <p class="mt-1 text-sm opacity-75">Masa check-in: {{ $result['checked_in_at'] }}</p>
                        @endif
                    </div>

                    <button type="button" wire:click="dismiss"
                            class="tap-target grid shrink-0 place-items-center rounded-xl hover:bg-black/10"
                            aria-label="Tutup keputusan">
                        <x-ui.icon name="x" class="h-5 w-5" />
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Kamera ─────────────────────────────────────────── --}}
    <div class="jelajah-container pt-5">
        <div class="overflow-hidden rounded-card-lg border border-white/12 bg-white/6">
            <div id="qr-reader" class="aspect-square w-full bg-black/40" x-show="kamera" x-cloak></div>

            <div x-show="!kamera" class="p-8 text-center">
                <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-white/10">
                    <x-ui.icon name="camera" class="h-7 w-7 text-brand-300" />
                </div>
                <p class="mt-4 font-medium text-white">Imbas QR Peserta</p>
                <p class="mt-1.5 text-sm text-white/60 text-pretty">
                    Benarkan akses kamera untuk mula mengimbas.
                </p>
                <button type="button" x-on:click="mula()"
                        class="tap-target mt-5 inline-flex items-center gap-2 rounded-full bg-brand-500 px-6
                               font-medium text-white transition hover:bg-brand-600">
                    <x-ui.icon name="qr" class="h-5 w-5" /> Buka Kamera
                </button>
                <p x-show="ralat" x-text="ralat" x-cloak class="mt-4 text-sm text-red-300"></p>
            </div>

            <div x-show="kamera" x-cloak class="border-t border-white/10 p-3 text-center">
                <button type="button" x-on:click="henti()"
                        class="tap-target rounded-full px-5 text-sm text-white/70 hover:bg-white/10 hover:text-white">
                    Hentikan Kamera
                </button>
            </div>
        </div>
    </div>

    {{-- ── Kod manual ─────────────────────────────────────── --}}
    <div class="jelajah-container pt-4">
        <form wire:submit="scan(manualCode)" class="flex gap-2">
            <label for="kod-manual" class="sr-only">Kod QR manual</label>
            <input id="kod-manual" wire:model="manualCode" type="text"
                   placeholder="Masukkan kod QR secara manual"
                   class="tap-target w-full rounded-xl border border-white/15 bg-white/8 px-4 text-base
                          text-white placeholder:text-white/40 focus:border-brand-400 focus:outline-none
                          focus:ring-4 focus:ring-brand-500/20" />
            <button type="submit"
                    class="tap-target shrink-0 rounded-xl bg-white/12 px-5 font-medium text-white hover:bg-white/20">
                Semak
            </button>
        </form>
    </div>

    {{-- ── Carian manual ──────────────────────────────────── --}}
    <div class="jelajah-container pt-6">
        <h2 class="text-sm font-semibold uppercase tracking-wider text-white/45">Carian Peserta</h2>

        <div class="mt-3">
            <label for="cari" class="sr-only">Cari nama, telefon atau rujukan</label>
            <input id="cari" wire:model.live.debounce.400ms="search" type="search"
                   placeholder="Cari nama, telefon atau rujukan…"
                   class="tap-target w-full rounded-xl border border-white/15 bg-white/8 px-4 text-base
                          text-white placeholder:text-white/40 focus:border-brand-400 focus:outline-none
                          focus:ring-4 focus:ring-brand-500/20" />
        </div>

        @if (strlen($search) >= 3)
            @if ($matches->isEmpty())
                <p class="mt-4 rounded-xl bg-white/6 p-4 text-center text-sm text-white/60">
                    Tiada peserta sepadan. Daftarkan sebagai walk-in di bawah.
                </p>
            @else
                <ul class="mt-3 space-y-2">
                    @foreach ($matches as $match)
                        <li class="flex items-center justify-between gap-3 rounded-xl bg-white/8 p-3.5">
                            <div class="min-w-0">
                                <p class="truncate font-medium text-white">{{ $match->name }}</p>
                                <p class="mt-0.5 truncate text-xs text-white/50">
                                    {{ $match->reference_no }} &middot; {{ $match->maskedPhone() }}
                                    @if ($match->guests_count > 0)
                                        &middot; +{{ $match->guests_count }}
                                    @endif
                                </p>
                            </div>

                            @if ($match->hasAttended())
                                <span class="shrink-0 rounded-full bg-success/20 px-3 py-1.5 text-xs font-medium text-success">
                                    Hadir {{ $match->attendance->checked_in_at->format('g:ia') }}
                                </span>
                            @else
                                <button type="button" wire:click="checkInManually({{ $match->id }})"
                                        wire:loading.attr="disabled"
                                        class="tap-target shrink-0 rounded-full bg-brand-500 px-4 text-sm
                                               font-medium text-white hover:bg-brand-600">
                                    Check-in
                                </button>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        @endif
    </div>

    {{-- ── Walk-in ────────────────────────────────────────── --}}
    <div class="jelajah-container pt-6">
        @if (! $showWalkIn)
            <button type="button" wire:click="$set('showWalkIn', true)"
                    class="tap-target flex w-full items-center justify-center gap-2 rounded-xl border
                           border-dashed border-white/20 px-5 text-sm text-white/70 hover:bg-white/6 hover:text-white">
                <x-ui.icon name="plus" class="h-4 w-4" /> Daftar Peserta Walk-in
            </button>
        @else
            <form wire:submit="registerWalkIn"
                  class="rounded-card border border-white/12 bg-white/8 p-5">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="font-semibold text-white">Pendaftaran Walk-in</h2>
                    <button type="button" wire:click="$set('showWalkIn', false)"
                            class="tap-target grid place-items-center rounded-xl text-white/50 hover:bg-white/10"
                            aria-label="Tutup borang walk-in">
                        <x-ui.icon name="x" class="h-4 w-4" />
                    </button>
                </div>

                <div class="mt-4 space-y-3">
                    <div>
                        <label for="wi-nama" class="mb-1.5 block text-sm text-white/70">Nama penuh</label>
                        <input id="wi-nama" wire:model="walkInName" type="text"
                               class="tap-target w-full rounded-xl border border-white/15 bg-white/8 px-4
                                      text-base text-white placeholder:text-white/40 focus:border-brand-400
                                      focus:outline-none focus:ring-4 focus:ring-brand-500/20" />
                        @error('walkInName') <p class="mt-1.5 text-sm text-red-300">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="wi-tel" class="mb-1.5 block text-sm text-white/70">Nombor telefon</label>
                        <input id="wi-tel" wire:model="walkInPhone" type="tel" inputmode="tel"
                               class="tap-target w-full rounded-xl border border-white/15 bg-white/8 px-4
                                      text-base text-white placeholder:text-white/40 focus:border-brand-400
                                      focus:outline-none focus:ring-4 focus:ring-brand-500/20" />
                        @error('walkInPhone') <p class="mt-1.5 text-sm text-red-300">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="wi-jantina" class="mb-1.5 block text-sm text-white/70">Jantina (pilihan)</label>
                        <select id="wi-jantina" wire:model="walkInGender"
                                class="tap-target w-full rounded-xl border border-white/15 bg-navy-900 px-4
                                       text-base text-white focus:border-brand-400 focus:outline-none">
                            <option value="">Tidak dinyatakan</option>
                            <option value="lelaki">Lelaki</option>
                            <option value="perempuan">Perempuan</option>
                        </select>
                    </div>
                </div>

                <button type="submit" wire:loading.attr="disabled"
                        class="tap-target mt-5 w-full rounded-full bg-brand-500 px-6 font-medium text-white
                               hover:bg-brand-600 disabled:opacity-60">
                    <span wire:loading.remove wire:target="registerWalkIn">Daftar & Check-in</span>
                    <span wire:loading wire:target="registerWalkIn">Memproses…</span>
                </button>
            </form>
        @endif
    </div>
</div>
