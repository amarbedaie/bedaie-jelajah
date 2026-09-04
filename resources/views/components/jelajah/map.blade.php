@props(['states'])

@php
    // Negeri kecil: label diletak di luar bentuk dengan garis penunjuk,
    // kerana Putrajaya dan Labuan hanya beberapa piksel lebar pada peta.
    $tinyStates = ['PLS', 'KUL', 'PJY', 'LBN', 'MLK', 'PNG'];

    // Empat keadaan, satu rona. Kedalaman tanah liat yang membezakan —
    // Teal untuk keadaan yang sedang berlaku sekarang; selebihnya
    // kedalaman ungu jenama. Negeri yang belum dijelajahi dibiar kosong
    // sebagai kertas — itu maknanya secara harfiah, dan ia melepaskan
    // hujung pucat supaya keadaan lain boleh terpisah.
    $fills = [
        'berlangsung' => '#124A4A',
        'dijelajahi'  => '#8040C0',
        'akan_datang' => '#D0BBEB',
        'belum'       => '#F4F3F7',
    ];

    $legend = [
        'dijelajahi'  => 'Sudah dijelajahi',
        'akan_datang' => 'Program akan datang',
        'berlangsung' => 'Sedang berlangsung',
        'belum'       => 'Belum dijelajahi',
    ];

    $payload = $states->keyBy('slug')->map(fn ($s) => [
        'name'         => $s['name'],
        'slug'         => $s['slug'],
        'status'       => $s['status'],
        'events'       => $s['events'],
        'completed'    => $s['completed'],
        'upcoming'     => $s['upcoming'],
        'participants' => $s['participants'],
        'districts'    => $s['districts'],
        'high_demand'  => $s['high_demand'],
        'url'          => route('peta.negeri', $s['slug']),
        'invite'       => route('jemput', ['negeri' => $s['slug']]),
    ]);
@endphp

