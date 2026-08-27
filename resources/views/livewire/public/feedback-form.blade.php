<div class="mx-auto max-w-xl">
    @if ($submitted)
        <x-ui.card class="text-center sm:p-8">
            <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-clay-50">
                <x-ui.icon name="check-circle" class="h-7 w-7 text-clay-700" />
            </div>
            <h2 class="mt-5 font-display text-2xl text-ink">Terima Kasih</h2>
            <p class="mt-3 text-ink-soft text-pretty">
                Maklum balas anda telah kami terima dan akan membantu kami memperbaiki
                program yang akan datang.
            </p>

            @if ($registration->certificate)
                <div class="mt-7 rounded-xl bg-clay-50 p-5">
                    <x-ui.icon name="certificate" class="mx-auto h-6 w-6 text-clay-600" />
                    <p class="mt-2.5 font-medium text-ink">Sijil anda telah sedia</p>
                    <p class="mt-1 font-mono text-xs text-ink-muted">
                        {{ $registration->certificate->certificate_number }}
                    </p>
                    <x-ui.button :href="$registration->certificate->downloadUrl()" target="_blank"
                                 variant="primary" size="sm" class="mt-4" icon="download">
                        Muat Turun Sijil
                    </x-ui.button>
                </div>
            @endif

            <div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
                <x-ui.button :href="route('tiket.show', $registration->public_token)" variant="outline">
                    Kembali ke Tiket
                </x-ui.button>
                <x-ui.button :href="route('program.index')" variant="ghost">
                    Lihat Program Seterusnya
                </x-ui.button>
            </div>
        </x-ui.card>
    @else
        <x-ui.card class="sm:p-8">
            <div class="space-y-6">
                <div>
                    <h2 class="text-xl font-semibold text-ink">Bagaimana pengalaman anda?</h2>
                    <p class="mt-1.5 text-sm text-ink-soft text-pretty">
                        Empat soalan ringkas sahaja — kurang dari seminit.
                    </p>
                </div>

                {{-- Penilaian bintang --}}
                <fieldset>
                    <legend class="mb-3 block text-sm font-medium text-ink">
                        Penilaian keseluruhan <span class="text-alert" aria-hidden="true">*</span>
                    </legend>
                    <div class="flex gap-2" role="radiogroup" aria-label="Penilaian 1 hingga 5">
                        @for ($i = 1; $i <= 5; $i++)
                            <button type="button" wire:click="setRating({{ $i }})"
                                    role="radio" aria-checked="{{ $rating === $i ? 'true' : 'false' }}"
                                    aria-label="{{ $i }} daripada 5"
                                    class="tap-target grid flex-1 place-items-center rounded-xl border transition
                                           {{ $rating >= $i
                                              ? 'border-clay-400 bg-mist'
                                              : 'border-hairline bg-surface hover:border-clay-300' }}">
                                <x-ui.icon name="star"
                                    :fill="$rating >= $i ? 'currentColor' : 'none'"
                                    class="h-6 w-6 {{ $rating >= $i ? 'text-clay-700' : 'text-hairline' }}" />
                            </button>
                        @endfor
                    </div>
                    @error('rating')
                        <p class="mt-2 text-sm text-alert">{{ $message }}</p>
                    @enderror
                </fieldset>

                <x-ui.field label="Apakah perkara paling bermanfaat?" for="fb-benefit" optional
                            :error="$errors->first('most_beneficial')">
                    <x-ui.textarea id="fb-benefit" wire:model.blur="most_beneficial" rows="3"
                                   :error="$errors->has('most_beneficial')"
                                   placeholder="Contoh: Penjelasan tentang cara qadha solat yang selama ini saya keliru." />
                </x-ui.field>

                <x-ui.field label="Apakah topik yang anda mahu selepas ini?" for="fb-next" optional
                            :error="$errors->first('next_topic')">
                    <x-ui.input id="fb-next" wire:model.blur="next_topic"
                                :error="$errors->has('next_topic')"
                                placeholder="Contoh: Fiqh muamalat harian" />
                </x-ui.field>

                <x-ui.choice type="checkbox" name="wants_advanced" value="1"
                             wire:model="wants_advanced" :checked="$wants_advanced"
                             label="Saya berminat menyertai kelas lanjutan"
                             hint="Kami akan maklumkan jika ada kelas susulan berhampiran anda." />

                <x-ui.button type="button" wire:click="submit" variant="primary" size="lg" block
                             wire:loading.attr="disabled" wire:target="submit">
                    <span wire:loading.remove wire:target="submit">Hantar Maklum Balas</span>
                    <span wire:loading wire:target="submit">Menghantar…</span>
                </x-ui.button>
            </div>
        </x-ui.card>
    @endif
</div>
