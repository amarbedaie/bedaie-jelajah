<x-layouts.admin :title="$target->name" :heading="$target->name">
    <a href="{{ route('admin.sasaran') }}"
       class="inline-flex items-center gap-1.5 text-sm font-medium text-ink-soft hover:text-ink">
        <x-ui.icon name="arrow-left" class="h-4 w-4" /> Papan sasaran
    </a>

    <div class="mt-4 mb-6 flex flex-wrap items-center gap-3">
        <span class="font-mono text-sm text-ink-muted">{{ $target->reference_no }}</span>
        <x-ui.badge :color="$target->stage->color()" dot>{{ $target->stage->label() }}</x-ui.badge>
        <x-ui.badge :color="$target->priority->color()">Keutamaan {{ $target->priority->label() }}</x-ui.badge>
        @if ($target->assignee)
            <x-ui.badge color="grey" icon="user">{{ $target->assignee->name }}</x-ui.badge>
        @endif
    </div>

    <livewire:admin.outreach-detail :target="$target" />
</x-layouts.admin>
