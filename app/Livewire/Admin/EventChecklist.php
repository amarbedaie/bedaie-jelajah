<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\NotifiesUser;
use App\Models\Blueprint;
use App\Models\Event;
use App\Models\EventChecklistItem;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Senarai semak SOP program (sebelum / semasa / selepas) yang dijana
 * daripada blueprint — pasukan tanda siap supaya tiada langkah tertinggal.
 */
class EventChecklist extends Component
{
    use NotifiesUser;

    #[Locked]
    public int $eventId;

    public string $blueprintId = '';

    // Item tambahan khusus untuk program ini.
    public string $newTitle = '';

    public string $newPhase = 'sebelum';

    public function mount(Event $event): void
    {
        $this->eventId = $event->id;
        $this->blueprintId = (string) ($event->blueprint_id ?? Blueprint::suggestFor($event)?->id ?? '');
    }

    public function getEventProperty(): Event
    {
        return Event::with(['venue', 'category', 'blueprint'])->findOrFail($this->eventId);
    }

    /** Salin item blueprint ke program. Item sedia ada (termasuk yang sudah ditanda) dikekalkan. */
    public function applyBlueprint(): void
    {
        $this->validate(['blueprintId' => ['required', Rule::exists('blueprints', 'id')]],
            [], ['blueprintId' => 'blueprint']);

        $event = $this->event;
        $blueprint = Blueprint::with('items')->findOrFail((int) $this->blueprintId);

        $added = DB::transaction(function () use ($event, $blueprint) {
            $existing = $event->checklistItems()->whereNotNull('blueprint_item_id')
                ->pluck('blueprint_item_id')->all();
            $offset = (int) $event->checklistItems()->max('sort_order') + 1;
            $added = 0;

            foreach ($blueprint->items as $item) {
                if (in_array($item->id, $existing, true)) {
                    continue;
                }

                $event->checklistItems()->create([
                    'blueprint_item_id' => $item->id,
                    'phase' => $item->phase,
                    'title' => $item->title,
                    'details' => $item->details,
                    'due_offset_days' => $item->due_offset_days,
                    'sort_order' => $offset + $item->sort_order,
                ]);
                $added++;
            }

            $event->update(['blueprint_id' => $blueprint->id]);

            return $added;
        });

        ActivityLogger::log('event.blueprint_applied', $event, "{$blueprint->name} digunakan ({$added} item).");
        $this->notify($added > 0 ? "{$added} item SOP ditambah daripada {$blueprint->name}." : 'Semua item blueprint sudah ada.', 'success');
    }

    public function toggle(int $itemId): void
    {
        $item = EventChecklistItem::where('event_id', $this->eventId)->findOrFail($itemId);

        $item->update($item->isDone()
            ? ['completed_at' => null, 'completed_by' => null]
            : ['completed_at' => now(), 'completed_by' => auth()->id()]);
    }

    public function addItem(): void
    {
        $this->validate([
            'newTitle' => ['required', 'string', 'min:3', 'max:200'],
            'newPhase' => ['required', Rule::in(array_keys(Blueprint::PHASES))],
        ], [], ['newTitle' => 'item', 'newPhase' => 'fasa']);

        EventChecklistItem::create([
            'event_id' => $this->eventId,
            'phase' => $this->newPhase,
            'title' => $this->newTitle,
            'sort_order' => (int) EventChecklistItem::where('event_id', $this->eventId)->max('sort_order') + 1,
        ]);

        $this->reset('newTitle');
        $this->notify('Item ditambah.', 'success');
    }

    /** Hanya item tambahan yang belum ditanda boleh dibuang; item blueprint kekal. */
    public function removeItem(int $itemId): void
    {
        $item = EventChecklistItem::where('event_id', $this->eventId)->findOrFail($itemId);

        if ($item->blueprint_item_id || $item->isDone()) {
            $this->notify('Item blueprint atau item yang sudah ditanda tidak boleh dibuang.', 'info');

            return;
        }

        $item->delete();
    }

    public function render()
    {
        $event = $this->event;
        $items = $event->checklistItems()->with('completer')->get();

        return view('livewire.admin.event-checklist', [
            'event' => $event,
            'blueprints' => Blueprint::active()->ordered()->get(),
            'phases' => Blueprint::PHASES,
            'grouped' => $items->groupBy('phase'),
            'total' => $items->count(),
            'done' => $items->whereNotNull('completed_at')->count(),
        ]);
    }
}
