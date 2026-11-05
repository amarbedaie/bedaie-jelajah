<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\NotifiesUser;
use App\Models\Blueprint;
use App\Models\BlueprintItem;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * Urus blueprint SOP (masjid, sekolah, institusi …) dan item-item
 * sebelum / semasa / selepas program.
 */
class BlueprintManager extends Component
{
    use NotifiesUser;

    public ?int $selectedId = null;

    // Butiran blueprint
    public string $name = '';

    public string $venue_type = '';

    public string $description = '';

    public bool $is_active = true;

    // Item baharu
    public string $itemPhase = 'sebelum';

    public string $itemTitle = '';

    public string $itemOffset = '';

    // Suntingan item sedia ada
    public ?int $editingItemId = null;

    public string $editTitle = '';

    public string $editOffset = '';

    public string $editDetails = '';

    public function mount(): void
    {
        if ($first = Blueprint::ordered()->first()) {
            $this->select($first->id);
        }
    }

    public function select(int $id): void
    {
        $bp = Blueprint::findOrFail($id);

        $this->selectedId = $bp->id;
        $this->name = $bp->name;
        $this->venue_type = (string) $bp->venue_type;
        $this->description = (string) $bp->description;
        $this->is_active = (bool) $bp->is_active;
        $this->cancelEditItem();
        $this->resetValidation();
    }

    public function createBlueprint(): void
    {
        $bp = Blueprint::create([
            'name' => 'Blueprint Baharu',
            'slug' => 'blueprint-'.Str::lower(Str::random(6)),
            'is_active' => true,
            'sort_order' => (int) Blueprint::max('sort_order') + 1,
        ]);

        $this->select($bp->id);
    }

    public function duplicate(): void
    {
        $source = Blueprint::with('items')->findOrFail($this->selectedId);

        $copy = DB::transaction(function () use ($source) {
            $copy = Blueprint::create([
                'name' => $source->name.' (salinan)',
                'slug' => $source->slug.'-'.Str::lower(Str::random(4)),
                'venue_type' => $source->venue_type,
                'description' => $source->description,
                'is_active' => true,
                'sort_order' => (int) Blueprint::max('sort_order') + 1,
            ]);

            foreach ($source->items as $item) {
                $copy->items()->create($item->only(['phase', 'title', 'details', 'due_offset_days', 'sort_order']));
            }

            return $copy;
        });

        $this->select($copy->id);
        $this->notify('Blueprint disalin.', 'success');
    }

    public function saveBlueprint(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'venue_type' => ['nullable', Rule::in(array_keys(Blueprint::VENUE_TYPES))],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ], [], ['name' => 'nama blueprint', 'venue_type' => 'jenis lokasi', 'description' => 'penerangan']);

        $bp = Blueprint::findOrFail($this->selectedId);
        $bp->update([
            'name' => $data['name'],
            'venue_type' => $data['venue_type'] ?: null,
            'description' => $data['description'] ?: null,
            'is_active' => $data['is_active'],
        ]);

        ActivityLogger::log('blueprint.updated', $bp, "Blueprint {$bp->name} dikemas kini.");
        $this->notify('Blueprint disimpan.', 'success');
    }

    public function addItem(): void
    {
        $this->validate([
            'itemPhase' => ['required', Rule::in(array_keys(Blueprint::PHASES))],
            'itemTitle' => ['required', 'string', 'min:3', 'max:200'],
            'itemOffset' => ['nullable', 'integer', 'min:-365', 'max:365'],
        ], [], ['itemTitle' => 'item', 'itemOffset' => 'hari']);

        BlueprintItem::create([
            'blueprint_id' => $this->selectedId,
            'phase' => $this->itemPhase,
            'title' => $this->itemTitle,
            'due_offset_days' => $this->itemOffset !== '' ? (int) $this->itemOffset : null,
            'sort_order' => (int) BlueprintItem::where('blueprint_id', $this->selectedId)
                ->where('phase', $this->itemPhase)->max('sort_order') + 1,
        ]);

        $this->reset('itemTitle', 'itemOffset');
        $this->notify('Item ditambah.', 'success');
    }

    public function editItem(int $id): void
    {
        $item = $this->item($id);

        $this->editingItemId = $item->id;
        $this->editTitle = $item->title;
        $this->editOffset = $item->due_offset_days === null ? '' : (string) $item->due_offset_days;
        $this->editDetails = (string) $item->details;
    }

    public function saveItem(): void
    {
        $this->validate([
            'editTitle' => ['required', 'string', 'min:3', 'max:200'],
            'editOffset' => ['nullable', 'integer', 'min:-365', 'max:365'],
            'editDetails' => ['nullable', 'string', 'max:1000'],
        ], [], ['editTitle' => 'item', 'editOffset' => 'hari', 'editDetails' => 'butiran']);

        $this->item($this->editingItemId)->update([
            'title' => $this->editTitle,
            'due_offset_days' => $this->editOffset !== '' ? (int) $this->editOffset : null,
            'details' => $this->editDetails ?: null,
        ]);

        $this->cancelEditItem();
    }

    public function cancelEditItem(): void
    {
        $this->reset('editingItemId', 'editTitle', 'editOffset', 'editDetails');
    }

    public function moveItem(int $id, int $direction): void
    {
        $item = $this->item($id);
        $siblings = BlueprintItem::where('blueprint_id', $item->blueprint_id)->where('phase', $item->phase)
            ->orderBy('sort_order')->orderBy('id')->get()->values();

        $from = $siblings->search(fn ($s) => $s->id === $item->id);
        $to = $from + $direction;

        if ($to < 0 || $to >= $siblings->count()) {
            return;
        }

        $ordered = $siblings->all();
        [$ordered[$from], $ordered[$to]] = [$ordered[$to], $ordered[$from]];

        foreach ($ordered as $i => $s) {
            $s->update(['sort_order' => $i]);
        }
    }

    /** Item yang sudah disalin ke program kekal pada program itu (pautan sahaja dilepaskan). */
    public function removeItem(int $id): void
    {
        $this->item($id)->delete();
        $this->notify('Item dibuang daripada blueprint. Senarai semak program sedia ada tidak terjejas.', 'info');
    }

    private function item(int $id): BlueprintItem
    {
        return BlueprintItem::where('blueprint_id', $this->selectedId)->findOrFail($id);
    }

    public function render()
    {
        $selected = $this->selectedId ? Blueprint::with('items')->find($this->selectedId) : null;

        return view('livewire.admin.blueprint-manager', [
            'blueprints' => Blueprint::ordered()->withCount('items')->get(),
            'selected' => $selected,
            'grouped' => $selected?->items->groupBy('phase') ?? collect(),
            'phases' => Blueprint::PHASES,
            'venueTypes' => Blueprint::VENUE_TYPES,
        ]);
    }
}
