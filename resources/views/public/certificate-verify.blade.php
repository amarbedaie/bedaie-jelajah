@php
    $valid = $certificate && $certificate->isValid();
@endphp

<x-layouts.public :title="'Semakan Sijil '.$number">
    <section class="jelajah-container py-12 sm:py-20">
        <div class="mx-auto max-w-2xl">
            @if (! $certificate)
                <x-ui.card class="text-center sm:p-10">
                    <div class="mx-auto grid h-16 w-16 place-items-center rounded-3xl bg-danger-soft">
                        <x-ui.icon name="x-circle" class="h-8 w-8 text-danger" />
                    </div>
                    <h1 class="mt-6 font-display text-2xl text-navy-900">Sijil Tidak Ditemui</h1>
                    <p class="mt-3 text-ink-soft text-pretty">
                        Tiada sijil dengan nombor <strong class="font-mono text-navy-900">{{ $number }}</strong>
                        dalam rekod kami. Sila semak semula nombor tersebut.
                    </p>
                    <x-ui.button :href="route('sijil.semak')" variant="primary" class="mt-6">
                        Cuba Nombor Lain
                    </x-ui.button>
                </x-ui.card>
            @else
                <x-ui.card class="sm:p-10">
                    <div class="text-center">
                        <div class="mx-auto grid h-16 w-16 place-items-center rounded-3xl
                                    {{ $valid ? 'bg-success-soft' : 'bg-danger-soft' }}">
                            <x-ui.icon :name="$valid ? 'check-circle' : 'x-circle'"
                                       class="h-8 w-8 {{ $valid ? 'text-success' : 'text-danger' }}" />
                        </div>
                        <h1 class="mt-6 font-display text-2xl text-navy-900 sm:text-3xl">
                            {{ $valid ? 'Sijil Ini Sah' : 'Sijil Ini Tidak Lagi Sah' }}
                        </h1>
                        @unless ($valid)
                            <p class="mt-3 text-ink-soft text-pretty">
                                Status: <strong class="text-danger">{{ $certificate->status->label() }}</strong>.
                                @if ($certificate->revoke_reason)
                                    {{ $certificate->revoke_reason }}
                                @endif
                            </p>
                        @endunless
                    </div>

                    <dl class="mt-8 divide-y divide-hairline border-y border-hairline">
                        @foreach ([
                            'Nombor Sijil' => $certificate->certificate_number,
                            'Nama Pemegang' => $certificate->recipient_name,
                            'Jenis Sijil' => $certificate->type->label(),
                            'Program' => $certificate->event?->title,
                            'Tarikh Program' => $certificate->event?->dateLabel(),
                            'Lokasi' => $certificate->event?->locationLabel(),
                            'Penceramah' => $certificate->event?->speaker?->name,
                            'Jam Pembelajaran' => $certificate->learning_hours
                                ? $certificate->learning_hours.' jam' : null,
                            'Dikeluarkan Pada' => $certificate->issued_at?->translatedFormat('j F Y'),
                        ] as $label => $value)
                            @if ($value)
                                <div class="flex flex-col gap-1 py-3.5 sm:flex-row sm:gap-4">
                                    <dt class="w-44 shrink-0 text-sm text-ink-muted">{{ $label }}</dt>
                                    <dd class="text-sm text-navy-900 text-pretty">{{ $value }}</dd>
                                </div>
                            @endif
                        @endforeach
                    </dl>

                    <div class="mt-8 flex flex-col items-center gap-5 sm:flex-row sm:items-start">
                        @if ($qrSvg)
                            <div class="shrink-0 rounded-xl bg-white p-2 ring-1 ring-hairline">{!! $qrSvg !!}</div>
                        @endif
                        <div class="flex-1 text-center sm:text-left">
                            <p class="text-sm text-ink-soft text-pretty">
                                Pengesahan ini dijana terus daripada pangkalan data rasmi BeDaie Jelajah.
                            </p>
                            @if ($valid)
                                <x-ui.button :href="$certificate->downloadUrl()" target="_blank"
                                             variant="primary" size="sm" class="mt-4" icon="download">
                                    Muat Turun PDF
                                </x-ui.button>
                            @endif
                        </div>
                    </div>
                </x-ui.card>
            @endif
        </div>
    </section>
</x-layouts.public>
