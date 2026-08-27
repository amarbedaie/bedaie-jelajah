<form wire:submit="save" class="space-y-6">
    @forelse ($groups as $groupName => $settings)
        <x-ui.card>
            <h2 class="font-semibold text-ink">{{ \Illuminate\Support\Str::headline($groupName) }}</h2>

            <div class="mt-5 space-y-5">
                @foreach ($settings as $setting)
                    <x-ui.field :label="$setting->label ?: $setting->key"
                                :for="'set-'.$setting->id"
                                :hint="$setting->hint"
                                :error="$errors->first('values.'.$setting->id)">
                        @if ($setting->type === 'longtext')
                            <x-ui.textarea :id="'set-'.$setting->id"
                                           wire:model="values.{{ $setting->id }}" rows="6"
                                           :error="$errors->has('values.'.$setting->id)" />
                        @elseif ($setting->type === 'number')
                            <x-ui.input :id="'set-'.$setting->id" type="number"
                                        wire:model="values.{{ $setting->id }}"
                                        :error="$errors->has('values.'.$setting->id)" />
                        @elseif ($setting->type === 'url')
                            <x-ui.input :id="'set-'.$setting->id" type="url"
                                        wire:model="values.{{ $setting->id }}"
                                        :error="$errors->has('values.'.$setting->id)" />
                        @elseif ($setting->type === 'email')
                            <x-ui.input :id="'set-'.$setting->id" type="email"
                                        wire:model="values.{{ $setting->id }}"
                                        :error="$errors->has('values.'.$setting->id)" />
                        @else
                            <x-ui.input :id="'set-'.$setting->id"
                                        wire:model="values.{{ $setting->id }}"
                                        :error="$errors->has('values.'.$setting->id)" />
                        @endif

                        <p class="mt-1 font-mono text-[0.68rem] text-ink-muted">{{ $setting->key }}</p>
                    </x-ui.field>
                @endforeach
            </div>
        </x-ui.card>
    @empty
        <x-ui.empty-state icon="settings" title="Tiada tetapan dalam kumpulan ini" />
    @endforelse

    @if ($groups->isNotEmpty())
        <div class="sticky bottom-4">
            <x-ui.button type="submit" variant="primary" size="lg"
                         wire:loading.attr="disabled" wire:target="save" class="shadow-lift">
                <span wire:loading.remove wire:target="save">Simpan Tetapan</span>
                <span wire:loading wire:target="save">Menyimpan…</span>
            </x-ui.button>
        </div>
    @endif
</form>
