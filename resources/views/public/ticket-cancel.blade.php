<x-layouts.public title="Batalkan Pendaftaran">
    <section class="jelajah-container py-12 sm:py-20">
        <div class="mx-auto max-w-lg">
            <x-ui.card class="sm:p-8">
                <div class="text-center">
                    <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-danger-soft">
                        <x-ui.icon name="alert" class="h-7 w-7 text-danger" />
                    </div>
                    <h1 class="mt-5 font-display text-2xl text-navy-900">Batalkan Pendaftaran?</h1>
                    <p class="mt-3 text-ink-soft text-pretty">
                        Anda akan membatalkan pendaftaran untuk
                        <strong class="text-navy-900">{{ $registration->event->title }}</strong>
                        pada {{ $registration->event->dateLabel() }}.
                    </p>
                </div>

                <div class="mt-6 rounded-xl bg-mist p-4 text-sm">
                    <div class="flex justify-between gap-3">
                        <span class="text-ink-muted">Rujukan</span>
                        <span class="font-mono text-navy-900">{{ $registration->reference_no }}</span>
                    </div>
                    <div class="mt-2 flex justify-between gap-3">
                        <span class="text-ink-muted">Nama</span>
                        <span class="text-navy-900">{{ $registration->name }}</span>
                    </div>
                    <div class="mt-2 flex justify-between gap-3">
                        <span class="text-ink-muted">Tempat</span>
                        <span class="text-navy-900">{{ $registration->seats() }}</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('tiket.cancel.submit', $registration->public_token) }}"
                      class="mt-6 space-y-5">
                    @csrf
                    @method('DELETE')

                    <x-ui.field label="Sebab pembatalan" for="reason" optional
                                hint="Membantu kami memahami dan memperbaiki program.">
                        <x-ui.textarea id="reason" name="reason" rows="3"
                                       placeholder="Contoh: Ada urusan lain pada tarikh tersebut." />
                    </x-ui.field>

                    <div class="grid gap-2.5 sm:grid-cols-2">
                        <x-ui.button :href="route('tiket.show', $registration->public_token)"
                                     variant="outline" block>
                            Tidak, Kekalkan
                        </x-ui.button>
                        <x-ui.button type="submit" variant="danger" block icon="trash">
                            Ya, Batalkan
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>
    </section>
</x-layouts.public>