<div x-data="{
        states: {{ Illuminate\Support\Js::from($payload) }},
        active: null,
        focused: null,
        select(slug) { this.active = this.states[slug] ?? null; },
     }"
     {{ $attributes->merge(['class' => 'grid gap-6 lg:grid-cols-[minmax(0,1.55fr)_minmax(0,1fr)]']) }}>

    {{-- ── Peta ───────────────────────────────────────────────────
         Disembunyikan di bawah 640px: memaksa SVG selebar 40rem ke dalam
         skrin telefon memotong Sabah dan Sarawak sepenuhnya. Senarai negeri
         di sebelah menjadi laluan utama pada mobile. --}}
    <div class="relative hidden overflow-hidden rounded-card-lg border border-hairline
                bg-surface p-3 shadow-soft sm:block sm:p-5">
        <div class="no-scrollbar overflow-x-auto">
            {{-- Geometri sebenar daripada data terbuka rasmi DOSM Malaysia.
                 Semenanjung dan Borneo dikekalkan pada skala yang sama; hanya
                 jurang Laut China Selatan dirapatkan supaya tiada ruang mati. --}}
            {{-- Bukan role="img": anak-anaknya ialah butang negeri yang
                 boleh difokus, dan anak role="img" dianggap hiasan. --}}
            <svg viewBox="0 0 1486 668" role="group" aria-label="Peta jelajah BeDaie mengikut negeri"
                 class="aspect-[1486/668] w-full">
                <title>Peta Jelajah BeDaie di Malaysia</title>

                @foreach ($states as $state)
                    @php $fill = $fills[$state['status']] ?? $fills['belum']; @endphp

                    <g>
                        <path d="{{ $state['path'] }}"
                              fill="{{ $fill }}"
                              stroke="#FFFFFF"
                              stroke-width="1.6"
                              stroke-linejoin="round"
                              {{-- Fokus papan kekunci ditanda dengan lilitan tebal navy,
                                   kerana outline CSS tidak boleh dipercayai pada laluan SVG. --}}
                              class="cursor-pointer transition-opacity duration-200 hover:opacity-80
                                     focus-visible:outline-2 focus-visible:outline-offset-2
                                     focus-visible:outline-brand-400"
                              x-bind:opacity="active && active.slug !== '{{ $state['slug'] }}' ? 0.45 : 1"
                              x-bind:stroke="focused === '{{ $state['slug'] }}' ? '#17161C' : '#FFFFFF'"
                              x-bind:stroke-width="focused === '{{ $state['slug'] }}' ? 4 : 1.6"
                              x-on:click="select('{{ $state['slug'] }}')"
                              x-on:mouseenter="select('{{ $state['slug'] }}')"
                              x-on:focus="focused = '{{ $state['slug'] }}'; select('{{ $state['slug'] }}')"
                              x-on:blur="focused = null"
                              tabindex="0"
                              role="button"
                              x-on:keydown.enter.prevent="select('{{ $state['slug'] }}')"
                              x-on:keydown.space.prevent="select('{{ $state['slug'] }}')"
                              aria-label="{{ $state['name'] }} — {{ $legend[$state['status']] ?? '' }}, {{ $state['events'] }} program">
                            @if ($state['status'] === 'berlangsung')
                                {{-- Denyut lembut menandakan program sedang berlangsung --}}
                                <animate attributeName="opacity" values="1;0.55;1" dur="2.4s" repeatCount="indefinite" />
                            @endif
                        </path>

                        @php $tiny = in_array($state['code'], $tinyStates, true); @endphp

                        @if ($state['high_demand'])
                            <circle cx="{{ $state['label_x'] }}" cy="{{ $state['label_y'] - ($tiny ? 26 : 26) }}" r="9"
                                    fill="#A32017" stroke="#FFFFFF" stroke-width="2.5" class="pointer-events-none">
                                <title>Permintaan tinggi daripada komuniti</title>
                            </circle>
                        @endif

                        @if ($tiny)
                            {{-- Titik penanda + label di luar bentuk. Negeri dekat
                                 tepi kiri diberi label ke kanan supaya tidak terpotong. --}}
                            @php $flip = $state['label_x'] < 150; @endphp
                            <circle cx="{{ $state['label_x'] }}" cy="{{ $state['label_y'] }}" r="7"
                                    fill="{{ $fill }}" stroke="#FFFFFF" stroke-width="2.5"
                                    class="pointer-events-none" />
                            <line x1="{{ $state['label_x'] }}" y1="{{ $state['label_y'] }}"
                                  x2="{{ $state['label_x'] + ($flip ? 34 : -34) }}"
                                  y2="{{ $state['label_y'] + 4 }}"
                                  stroke="#E4E2EC" stroke-width="1.6" class="pointer-events-none" />
                            <text x="{{ $state['label_x'] + ($flip ? 40 : -40) }}"
                                  y="{{ $state['label_y'] + 9 }}"
                                  text-anchor="{{ $flip ? 'start' : 'end' }}"
                                  class="pointer-events-none select-none"
                                  font-size="22" font-weight="700" fill="#54525F">
                                {{ $state['code'] }}
                            </text>
                        @else
                            <text x="{{ $state['label_x'] }}" y="{{ $state['label_y'] }}"
                                  text-anchor="middle"
                                  class="pointer-events-none select-none"
                                  font-size="24" font-weight="700"
                                  fill="{{ $state['status'] === 'belum' ? '#54525F' : '#FFFFFF' }}">
                                {{ $state['code'] }}
                            </text>
                        @endif
                    </g>
                @endforeach
            </svg>
        </div>

        {{-- Legenda --}}
        <ul class="mt-4 flex flex-wrap gap-x-5 gap-y-2 border-t border-hairline pt-4">
            @foreach ($legend as $key => $label)
                <li class="flex items-center gap-2 text-xs text-ink-soft">
                    <span class="h-3 w-3 rounded-full ring-1 ring-inset ring-ink/12"
                          style="background: {{ $fills[$key] }}"></span>
                    {{ $label }}
                </li>
            @endforeach
            <li class="flex items-center gap-2 text-xs text-ink-soft">
                <span class="h-3 w-3 rounded-full bg-alert"></span> Permintaan tinggi
            </li>
        </ul>
    </div>

    {{-- ── Panel butiran + alternatif senarai (accessibility) ── --}}
    <div class="flex flex-col gap-4">
        <x-ui.card class="min-h-[13rem]">
            <template x-if="!active">
                <div class="flex h-full flex-col justify-center py-4 text-center">
                    <x-ui.icon name="map" class="mx-auto h-8 w-8 text-brand-300" />
                    <p class="mt-3 font-medium text-ink">Pilih negeri pada peta</p>
                    <p class="mt-1 text-sm text-ink-muted text-pretty">
                        Atau gunakan senarai negeri di bawah untuk melihat rekod jelajah.
                    </p>
                </div>
            </template>

            <template x-if="active">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-brand-600">Negeri</p>
                    <h3 class="mt-1 text-xl font-semibold text-ink" x-text="active.name"></h3>

                    <dl class="mt-4 grid grid-cols-2 gap-3">
                        <div class="rounded-xl bg-mist p-3">
                            <dt class="text-xs text-ink-muted">Program</dt>
                            <dd class="text-lg font-semibold text-ink" x-text="active.events"></dd>
                        </div>
                        <div class="rounded-xl bg-mist p-3">
                            <dt class="text-xs text-ink-muted">Peserta</dt>
                            <dd class="text-lg font-semibold text-ink"
                                x-text="active.participants.toLocaleString('ms-MY')"></dd>
                        </div>
                        <div class="rounded-xl bg-mist p-3">
                            <dt class="text-xs text-ink-muted">Daerah dilawati</dt>
                            <dd class="text-lg font-semibold text-ink" x-text="active.districts"></dd>
                        </div>
                        <div class="rounded-xl bg-mist p-3">
                            <dt class="text-xs text-ink-muted">Akan datang</dt>
                            <dd class="text-lg font-semibold text-ink" x-text="active.upcoming"></dd>
                        </div>
                    </dl>

                    <div class="mt-4 grid gap-2">
                        <a x-bind:href="active.url"
                           class="tap-target inline-flex items-center justify-center gap-2 rounded-lg bg-brand-600
                                  px-5 text-sm font-medium text-white transition hover:bg-char-800">
                            Lihat Butiran Negeri
                        </a>
                        <a x-bind:href="active.invite"
                           class="tap-target inline-flex items-center justify-center gap-2 rounded-full border
                                  border-brand-300 px-5 text-sm font-medium text-brand-700 transition hover:bg-brand-50">
                            Jemput BeDaie ke Negeri Ini
                        </a>
                    </div>
                </div>
            </template>
        </x-ui.card>

        {{-- Senarai negeri. Terbuka secara lalai pada mobile kerana peta
             disembunyikan di situ; tertutup pada desktop di mana peta memimpin. --}}
        <details class="group rounded-card border border-hairline bg-surface shadow-soft"
                 x-data x-init="$el.open = window.matchMedia('(max-width: 639px)').matches">
            <summary class="tap-target flex cursor-pointer items-center justify-between gap-2 px-5 text-sm font-medium text-ink">
                <span class="sm:hidden">Pilih negeri anda</span>
                <span class="hidden sm:inline">Senarai semua negeri</span>
                <x-ui.icon name="chevron-down" class="h-4 w-4 shrink-0 text-ink-muted transition group-open:rotate-180" />
            </summary>
            <ul class="max-h-96 space-y-0.5 overflow-y-auto border-t border-hairline p-2 sm:max-h-72">
                @foreach ($states as $state)
                    <li>
                        <a href="{{ route('peta.negeri', $state['slug']) }}"
                           class="tap-target flex items-center justify-between gap-3 rounded-xl px-3 text-sm hover:bg-mist">
                            <span class="flex items-center gap-2.5 text-ink">
                                <span class="h-2.5 w-2.5 shrink-0 rounded-full ring-1 ring-inset ring-ink/12"
                                      style="background: {{ $fills[$state['status']] ?? $fills['belum'] }}"></span>
                                {{ $state['name'] }}
                            </span>
                            <span class="shrink-0 text-xs text-ink-muted">
                                {{ $state['events'] ? $state['events'].' program' : 'Belum dijelajahi' }}
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </details>
    </div>
</div>
