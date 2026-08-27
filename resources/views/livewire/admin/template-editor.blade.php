@php
    $channelLabels = ['in_app' => 'In-App', 'mail' => 'E-mel', 'whatsapp' => 'WhatsApp'];
@endphp

<li class="py-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <x-ui.badge :color="$template->channel === 'whatsapp' ? 'success' : 'grey'">
                    {{ $channelLabels[$template->channel] ?? $template->channel }}
                </x-ui.badge>
                @if ($template->subject)
                    <span class="text-sm font-medium text-ink">{{ $template->subject }}</span>
                @endif
                <button type="button" wire:click="toggleActive" aria-label="Tukar status aktif">
                    <x-ui.badge :color="$template->is_active ? 'success' : 'warning'">
                        {{ $template->is_active ? 'Aktif' : 'Tidak aktif' }}
                    </x-ui.badge>
                </button>
            </div>

            @unless ($open)
                <p class="mt-2 whitespace-pre-line text-sm text-ink-soft text-pretty">{{ $template->body }}</p>
            @endunless
        </div>

        <x-ui.button wire:click="toggle" variant="ghost" size="sm" icon="edit">
            {{ $open ? 'Tutup' : 'Ubah' }}
        </x-ui.button>
    </div>

    @if (! empty($template->placeholders) && ! $open)
        <ul class="mt-2.5 flex flex-wrap gap-1.5">
            @foreach ($template->placeholders as $placeholder)
                <li class="rounded bg-mist px-1.5 py-0.5 font-mono text-[0.68rem] text-ink-soft">
                    &#123;&#123;{{ $placeholder }}&#125;&#125;
                </li>
            @endforeach
        </ul>
    @endif

    @if ($open)
        <form wire:submit="save" class="mt-4 space-y-4 rounded-xl border border-clay-200 bg-clay-50/50 p-4">
            @if ($template->channel === 'mail')
                <x-ui.field label="Tajuk e-mel" for="tpl-subject-{{ $template->id }}"
                            :error="$errors->first('subject')">
                    <x-ui.input id="tpl-subject-{{ $template->id }}" wire:model="subject"
                                :error="$errors->has('subject')" />
                </x-ui.field>
            @endif

            <x-ui.field label="Kandungan" for="tpl-body-{{ $template->id }}" required
                        :error="$errors->first('body')">
                <x-ui.textarea id="tpl-body-{{ $template->id }}" wire:model="body" rows="6"
                               :error="$errors->has('body')" class="font-mono text-sm" />
            </x-ui.field>

            @if (! empty($template->placeholders))
                <div>
                    <p class="text-xs font-medium text-ink">Placeholder yang tersedia</p>
                    <ul class="mt-1.5 flex flex-wrap gap-1.5">
                        @foreach ($template->placeholders as $placeholder)
                            <li class="rounded bg-surface px-1.5 py-0.5 font-mono text-[0.68rem] text-clay-700
                                       ring-1 ring-hairline">
                                &#123;&#123;{{ $placeholder }}&#125;&#125;
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <x-ui.choice type="checkbox" name="is_active" value="1"
                         wire:model="is_active" :checked="$is_active"
                         label="Aktif"
                         hint="Template tidak aktif akan dilangkau semasa penghantaran." />

            <div class="flex flex-wrap gap-2.5">
                <x-ui.button type="submit" variant="primary" size="sm"
                             wire:loading.attr="disabled" wire:target="save">
                    Simpan
                </x-ui.button>
                <x-ui.button type="button" wire:click="toggle" variant="ghost" size="sm">Batal</x-ui.button>
            </div>
        </form>
    @endif
</li>
