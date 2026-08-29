@php
    $needsPartner = $form_source === 'rakan';
    $needsPenggerak = $form_source === 'penggerak';
    $needsRujukan = $form_source === 'rujukan';
@endphp

<div>
    {{-- ══ Ringkasan corong ══════════════════════════════════ --}}
    <div class="flex flex-wrap items-end justify-between gap-4">
        <dl class="flex flex-wrap gap-x-8 gap-y-3">
            <div>
                <dd class="font-display text-3xl text-ink">{{ number_format($total) }}</dd>
                <dt class="text-sm text-ink-soft">Sasaran dipaparkan</dt>
            </div>
            <div>
                <dd class="font-display text-3xl {{ $overdueCount > 0 ? 'text-alert' : 'text-ink' }}">
                    {{ number_format($overdueCount) }}
                </dd>
                <dt class="text-sm text-ink-soft">Tindakan tertunggak</dt>
            </div>
            <div>
                <dd class="font-display text-3xl text-clay-700">{{ number_format($wonCount) }}</dd>
                <dt class="text-sm text-ink-soft">Berjaya jadi jelajah</dt>
            </div>
        </dl>

        <div class="flex flex-wrap gap-2">
            <div class="inline-flex rounded-full bg-mist p-1" role="group" aria-label="Tukar paparan">
                @foreach (['papan' => 'Papan', 'senarai' => 'Senarai', 'rakan' => 'Rakan'] as $key => $label)
                    <button type="button" wire:click="$set('view', '{{ $key }}')"
                            aria-pressed="{{ $view === $key ? 'true' : 'false' }}"
                            class="rounded-full px-4 py-1.5 text-sm font-medium transition
                                   {{ $view === $key ? 'bg-surface text-ink shadow-soft' : 'text-ink-soft hover:text-ink' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            @unless ($showForm)
                <x-ui.button wire:click="create" variant="primary" icon="plus">Tambah Sasaran</x-ui.button>
            @endunless
        </div>
    </div>

    {{-- ══ Borang sasaran baharu ═════════════════════════════ --}}
    @if ($showForm)
        <x-ui.card class="mt-6">
            <h2 class="font-semibold text-ink">Sasaran Baharu</h2>
            <p class="mt-1 text-sm text-ink-soft text-pretty">
                Rekod lokasi yang anda mahu dekati. Jika kontak sudah ada, sasaran terus
                masuk ke peringkat "Kontak Dijumpai".
            </p>

            <form wire:submit="save" class="mt-5 space-y-5">
                <div class="grid gap-4 sm:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
                    <x-ui.field label="Nama lokasi" for="o-name" required :error="$errors->first('name')">
                        <x-ui.input id="o-name" wire:model="name" :error="$errors->has('name')"
                                    placeholder="Contoh: Masjid Jamek Kampung Melayu" />
                    </x-ui.field>

                    <x-ui.field label="Jenis" for="o-type" required :error="$errors->first('type')">
                        <x-ui.select id="o-type" wire:model="type" :error="$errors->has('type')">
                            @foreach ($types as $option)
                                <option value="{{ $option->value }}">{{ $option->label() }}</option>
                            @endforeach
                        </x-ui.select>
                    </x-ui.field>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.field label="Negeri" for="o-state" required :error="$errors->first('state_id')">
                        <x-ui.select id="o-state" wire:model.live="state_id" :error="$errors->has('state_id')">
                            <option value="">Pilih negeri</option>
                            @foreach ($states as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </x-ui.select>
                    </x-ui.field>

                    <x-ui.field label="Daerah" for="o-district" optional :error="$errors->first('district_id')">
                        <x-ui.select id="o-district" wire:model="district_id"
                                     :error="$errors->has('district_id')" :disabled="$districts->isEmpty()">
                            <option value="">{{ $districts->isEmpty() ? 'Pilih negeri dahulu' : 'Pilih daerah' }}</option>
                            @foreach ($districts as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </x-ui.select>
                    </x-ui.field>
                </div>

                {{-- ── Sumber sasaran ─────────────────────────── --}}
                <fieldset class="rounded-xl border border-hairline bg-mist/40 p-4">
                    <legend class="px-1 text-sm font-medium text-ink">
                        Dari mana sasaran ini datang? <span class="text-alert" aria-hidden="true">*</span>
                    </legend>
                    <p class="mt-1 text-xs text-ink-soft text-pretty">
                        Ini yang membolehkan kita tahu rakan mana benar-benar membawa hasil.
                    </p>

                    <div class="mt-3 grid gap-2.5 sm:grid-cols-2">
                        @foreach ($sources as $option)
                            <x-ui.choice name="form_source" :value="$option->value"
                                         :label="$option->label()"
                                         :hint="$option->description()"
                                         wire:model.live="form_source"
                                         :checked="$form_source === $option->value" />
                        @endforeach
                    </div>

                    @if ($needsPartner)
                        <div class="mt-4">
                            <x-ui.field label="Rakan yang memperkenalkan" for="o-partner" required
                                        :error="$errors->first('partner_id')">
                                <x-ui.select id="o-partner" wire:model="partner_id"
                                             :error="$errors->has('partner_id')">
                                    <option value="">Pilih rakan</option>
                                    @foreach ($partners as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </x-ui.select>
                            </x-ui.field>
                            @if ($partners->isEmpty())
                                <p class="mt-2 text-sm text-clay-700">
                                    Belum ada rakan aktif.
                                    <a href="{{ route('admin.rakan') }}" class="font-medium underline">Tambah rakan dahulu</a>.
                                </p>
                            @endif
                        </div>
                    @endif

                    @if ($needsPenggerak)
                        <div class="mt-4">
                            <x-ui.field label="Penggerak yang mencadangkan" for="o-penggerak" required
                                        :error="$errors->first('referrer_user_id')">
                                <x-ui.select id="o-penggerak" wire:model="referrer_user_id"
                                             :error="$errors->has('referrer_user_id')">
                                    <option value="">Pilih penggerak</option>
                                    @foreach ($penggerak as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </x-ui.select>
                            </x-ui.field>
                        </div>
                    @endif

                    @if ($needsRujukan)
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <x-ui.field label="Nama perujuk" for="o-refname" required
                                        :error="$errors->first('referrer_name')">
                                <x-ui.input id="o-refname" wire:model="referrer_name"
                                            :error="$errors->has('referrer_name')" />
                            </x-ui.field>
                            <x-ui.field label="Telefon perujuk" for="o-refphone" optional
                                        :error="$errors->first('referrer_phone')">
                                <x-ui.input id="o-refphone" type="tel" wire:model="referrer_phone"
                                            :error="$errors->has('referrer_phone')" />
                            </x-ui.field>
                        </div>
                    @endif
                </fieldset>

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.field label="Nama kontak" for="o-contact" optional
                                hint="Kosongkan jika kontak belum dijumpai."
                                :error="$errors->first('contact_name')">
                        <x-ui.input id="o-contact" wire:model="contact_name"
                                    :error="$errors->has('contact_name')" />
                    </x-ui.field>

                    <x-ui.field label="Telefon kontak" for="o-phone" optional
                                :error="$errors->first('contact_phone')">
                        <x-ui.input id="o-phone" type="tel" wire:model="contact_phone"
                                    icon="whatsapp" :error="$errors->has('contact_phone')" />
                    </x-ui.field>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <x-ui.field label="Staf bertanggungjawab" for="o-assignee" optional
                                :error="$errors->first('assigned_to')">
                        <x-ui.select id="o-assignee" wire:model="assigned_to"
                                     :error="$errors->has('assigned_to')">
                            <option value="">Belum ditetapkan</option>
                            @foreach ($staff as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </x-ui.select>
                    </x-ui.field>

                    <x-ui.field label="Keutamaan" for="o-priority" required
                                :error="$errors->first('form_priority')">
                        <x-ui.select id="o-priority" wire:model="form_priority"
                                     :error="$errors->has('form_priority')">
                            @foreach ($priorities as $option)
                                <option value="{{ $option->value }}">{{ $option->label() }}</option>
                            @endforeach
                        </x-ui.select>
                    </x-ui.field>

                    <x-ui.field label="Tindakan seterusnya" for="o-next" optional
                                hint="Bila anda akan hubungi?" :error="$errors->first('next_action_at')">
                        <x-ui.input id="o-next" type="date" wire:model="next_action_at"
                                    :error="$errors->has('next_action_at')" />
                    </x-ui.field>
                </div>

                <x-ui.field label="Nota" for="o-notes" optional :error="$errors->first('notes')">
                    <x-ui.textarea id="o-notes" wire:model="notes" rows="2"
                                   :error="$errors->has('notes')"
                                   placeholder="Contoh: Nazir masjid kenal Ustaz Faiz. Cuba hubungi selepas Jumaat." />
                </x-ui.field>

                <div class="flex flex-wrap gap-2.5">
                    <x-ui.button type="submit" variant="primary"
                                 wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">Simpan Sasaran</span>
                        <span wire:loading wire:target="save">Menyimpan…</span>
                    </x-ui.button>
                    <x-ui.button type="button" wire:click="cancel" variant="ghost">Batal</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    @endif

    {{-- ══ Penapis ═══════════════════════════════════════════ --}}
    <div class="mt-6 rounded-card border border-hairline bg-surface p-4">
        <div class="grid gap-3 lg:grid-cols-[minmax(0,1.4fr)_repeat(4,minmax(0,1fr))]">
            <div>
                <label for="o-search" class="sr-only">Cari sasaran</label>
                <x-ui.input id="o-search" wire:model.live.debounce.400ms="search" icon="search"
                            placeholder="Cari nama lokasi, rujukan atau kontak…" />
            </div>

            <div>
                <label for="f-assignee" class="sr-only">Tapis mengikut staf</label>
                <x-ui.select id="f-assignee" wire:model.live="assignee">
                    <option value="">Semua staf</option>
                    @foreach ($staff as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            <div>
                <label for="f-state" class="sr-only">Tapis mengikut negeri</label>
                <x-ui.select id="f-state" wire:model.live="state">
                    <option value="">Semua negeri</option>
                    @foreach ($states as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            <div>
                <label for="f-source" class="sr-only">Tapis mengikut sumber</label>
                <x-ui.select id="f-source" wire:model.live="source">
                    <option value="">Semua sumber</option>
                    @foreach ($sources as $option)
                        <option value="{{ $option->value }}">{{ $option->label() }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            <div>
                <label for="f-priority" class="sr-only">Tapis mengikut keutamaan</label>
                <x-ui.select id="f-priority" wire:model.live="priority">
                    <option value="">Semua keutamaan</option>
                    @foreach ($priorities as $option)
                        <option value="{{ $option->value }}">{{ $option->label() }}</option>
                    @endforeach
                </x-ui.select>
            </div>
        </div>

        <div class="mt-3 flex flex-wrap items-center gap-2">
            <button type="button" wire:click="$toggle('mineOnly')"
                    aria-pressed="{{ $mineOnly ? 'true' : 'false' }}"
                    class="tap-target rounded-full px-4 text-sm font-medium transition
                           {{ $mineOnly ? 'bg-clay-600 text-white' : 'bg-mist text-ink-soft hover:text-ink' }}">
                Sasaran saya
            </button>

            <button type="button" wire:click="$toggle('overdueOnly')"
                    aria-pressed="{{ $overdueOnly ? 'true' : 'false' }}"
                    class="tap-target rounded-full px-4 text-sm font-medium transition
                           {{ $overdueOnly ? 'bg-alert text-white' : 'bg-mist text-ink-soft hover:text-ink' }}">
                Tertunggak
            </button>

            @if ($this->hasFilters())
                <button type="button" wire:click="clearFilters"
                        class="tap-target rounded-full px-4 text-sm text-ink-muted hover:text-ink">
                    Kosongkan penapis
                </button>
            @endif
        </div>
    </div>

    {{-- ══ PAPAN ═════════════════════════════════════════════ --}}
    @if ($view === 'papan')
        @if ($total === 0)
            <x-ui.empty-state class="mt-6" icon="pin"
                title="{{ $this->hasFilters() ? 'Tiada sasaran sepadan' : 'Papan sasaran masih kosong' }}"
                description="{{ $this->hasFilters()
                    ? 'Cuba longgarkan penapis anda.'
                    : 'Mula dengan merekod satu masjid atau sekolah yang anda mahu dekati.' }}">
                @if ($this->hasFilters())
                    <x-ui.button wire:click="clearFilters" variant="outline" size="sm" class="mt-4">
                        Kosongkan Penapis
                    </x-ui.button>
                @else
                    <x-ui.button wire:click="create" variant="primary" size="sm" class="mt-4" icon="plus">
                        Tambah Sasaran Pertama
                    </x-ui.button>
                @endif
            </x-ui.empty-state>
        @else
            <div class="relative mt-6" x-data="kanbanScroll" x-init="init()">
                <button type="button" x-show="!atStart" x-cloak x-on:click="scroll(-1)"
                        class="tap-target absolute -left-3 top-1/2 z-10 grid h-9 w-9 -translate-y-1/2 place-items-center
                               rounded-full border border-hairline bg-surface text-ink-soft shadow-soft
                               transition hover:text-ink"
                        aria-label="Tatal papan ke kiri">
                    <x-ui.icon name="arrow-left" class="h-4 w-4" />
                </button>
                <button type="button" x-show="!atEnd" x-cloak x-on:click="scroll(1)"
                        class="tap-target absolute -right-3 top-1/2 z-10 grid h-9 w-9 -translate-y-1/2 place-items-center
                               rounded-full border border-hairline bg-surface text-ink-soft shadow-soft
                               transition hover:text-ink"
                        aria-label="Tatal papan ke kanan">
                    <x-ui.icon name="arrow-right" class="h-4 w-4" />
                </button>

                <div x-ref="scroller"
                     class="no-scrollbar flex snap-x snap-mandatory gap-4 overflow-x-auto pb-2">
                @foreach ($stages as $stage)
                    @php $items = $grouped[$stage->value] ?? collect(); @endphp
                    <section class="w-[17rem] shrink-0 snap-start" aria-label="{{ $stage->label() }}">
                        <header class="flex items-center justify-between gap-2 px-1 pb-2.5">
                            <h3 class="text-sm font-semibold text-ink">{{ $stage->label() }}</h3>
                            <span class="rounded-full bg-mist px-2 py-0.5 text-xs font-medium text-ink-soft">
                                {{ $items->count() }}
                            </span>
                        </header>

                        <div class="min-h-[6rem] space-y-2.5 rounded-card bg-mist/50 p-2.5">
                            @forelse ($items as $target)
                                <article wire:key="t-{{ $target->id }}"
                                         class="rounded-xl border border-hairline bg-surface p-3.5 shadow-soft
                                                transition hover:border-clay-200">
                                    <div class="flex items-start gap-2">
                                        <x-ui.icon :name="$target->type->icon()"
                                                   class="mt-0.5 h-4 w-4 shrink-0 text-ink-muted" />
                                        <a href="{{ route('admin.sasaran.show', $target) }}"
                                           class="min-w-0 flex-1 text-sm font-medium leading-snug text-ink
                                                  hover:text-clay-700 text-pretty">
                                            {{ $target->name }}
                                        </a>
                                        @if ($target->priority === \App\Enums\OutreachPriority::Tinggi)
                                            <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-alert"
                                                  title="Keutamaan tinggi"></span>
                                        @endif
                                    </div>

                                    <p class="mt-1.5 text-xs text-ink-muted">{{ $target->locationLabel() }}</p>

                                    <div class="mt-2.5 flex flex-wrap gap-1.5">
                                        <x-ui.badge :color="$target->source->color()" :icon="$target->source->icon()">
                                            {{ $target->partner?->name
                                                ?? $target->referrer?->name
                                                ?? $target->referrer_name
                                                ?? $target->source->label() }}
                                        </x-ui.badge>
                                    </div>

                                    <div class="mt-3 flex items-center justify-between gap-2 border-t border-hairline pt-2.5">
                                        <span class="min-w-0 truncate text-xs {{ $target->isOverdue() ? 'font-medium text-alert' : 'text-ink-muted' }}">
                                            @if ($target->next_action_at)
                                                {{ $target->isOverdue() ? 'Tertunggak' : 'Susul' }}
                                                {{ $target->next_action_at->translatedFormat('j M') }}
                                            @elseif ($target->assignee)
                                                {{ \Illuminate\Support\Str::before($target->assignee->name, ' ') }}
                                            @else
                                                Belum ditetapkan
                                            @endif
                                        </span>

                                        @if ($stage !== \App\Enums\OutreachStage::Berjaya)
                                            <button type="button" wire:click="advance({{ $target->id }})"
                                                    class="shrink-0 rounded-full p-1.5 text-ink-muted transition
                                                           hover:bg-clay-50 hover:text-clay-700"
                                                    aria-label="Gerakkan {{ $target->name }} ke peringkat seterusnya">
                                                <x-ui.icon name="arrow-right" class="h-4 w-4" />
                                            </button>
                                        @endif
                                    </div>
                                </article>
                            @empty
                                <p class="px-2 py-4 text-center text-xs text-ink-muted">Kosong</p>
                            @endforelse
                        </div>
                    </section>
                @endforeach
                </div>
            </div>

            @if ($closed->isNotEmpty())
                <details class="mt-5 rounded-card border border-hairline bg-surface">
                    <summary class="tap-target flex cursor-pointer items-center justify-between px-5 text-sm font-medium text-ink">
                        Ditutup & ditangguh ({{ $closed->count() }})
                        <x-ui.icon name="chevron-down" class="h-4 w-4 text-ink-muted" />
                    </summary>
                    <ul class="divide-y divide-hairline border-t border-hairline">
                        @foreach ($closed as $target)
                            <li class="flex flex-wrap items-center justify-between gap-3 px-5 py-3">
                                <div class="min-w-0">
                                    <a href="{{ route('admin.sasaran.show', $target) }}"
                                       class="text-sm font-medium text-ink hover:text-clay-700">
                                        {{ $target->name }}
                                    </a>
                                    <p class="text-xs text-ink-muted">
                                        {{ $target->locationLabel() }}
                                        @if ($target->closed_reason) &middot; {{ $target->closed_reason }} @endif
                                    </p>
                                </div>
                                <x-ui.badge :color="$target->stage->color()">{{ $target->stage->label() }}</x-ui.badge>
                            </li>
                        @endforeach
                    </ul>
                </details>
            @endif
        @endif
    @endif

    {{-- ══ SENARAI ═══════════════════════════════════════════ --}}
    @if ($view === 'senarai')
        @if ($total === 0)
            <x-ui.empty-state class="mt-6" icon="pin" title="Tiada sasaran sepadan" />
        @else
            <div class="mt-6">
                <x-jelajah.admin-table caption="Senarai sasaran jelajah"
                    :headers="['Lokasi', 'Kawasan', 'Sumber', 'Staf', 'Peringkat', 'Tindakan', '']">
                    @foreach ($grouped->flatten() as $target)
                        <tr wire:key="row-{{ $target->id }}" class="hover:bg-mist/40">
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.sasaran.show', $target) }}"
                                   class="font-medium text-ink hover:text-clay-700">{{ $target->name }}</a>
                                <p class="font-mono text-xs text-ink-muted">{{ $target->reference_no }}</p>
                            </td>
                            <td class="px-4 py-3 text-ink-soft">{{ $target->locationLabel() }}</td>
                            <td class="px-4 py-3">
                                <x-ui.badge :color="$target->source->color()">{{ $target->sourceLabel() }}</x-ui.badge>
                            </td>
                            <td class="px-4 py-3 text-ink-soft">{{ $target->assignee?->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <x-ui.badge :color="$target->stage->color()">{{ $target->stage->label() }}</x-ui.badge>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm {{ $target->isOverdue() ? 'font-medium text-alert' : 'text-ink-muted' }}">
                                {{ $target->next_action_at?->translatedFormat('j M Y') ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <x-ui.button :href="route('admin.sasaran.show', $target)"
                                             variant="ghost" size="sm">Buka</x-ui.button>
                            </td>
                        </tr>
                    @endforeach
                </x-jelajah.admin-table>
            </div>
        @endif
    @endif

    {{-- ══ RAKAN ═════════════════════════════════════════════ --}}
    @if ($view === 'rakan')
        <div class="mt-6">
            <h2 class="text-lg font-semibold text-ink">Sumbangan Rakan</h2>
            <p class="mt-1 text-sm text-ink-soft text-pretty">
                Berapa lokasi dibawa oleh setiap rakan, dan berapa yang akhirnya menjadi jelajah sebenar.
            </p>

            @if ($partnerPerformance->isEmpty())
                <x-ui.empty-state class="mt-5" icon="handshake"
                    title="Belum ada sasaran daripada rakan"
                    description="Apabila anda merekod sasaran dengan sumber “Melalui Rakan”, prestasi setiap rakan muncul di sini." />
            @else
                <div class="mt-5">
                    <x-jelajah.admin-table caption="Prestasi rakan membawa lokasi"
                        :headers="['Rakan', 'Dibawa', 'Masih aktif', 'Berjaya', 'Kadar berjaya']">
                        @foreach ($partnerPerformance as $row)
                            @php
                                $rate = $row->jumlah > 0 ? round($row->berjaya / $row->jumlah * 100) : 0;
                            @endphp
                            <tr class="hover:bg-mist/40">
                                <td class="px-4 py-3 font-medium text-ink">
                                    {{ $row->partner?->name ?? 'Rakan dibuang' }}
                                </td>
                                <td class="px-4 py-3 text-ink">{{ $row->jumlah }}</td>
                                <td class="px-4 py-3 text-ink-soft">{{ $row->aktif }}</td>
                                <td class="px-4 py-3 text-ink">{{ $row->berjaya }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <span class="h-1.5 w-20 overflow-hidden rounded-full bg-mist">
                                            <span class="block h-full rounded-full bg-clay-600"
                                                  style="width: {{ $rate }}%"></span>
                                        </span>
                                        <span class="text-sm text-ink-soft">{{ $rate }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </x-jelajah.admin-table>
                </div>
            @endif
        </div>
    @endif
</div>
