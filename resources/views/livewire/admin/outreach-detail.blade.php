<div class="grid gap-6 xl:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)]">
    {{-- ══ Lajur utama ═══════════════════════════════════════ --}}
    <div class="space-y-6">
        {{-- Kemajuan --}}
        <x-ui.card>
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0">
                    <h2 class="font-semibold text-ink">Kemajuan Sasaran</h2>
                    <p class="mt-1 text-sm text-ink-soft text-pretty">{{ $target->stage->description() }}</p>
                </div>
                <x-ui.badge :color="$target->stage->color()" dot>{{ $target->stage->label() }}</x-ui.badge>
            </div>

            <div class="mt-4">
                <x-ui.progress :value="$target->stage->progress()" :showValue="false"
                               :tone="$target->stage->isWon() ? 'success' : ($target->stage->isOpen() ? 'brand' : 'navy')" />
            </div>

            <form wire:submit="changeStage" class="mt-5 grid gap-3 sm:grid-cols-[minmax(0,1fr)_minmax(0,1.3fr)_auto]">
                <div>
                    <label for="d-stage" class="mb-1.5 block text-sm font-medium text-ink">Peringkat</label>
                    <x-ui.select id="d-stage" wire:model="stage" :error="$errors->has('stage')">
                        @foreach ($stages as $option)
                            <option value="{{ $option->value }}">{{ $option->label() }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <div>
                    <label for="d-note" class="mb-1.5 block text-sm font-medium text-ink">
                        Nota <span class="font-normal text-ink-muted">(pilihan)</span>
                    </label>
                    <x-ui.input id="d-note" wire:model="stageNote" :error="$errors->has('stageNote')"
                                placeholder="Contoh: Nazir setuju, tunggu kelulusan JK." />
                    @error('stageNote')
                        <p class="mt-1.5 text-sm text-alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-end">
                    <x-ui.button type="submit" variant="outline" block
                                 wire:loading.attr="disabled" wire:target="changeStage">
                        Kemas Kini
                    </x-ui.button>
                </div>
            </form>

            @if ($target->application)
                <div class="mt-5 rounded-xl border border-clay-600/25 bg-clay-50 p-4">
                    <div class="flex items-start gap-3">
                        <x-ui.icon name="check-circle" class="mt-0.5 h-5 w-5 shrink-0 text-clay-700" />
                        <div class="min-w-0 flex-1">
                            <p class="font-medium text-[#0A5537]">Sasaran ini telah menjadi permohonan</p>
                            <p class="mt-1 font-mono text-xs text-[#0A5537]/85">
                                {{ $target->application->reference_no }}
                            </p>
                            <x-ui.button :href="route('admin.permohonan.show', $target->application)"
                                         variant="success" size="sm" class="mt-3">
                                Buka Permohonan
                            </x-ui.button>
                        </div>
                    </div>
                </div>
            @elseif ($target->stage->canConvert())
                @if (! $converting)
                    <div class="mt-5 rounded-xl border border-clay-200 bg-clay-50/60 p-4">
                        <p class="text-sm font-medium text-ink">Pihak lokasi sudah bersetuju?</p>
                        <p class="mt-1 text-sm text-ink-soft text-pretty">
                            Tukar sasaran ini kepada permohonan rasmi. Selepas itu ia mengalir melalui
                            aliran permohonan biasa sehingga program dijana.
                        </p>
                        <x-ui.button wire:click="startConvert" variant="primary" size="sm" class="mt-3"
                                     icon="arrow-right">
                            Tukar kepada Permohonan
                        </x-ui.button>
                    </div>
                @else
                    <form wire:submit="convert" class="mt-5 space-y-4 rounded-xl border border-clay-200 bg-clay-50/60 p-4">
                        <p class="text-sm font-semibold text-ink">Butiran Permohonan</p>

                        <x-ui.field label="Jenis program" for="c-cat" required
                                    :error="$errors->first('event_category_id')">
                            <x-ui.select id="c-cat" wire:model="event_category_id"
                                         :error="$errors->has('event_category_id')">
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </x-ui.select>
                        </x-ui.field>

                        <x-ui.field label="Topik / keperluan komuniti" for="c-topic" required
                                    :error="$errors->first('topic')">
                            <x-ui.textarea id="c-topic" wire:model="topic" rows="3"
                                           :error="$errors->has('topic')" />
                        </x-ui.field>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-ui.field label="Cadangan tarikh pertama" for="c-d1" required
                                        :error="$errors->first('preferred_date_1')">
                                <x-ui.input id="c-d1" type="date" wire:model="preferred_date_1"
                                            :error="$errors->has('preferred_date_1')"
                                            min="{{ now()->addDay()->toDateString() }}" />
                            </x-ui.field>

                            <x-ui.field label="Cadangan tarikh kedua" for="c-d2" optional
                                        :error="$errors->first('preferred_date_2')">
                                <x-ui.input id="c-d2" type="date" wire:model="preferred_date_2"
                                            :error="$errors->has('preferred_date_2')"
                                            min="{{ now()->addDay()->toDateString() }}" />
                            </x-ui.field>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-ui.field label="Anggaran peserta" for="c-est" required
                                        :error="$errors->first('estimated_attendees')">
                                <x-ui.select id="c-est" wire:model="estimated_attendees"
                                             :error="$errors->has('estimated_attendees')">
                                    @foreach ($estimates as $option)
                                        <option value="{{ $option->value }}">{{ $option->label() }}</option>
                                    @endforeach
                                </x-ui.select>
                            </x-ui.field>

                            <x-ui.field label="Sasaran peserta" for="c-aud" required
                                        :error="$errors->first('target_audience')">
                                <x-ui.select id="c-aud" wire:model="target_audience"
                                             :error="$errors->has('target_audience')">
                                    @foreach ($audiences as $option)
                                        <option value="{{ $option->value }}">{{ $option->label() }}</option>
                                    @endforeach
                                </x-ui.select>
                            </x-ui.field>
                        </div>

                        <div class="flex flex-wrap gap-2.5">
                            <x-ui.button type="submit" variant="primary"
                                         wire:loading.attr="disabled" wire:target="convert">
                                <span wire:loading.remove wire:target="convert">Jana Permohonan</span>
                                <span wire:loading wire:target="convert">Menjana…</span>
                            </x-ui.button>
                            <x-ui.button type="button" wire:click="$set('converting', false)" variant="ghost">
                                Batal
                            </x-ui.button>
                        </div>
                    </form>
                @endif
            @endif
        </x-ui.card>

        {{-- Rekod aktiviti --}}
        <x-ui.card>
            <h2 class="font-semibold text-ink">Rekod Aktiviti</h2>
            <p class="mt-1 text-sm text-ink-soft text-pretty">
                Catat setiap panggilan dan lawatan supaya staf lain tahu apa yang sudah dibuat.
            </p>

            <form wire:submit="logActivity" class="mt-4 space-y-3">
                <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_minmax(0,1.2fr)]">
                    <x-ui.field label="Jenis" for="a-type" :error="$errors->first('activityType')">
                        <x-ui.select id="a-type" wire:model="activityType" :error="$errors->has('activityType')">
                            @foreach ($activityTypes as $option)
                                <option value="{{ $option->value }}">{{ $option->label() }}</option>
                            @endforeach
                        </x-ui.select>
                    </x-ui.field>

                    <x-ui.field label="Hasil" for="a-outcome" optional
                                hint="Contoh: Minta hubungi semula minggu depan"
                                :error="$errors->first('activityOutcome')">
                        <x-ui.input id="a-outcome" wire:model="activityOutcome"
                                    :error="$errors->has('activityOutcome')" />
                    </x-ui.field>
                </div>

                <x-ui.field label="Catatan" for="a-body" required :error="$errors->first('activityBody')">
                    <x-ui.textarea id="a-body" wire:model="activityBody" rows="3"
                                   :error="$errors->has('activityBody')"
                                   placeholder="Apa yang berlaku semasa hubungan ini?" />
                </x-ui.field>

                <x-ui.button type="submit" variant="outline" size="sm" icon="plus"
                             wire:loading.attr="disabled" wire:target="logActivity">
                    Simpan Aktiviti
                </x-ui.button>
            </form>

            {{-- Garis masa --}}
            <ol class="mt-6 border-t border-hairline pt-5">
                @forelse ($target->activities as $activity)
                    <li class="relative flex gap-4 border-l-2 border-clay-200 pb-5 pl-6 last:border-transparent last:pb-0">
                        <span class="absolute -left-[7px] top-1 h-3 w-3 rounded-full
                                     {{ $activity->isStageChange() ? 'bg-clay-400' : 'bg-hairline' }} ring-4 ring-surface"></span>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <x-ui.icon :name="$activity->type->icon()" class="h-4 w-4 text-ink-muted" />
                                <span class="text-sm font-medium text-ink">
                                    @if ($activity->isStageChange())
                                        {{ $activity->from_stage?->label() ?? 'Baharu' }}
                                        &rarr; {{ $activity->to_stage?->label() }}
                                    @else
                                        {{ $activity->type->label() }}
                                    @endif
                                </span>
                                @if ($activity->outcome)
                                    <x-ui.badge color="grey">{{ $activity->outcome }}</x-ui.badge>
                                @endif
                            </div>

                            @if ($activity->body)
                                <p class="mt-1.5 text-sm text-ink-soft text-pretty">{{ $activity->body }}</p>
                            @endif

                            <p class="mt-1.5 text-xs text-ink-muted">
                                {{ $activity->occurred_at->translatedFormat('j M Y, g:ia') }}
                                @if ($activity->user) &middot; {{ $activity->user->name }} @endif
                            </p>
                        </div>
                    </li>
                @empty
                    <li class="text-sm text-ink-muted">Belum ada aktiviti direkodkan.</li>
                @endforelse
            </ol>
        </x-ui.card>
    </div>

    {{-- ══ Sisi ══════════════════════════════════════════════ --}}
    <aside class="space-y-5 xl:sticky xl:top-24 xl:self-start">
        {{-- Kontak --}}
        <x-ui.card>
            <div class="flex items-center justify-between gap-3">
                <h3 class="font-semibold text-ink">Kontak Lokasi</h3>
                <x-ui.button wire:click="$toggle('editingContact')" variant="ghost" size="sm" icon="edit">
                    {{ $editingContact ? 'Tutup' : 'Ubah' }}
                </x-ui.button>
            </div>

            @if ($editingContact)
                <form wire:submit="saveContact" class="mt-4 space-y-3">
                    <x-ui.field label="Nama" for="k-name" :error="$errors->first('contact_name')">
                        <x-ui.input id="k-name" wire:model="contact_name" :error="$errors->has('contact_name')" />
                    </x-ui.field>
                    <x-ui.field label="Jawatan" for="k-role" optional :error="$errors->first('contact_role')">
                        <x-ui.input id="k-role" wire:model="contact_role" :error="$errors->has('contact_role')"
                                    placeholder="Contoh: Nazir Masjid" />
                    </x-ui.field>
                    <x-ui.field label="Telefon" for="k-phone" :error="$errors->first('contact_phone')">
                        <x-ui.input id="k-phone" type="tel" wire:model="contact_phone" icon="whatsapp"
                                    :error="$errors->has('contact_phone')" />
                    </x-ui.field>
                    <x-ui.field label="E-mel" for="k-email" optional :error="$errors->first('contact_email')">
                        <x-ui.input id="k-email" type="email" wire:model="contact_email"
                                    :error="$errors->has('contact_email')" />
                    </x-ui.field>
                    <x-ui.field label="Nota kontak" for="k-note" optional :error="$errors->first('contact_note')">
                        <x-ui.textarea id="k-note" wire:model="contact_note" rows="2"
                                       :error="$errors->has('contact_note')" />
                    </x-ui.field>
                    <x-ui.button type="submit" variant="primary" size="sm">Simpan Kontak</x-ui.button>
                </form>
            @elseif ($target->hasContact())
                <dl class="mt-4 space-y-3 text-sm">
                    @if ($target->contact_name)
                        <div>
                            <dt class="text-xs text-ink-muted">Nama</dt>
                            <dd class="mt-0.5 text-ink">
                                {{ $target->contact_name }}
                                @if ($target->contact_role)
                                    <span class="block text-xs text-ink-muted">{{ $target->contact_role }}</span>
                                @endif
                            </dd>
                        </div>
                    @endif
                    @if ($target->contact_phone)
                        <div>
                            <dt class="text-xs text-ink-muted">Telefon</dt>
                            <dd class="mt-0.5">
                                <a href="tel:{{ $target->contact_phone }}"
                                   class="font-medium text-clay-600 hover:underline">{{ $target->contact_phone }}</a>
                            </dd>
                        </div>
                    @endif
                    @if ($target->contact_email)
                        <div>
                            <dt class="text-xs text-ink-muted">E-mel</dt>
                            <dd class="mt-0.5 break-all text-ink">{{ $target->contact_email }}</dd>
                        </div>
                    @endif
                    @if ($target->contact_note)
                        <div>
                            <dt class="text-xs text-ink-muted">Nota</dt>
                            <dd class="mt-0.5 text-ink-soft text-pretty">{{ $target->contact_note }}</dd>
                        </div>
                    @endif
                </dl>

                @if ($target->whatsappUrl())
                    <x-ui.button :href="$target->whatsappUrl('Assalamualaikum. Saya dari BeDaie. Kami ingin berbincang tentang program jelajah di '.$target->name.'.')"
                                 target="_blank" rel="noopener" variant="whatsapp" block class="mt-4" icon="whatsapp">
                        Hubungi di WhatsApp
                    </x-ui.button>
                @endif
            @else
                <div class="mt-4 rounded-xl bg-mist p-4">
                    <p class="text-sm text-[#7A4E06] text-pretty">
                        Kontak belum dijumpai. Ini kerja pertama untuk sasaran ini.
                    </p>
                    <x-ui.button wire:click="$set('editingContact', true)" variant="outline" size="sm" class="mt-3">
                        Rekod Kontak
                    </x-ui.button>
                </div>
            @endif
        </x-ui.card>

        {{-- Sumber --}}
        <x-ui.card>
            <h3 class="font-semibold text-ink">Sumber Sasaran</h3>
            <div class="mt-3 flex items-start gap-3">
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-clay-50">
                    <x-ui.icon :name="$target->source->icon()" class="h-4 w-4 text-clay-600" />
                </span>
                <div class="min-w-0">
                    <p class="text-sm font-medium text-ink">{{ $target->sourceLabel() }}</p>
                    <p class="mt-0.5 text-xs text-ink-soft text-pretty">{{ $target->source->description() }}</p>
                </div>
            </div>

            @if ($target->referrer_phone)
                <p class="mt-3 text-sm text-ink-soft">
                    Perujuk: <a href="tel:{{ $target->referrer_phone }}"
                                class="font-medium text-clay-600 hover:underline">{{ $target->referrer_phone }}</a>
                </p>
            @endif

            @if ($target->partner)
                <x-ui.button :href="route('admin.rakan')" variant="ghost" size="sm" class="mt-3">
                    Lihat rakan
                </x-ui.button>
            @endif
        </x-ui.card>

        {{-- Tugasan --}}
        <x-ui.card>
            <h3 class="font-semibold text-ink">Tugasan & Susulan</h3>

            <form wire:submit="saveAssignment" class="mt-4 space-y-3">
                <x-ui.field label="Staf bertanggungjawab" for="t-assignee" :error="$errors->first('assigned_to')">
                    <x-ui.select id="t-assignee" wire:model="assigned_to" :error="$errors->has('assigned_to')">
                        <option value="">Belum ditetapkan</option>
                        @foreach ($staff as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Keutamaan" for="t-priority" :error="$errors->first('priority')">
                    <x-ui.select id="t-priority" wire:model="priority" :error="$errors->has('priority')">
                        @foreach ($priorities as $option)
                            <option value="{{ $option->value }}">{{ $option->label() }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Tindakan seterusnya" for="t-next" optional
                            :error="$errors->first('next_action_at')">
                    <x-ui.input id="t-next" type="date" wire:model="next_action_at"
                                :error="$errors->has('next_action_at')" />
                </x-ui.field>

                <x-ui.field label="Nota tindakan" for="t-nextnote" optional
                            :error="$errors->first('next_action_note')">
                    <x-ui.input id="t-nextnote" wire:model="next_action_note"
                                :error="$errors->has('next_action_note')"
                                placeholder="Contoh: Hubungi selepas solat Jumaat" />
                </x-ui.field>

                <x-ui.button type="submit" variant="outline" size="sm" block>Simpan Tugasan</x-ui.button>
            </form>

            @if ($target->isOverdue())
                <p class="mt-3 rounded-lg bg-alert-soft px-3 py-2 text-sm text-[#8C1A1E]">
                    Tindakan susulan tertunggak sejak
                    {{ $target->next_action_at->translatedFormat('j M Y') }}.
                </p>
            @endif
        </x-ui.card>

        {{-- Butiran lokasi --}}
        <x-ui.card>
            <h3 class="font-semibold text-ink">Butiran Lokasi</h3>
            <dl class="mt-3 space-y-3 text-sm">
                @foreach ([
                    'Jenis' => $target->type->label(),
                    'Kawasan' => $target->locationLabel(),
                    'Alamat' => $target->address,
                    'Rujukan' => $target->reference_no,
                    'Direkod oleh' => $target->creator?->name,
                    'Ditambah' => $target->created_at->translatedFormat('j M Y'),
                ] as $label => $value)
                    @if ($value)
                        <div class="flex justify-between gap-3">
                            <dt class="text-ink-muted">{{ $label }}</dt>
                            <dd class="text-right text-ink text-pretty">{{ $value }}</dd>
                        </div>
                    @endif
                @endforeach
            </dl>

            @if ($target->google_maps_url)
                <x-ui.button :href="$target->google_maps_url" target="_blank" rel="noopener"
                             variant="outline" size="sm" block class="mt-4" icon="pin">
                    Buka Google Maps
                </x-ui.button>
            @endif

            @if ($target->notes)
                <div class="mt-4 rounded-xl bg-mist p-3.5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Nota</p>
                    <p class="mt-1.5 text-sm text-ink-soft text-pretty">{{ $target->notes }}</p>
                </div>
            @endif
        </x-ui.card>
    </aside>
</div>
