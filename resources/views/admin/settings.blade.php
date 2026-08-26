<x-layouts.admin title="Tetapan" heading="Tetapan Sistem">
    <div class="grid gap-6 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)]">
        <div>
            <livewire:admin.settings-editor />
        </div>

        <aside class="space-y-5">
            <x-ui.card>
                <h2 class="font-semibold text-navy-900">Gateway Pembayaran</h2>
                <p class="mt-1.5 text-sm text-ink-soft text-pretty">
                    Gateway aktif: <strong class="text-navy-900">{{ $activeGateway }}</strong>
                </p>

                <ul class="mt-4 space-y-2.5">
                    @foreach ($gateways as $key => $gateway)
                        <li class="flex items-center justify-between gap-3 rounded-xl border border-hairline p-3.5">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-navy-900">{{ $gateway->label() }}</p>
                                <p class="font-mono text-xs text-ink-muted">{{ $key }}</p>
                            </div>
                            <x-ui.badge :color="$key === $activeGateway ? 'success' : 'grey'">
                                {{ $key === $activeGateway ? 'Aktif' : 'Sedia' }}
                            </x-ui.badge>
                        </li>
                    @endforeach
                </ul>

                <x-ui.alert variant="info" icon="info" class="mt-4">
                    Kredensial gateway disimpan dalam <code class="font-mono">.env</code>, bukan
                    dalam pangkalan data. Gateway yang belum dikonfigurasi jatuh balik ke mod manual.
                </x-ui.alert>
            </x-ui.card>

            <x-ui.card>
                <h2 class="font-semibold text-navy-900">Jenama</h2>
                <p class="mt-1.5 text-xs text-ink-muted text-pretty">
                    Nilai ini ditetapkan dalam <code class="font-mono">.env</code>.
                </p>
                <dl class="mt-4 space-y-3 text-sm">
                    @foreach ([
                        'Jenama' => config('jelajah.brand'),
                        'Penerbit' => config('jelajah.org'),
                        'Tagline' => config('jelajah.tagline'),
                        'Slogan' => config('jelajah.slogan'),
                        'Motto' => config('jelajah.motto'),
                        'Telefon sokongan' => config('jelajah.support.phone'),
                        'E-mel sokongan' => config('jelajah.support.email'),
                    ] as $label => $value)
                        <div class="flex justify-between gap-3">
                            <dt class="text-ink-muted">{{ $label }}</dt>
                            <dd class="text-right text-navy-900 text-pretty">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </x-ui.card>
        </aside>
    </div>
</x-layouts.admin>
