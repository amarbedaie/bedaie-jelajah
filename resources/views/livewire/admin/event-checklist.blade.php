<x-ui.card class="sm:p-7">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="font-display text-2xl text-ink">Senarai Semak SOP</h2>
            <p class="mt-1.5 text-sm text-ink-soft text-pretty">
                Keperluan sebelum, semasa dan selepas program daripada
                <a href="{{ route('admin.blueprint') }}" class="font-medium underline">blueprint</a>.
                Tanda setiap langkah apabila siap.
            </p>
        </div>
        @if ($total > 0)
            <x-ui.badge :color="$done === $total ? 'success' : 'grey'">{{ $done }}/{{ $total }} siap</x-ui.badge>
        @endif
    </div>

    <div class="mt-5 flex flex-wrap items-end gap-2.5">
        <x-ui.field label="Blueprint" for="cl-blueprint" class="min-w-[16rem] flex-1" :error="$errors->first('blueprintId')">
            <x-ui.select id="cl-blueprint" wire:model="blueprintId">
                <option value="">— Pilih blueprint —</option>
                @foreach ($blueprints as $bp)
                    <option value="{{ $bp->id }}">{{ $bp->name }}</option>
                @endforeach
            </x-ui.select>
        </x-ui.field>
        <x-ui.button wire:click="applyBlueprint" variant="{{ $total ? 'outline' : 'primary' }}" icon="clipboard"
                     wire:loading.attr="disabled" wire:target="applyBlueprint">
            {{ $total ? 'Tambah item baharu dari blueprint' : 'Guna blueprint' }}
        </x-ui.button>
    </div>

    @if ($total > 0)
        <x-ui.progress class="mt-5" :value="$done" :max="$total" label="Kemajuan SOP" />

        <div class="mt-6 grid gap-5 lg:grid-cols-3">
            @foreach ($phases as $phase => $phaseLabel)
                @php $list = $grouped->get($phase, collect()); @endphp
                <section>
                    <div class="flex items-baseline justify-between gap-2 border-b border-hairline pb-2">
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-ink-muted">{{ $phaseLabel }}</h3>
                        <span class="text-xs tabular-nums text-ink-muted">
                            {{ $list->whereNotNull('completed_at')->count() }}/{{ $list->count() }}
                        </span>
                    </div>

                    <ul class="mt-2 space-y-1.5">
                        @forelse ($list as $item)
                            @php
                                $due = $item->dueDate($event->starts_at);
                                $overdue = $due && ! $item->isDone() && $due->isPast() && ! $due->isToday();
                            @endphp
                            <li wire:key="cl-{{ $item->id }}" class="group flex items-start gap-2.5 rounded-lg p-2 hover:bg-mist/50">
                                <input type="checkbox" wire:click="toggle({{ $item->id }})" @checked($item->isDone())
                                       aria-label="Tanda siap: {{ $item->title }}"
                                       class="mt-0.5 h-5 w-5 shrink-0 cursor-pointer rounded border-hairline text-brand-600 focus:ring-brand-400">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm text-pretty {{ $item->isDone() ? 'text-ink-muted line-through' : 'text-ink' }}">
                                        {{ $item->title }}
                                    </p>
                                    <p class="mt-0.5 text-xs {{ $overdue ? 'font-medium text-alert' : 'text-ink-muted' }}">
                                        @if ($item->isDone())
                                            Siap {{ $item->completed_at->translatedFormat('j M') }}{{ $item->completer ? ' · '.$item->completer->name : '' }}
                                        @elseif ($due)
                                            {{ $overdue ? 'Lewat — ' : '' }}{{ \App\Models\BlueprintItem::offsetLabel($item->due_offset_days) }}
                                            ({{ $due->translatedFormat('j M') }})
                                        @endif
                                    </p>
                                </div>
                                @if (! $item->blueprint_item_id && ! $item->isDone())
                                    <button type="button" wire:click="removeItem({{ $item->id }})" wire:confirm="Buang item ini?"
                                            class="opacity-0 transition group-hover:opacity-100" aria-label="Buang item">
                                        <x-ui.icon name="x" class="h-4 w-4 text-ink-muted" />
                                    </button>
                                @endif
                            </li>
                        @empty
                            <li class="p-2 text-sm text-ink-muted">Tiada item.</li>
                        @endforelse
                    </ul>
                </section>
            @endforeach
        </div>

        <form wire:submit="addItem" class="mt-6 flex flex-wrap items-end gap-2.5 border-t border-hairline pt-5">
            <x-ui.field label="Item tambahan untuk program ini" for="cl-new" class="min-w-[16rem] flex-1"
                        :error="$errors->first('newTitle')">
                <x-ui.input id="cl-new" wire:model="newTitle" placeholder="Contoh: Tempah van untuk pasukan" />
            </x-ui.field>
            <x-ui.field label="Fasa" for="cl-phase">
                <x-ui.select id="cl-phase" wire:model="newPhase">
                    @foreach ($phases as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>
            <x-ui.button type="submit" variant="outline" icon="plus">Tambah</x-ui.button>
        </form>
    @endif
</x-ui.card>
