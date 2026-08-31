<x-layouts.admin title="Kandungan Website" heading="Kandungan Website">
    <p class="mb-6 text-ink-soft text-pretty">
        Teks yang boleh diurus tanpa mengubah kod. Medan kosong akan menggunakan teks lalai sistem.
    </p>

    <x-ui.alert variant="warning" icon="alert" class="mb-6">
        Polisi privasi dan terma penggunaan masih menggunakan teks placeholder.
        Ia perlu disemak oleh penasihat undang-undang sebelum digunakan secara rasmi.
        Tulis teks rasmi pada medan <code class="font-mono">legal.privacy</code> dan
        <code class="font-mono">legal.terms</code> di bawah.
    </x-ui.alert>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)]">
        <div>
            <livewire:admin.settings-editor group="kandungan" />
        </div>

        <aside>
            <x-ui.card class="lg:sticky lg:top-24">
                <h2 class="font-semibold text-ink">Halaman Rasmi</h2>
                <ul class="mt-4 space-y-3">
                    @foreach ([
                        ['Polisi Privasi', route('privasi'), $legal['privacy']],
                        ['Terma Penggunaan', route('terma'), $legal['terms']],
                    ] as [$label, $url, $value])
                        <li class="flex items-center justify-between gap-3 rounded-xl border border-hairline p-4">
                            <div class="min-w-0">
                                <p class="font-medium text-ink">{{ $label }}</p>
                                <p class="mt-0.5 text-[0.8125rem] text-ink-muted">
                                    {{ $value ? 'Teks tersuai disimpan' : 'Menggunakan teks lalai' }}
                                </p>
                            </div>
                            <x-ui.button :href="$url" target="_blank" variant="ghost" size="sm" icon="external">
                                Lihat
                            </x-ui.button>
                        </li>
                    @endforeach
                </ul>

                <p class="mt-4 text-[0.8125rem] text-ink-muted text-pretty">
                    Teks menyokong format Markdown — gunakan <code class="font-mono">##</code> untuk
                    tajuk dan <code class="font-mono">-</code> untuk senarai.
                </p>
            </x-ui.card>
        </aside>
    </div>
</x-layouts.admin>
