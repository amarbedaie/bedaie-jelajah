<div class="space-y-5">
    {{-- ── Tukar status ───────────────────────────────────── --}}
    <x-ui.card>
        <h2 class="font-semibold text-ink">Tindakan Permohonan</h2>

        <form wire:submit="save" class="mt-4 space-y-4">
            <x-ui.field label="Status" for="wf-status" required :error="$errors->first('status')">
                <x-ui.select id="wf-status" wire:model.live="status" :error="$errors->has('status')">
                    @foreach ($statuses as $option)
                        <option value="{{ $option->value }}">{{ $option->label() }}</option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>

            @if ($this->confirmsProgram())
                @if ($application->isConverted())
                    <x-ui.alert variant="success" icon="check-circle" title="Program telah dijana">
                        {{ $application->event?->title }} —
                        <a href="{{ route('admin.program.show', $application->event) }}"
                           class="font-medium underline">buka program</a>.
                    </x-ui.alert>
                @else
                    <div class="rounded-xl border border-clay-200 bg-clay-50/60 p-4">
                        <p class="text-sm font-semibold text-ink">Butiran Program</p>
                        <p class="mt-1 text-[0.8125rem] text-ink-soft text-pretty">
                            Menyimpan status ini akan menjana halaman program, link pendek, QR,
                            poster dan dashboard Penggerak secara automatik.
                        </p>

                        <div class="mt-4 space-y-4">
                            <x-ui.field label="Tajuk program" for="wf-title" required :error="$errors->first('title')">
                                <x-ui.input id="wf-title" wire:model="title" :error="$errors->has('title')" />
                            </x-ui.field>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <x-ui.field label="Tarikh & masa" for="wf-starts" required
                                            :error="$errors->first('startsAt')">
                                    <x-ui.input id="wf-starts" type="datetime-local" wire:model="startsAt"
                                                :error="$errors->has('startsAt')" />
                                </x-ui.field>

                                <x-ui.field label="Penceramah" for="wf-speaker" optional
                                            :error="$errors->first('speakerId')">
                                    <x-ui.select id="wf-speaker" wire:model="speakerId"
                                                 :error="$errors->has('speakerId')">
                                        <option value="">Tetapkan kemudian</option>
                                        @foreach ($speakers as $speaker)
                                            <option value="{{ $speaker->id }}">{{ $speaker->name }}</option>
                                        @endforeach
                                    </x-ui.select>
                                </x-ui.field>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-3">
                                <x-ui.field label="Kapasiti" for="wf-capacity" required
                                            :error="$errors->first('capacity')">
                                    <x-ui.input id="wf-capacity" type="number" min="1" wire:model="capacity"
                                                :error="$errors->has('capacity')" />
                                </x-ui.field>

                                <x-ui.field label="Mod harga" for="wf-pricing" required
                                            :error="$errors->first('pricingMode')">
                                    <x-ui.select id="wf-pricing" wire:model.live="pricingMode"
                                                 :error="$errors->has('pricingMode')">
                                        @foreach ($pricingModes as $mode)
                                            <option value="{{ $mode->value }}">{{ $mode->label() }}</option>
                                        @endforeach
                                    </x-ui.select>
                                </x-ui.field>

                                @if ($pricingMode === 'berbayar')
                                    <x-ui.field label="Harga (RM)" for="wf-price" required
                                                :error="$errors->first('price')">
                                        <x-ui.input id="wf-price" type="number" step="0.01" min="0"
                                                    wire:model="price" :error="$errors->has('price')" />
                                    </x-ui.field>
                                @else
                                    <x-ui.field label="Jam pembelajaran" for="wf-hours" optional
                                                :error="$errors->first('learningHours')">
                                        <x-ui.input id="wf-hours" type="number" step="0.5" min="0"
                                                    wire:model="learningHours" :error="$errors->has('learningHours')" />
                                    </x-ui.field>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            <x-ui.field label="Nota untuk pemohon" for="wf-public" optional
                        hint="Dipaparkan kepada Penggerak pada timeline permohonan."
                        :error="$errors->first('publicNote')">
                <x-ui.textarea id="wf-public" wire:model="publicNote" rows="2"
                               :error="$errors->has('publicNote')" />
            </x-ui.field>

            <x-ui.field label="Nota dalaman" for="wf-internal" optional
                        hint="Untuk pasukan BeDaie sahaja — tidak dilihat oleh pemohon."
                        :error="$errors->first('internalNote')">
                <x-ui.textarea id="wf-internal" wire:model="internalNote" rows="2"
                               :error="$errors->has('internalNote')" />
            </x-ui.field>

            <x-ui.button type="submit" variant="primary" block wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">
                    {{ $this->confirmsProgram() && ! $application->isConverted()
                        ? 'Sahkan Program & Jana EventSpace'
                        : 'Kemas Kini Status' }}
                </span>
                <span wire:loading wire:target="save">Memproses…</span>
            </x-ui.button>
        </form>
    </x-ui.card>

    {{-- ── Rekod komunikasi ───────────────────────────────── --}}
    <x-ui.card>
        <h2 class="font-semibold text-ink">Rekod Komunikasi</h2>
        <p class="mt-1 text-sm text-ink-soft text-pretty">
            Catat perbualan dengan pemohon supaya pasukan lain tahu konteksnya.
        </p>

        <form wire:submit="addNote" class="mt-4 space-y-3">
            <x-ui.field label="Saluran" for="wf-channel">
                <x-ui.select id="wf-channel" wire:model="noteChannel">
                    <option value="whatsapp">WhatsApp</option>
                    <option value="telefon">Telefon</option>
                    <option value="emel">E-mel</option>
                    <option value="mesyuarat">Mesyuarat</option>
                    <option value="lain">Lain-lain</option>
                </x-ui.select>
            </x-ui.field>

            <x-ui.field label="Nota" for="wf-note" :error="$errors->first('noteBody')">
                <x-ui.textarea id="wf-note" wire:model="noteBody" rows="3"
                               :error="$errors->has('noteBody')"
                               placeholder="Contoh: Dihubungi pukul 3 petang. Pihak masjid setuju tarikh 14 Mac." />
            </x-ui.field>

            <x-ui.button type="submit" variant="outline" size="sm" icon="plus"
                         wire:loading.attr="disabled" wire:target="addNote">
                Simpan Nota
            </x-ui.button>
        </form>
    </x-ui.card>
</div>
