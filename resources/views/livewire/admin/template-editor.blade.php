@php
    $channelLabels = ['in_app' => 'In-App', 'mail' => 'E-mel', 'whatsapp' => 'WhatsApp'];
@endphp

<li class="py-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <x-ui.badge :color="$channel === 'whatsapp' ? 'success' : 'grey'">
                    {{ $channelLabels[$channel] ?? $channel }}
                </x-ui.badge>
                @if ($subject)
                    <span class="text-sm font-medium text-ink">{{ $subject }}</span>
                @endif
                <button type="button" wire:click="toggleActive" aria-label="Tukar status aktif">
                    <x-ui.badge :color="$is_active ? 'success' : 'warning'">
                        {{ $is_active ? 'Aktif' : 'Tidak aktif' }}
                    </x-ui.badge>
                </button>
            </div>

            @unless ($open)
                <p class="mt-2 whitespace-pre-line text-sm text-ink-soft text-pretty">{{ $body }}</p>
            @endunless
        </div>

        <x-ui.button wire:click="toggle" variant="ghost" size="sm" icon="edit">
            {{ $open ? 'Tutup' : 'Ubah' }}
        </x-ui.button>
    </div>

    @if (! empty($placeholders) && ! $open)
        <ul class="mt-2.5 flex flex-wrap gap-1.5">
            @foreach ($placeholders as $placeholder)
                <li class="rounded bg-mist px-1.5 py-0.5 font-mono text-[0.68rem] text-ink-soft">
                    &#123;&#123;{{ $placeholder }}&#125;&#125;
                </li>
            @endforeach
        </ul>
    @endif

    @if ($open)
        <form wire:submit="save" class="mt-4 space-y-4 rounded-xl border border-brand-200 bg-brand-50/50 p-4">
            @if ($channel === 'mail')
                <x-ui.field label="Tajuk e-mel" for="tpl-subject-{{ $templateId }}"
                            :error="$errors->first('subject')">
                    <x-ui.input id="tpl-subject-{{ $templateId }}" wire:model="subject"
                                :error="$errors->has('subject')" />
                </x-ui.field>
            @endif

            <x-ui.field label="Kandungan" for="tpl-body-{{ $templateId }}" required
                        :error="$errors->first('body')">
                <x-ui.textarea id="tpl-body-{{ $templateId }}" wire:model="body" rows="6"
                               :error="$errors->has('body')" class="font-mono text-sm" />
            </x-ui.field>

            @if (! empty($placeholders))
                <div>
                    <p class="text-[0.8125rem] font-medium text-ink">Placeholder yang tersedia</p>
                    <ul class="mt-1.5 flex flex-wrap gap-1.5">
                        @foreach ($placeholders as $placeholder)
                            <li class="rounded bg-surface px-1.5 py-0.5 font-mono text-[0.68rem] text-brand-700
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
