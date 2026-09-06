<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\NotifiesUser;
use App\Models\Event;
use App\Models\EventFlowStep;
use App\Services\ActivityLogger;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Perancangan dalaman program: objektif, aliran acara dan SOP.
 *
 * Dokumen kerja pasukan BeDaie. Kebenaran dikuatkuasakan pada setiap
 * tindakan, bukan hanya disembunyikan pada paparan — komponen Livewire
 * boleh dipanggil terus melalui /livewire/update.
 */
class InternalPlanning extends Component
{
    use NotifiesUser;

    #[Locked]
    public int $eventId;

    public string $objectives = '';

    public string $sop = '';

    public string $flowNotes = '';

    /** Baris aliran acara: masa, aktiviti, siapa bertanggungjawab. */
    public array $steps = [];

    public function mount(Event $event): void
    {
        $this->authorize('viewInternalPlanning', $event);

        $this->eventId = $event->id;
        $this->objectives = (string) $event->objectives;
        $this->sop = (string) $event->sop;
        $this->flowNotes = (string) $event->flow_notes;

        $this->steps = $event->flowSteps()->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'time_label' => (string) $s->time_label,
                'activity' => (string) $s->activity,
                'responsible' => (string) $s->responsible,
            ])->all();

        if ($this->steps === []) {
            $this->addStep();
        }
    }

    public function getEventProperty(): Event
    {
        return Event::findOrFail($this->eventId);
    }

    public function addStep(): void
    {
        $this->authorize('viewInternalPlanning', $this->event);

        $this->steps[] = ['id' => null, 'time_label' => '', 'activity' => '', 'responsible' => ''];
    }

    public function removeStep(int $index): void
    {
        $this->authorize('viewInternalPlanning', $this->event);

        unset($this->steps[$index]);
        $this->steps = array_values($this->steps);

        if ($this->steps === []) {
            $this->addStep();
        }
    }

    public function moveStep(int $index, int $direction): void
    {
        $this->authorize('viewInternalPlanning', $this->event);

        $target = $index + $direction;

        if (! isset($this->steps[$index], $this->steps[$target])) {
            return;
        }

        [$this->steps[$index], $this->steps[$target]] = [$this->steps[$target], $this->steps[$index]];
    }

    public function save(): void
    {
        $event = $this->event;
        $this->authorize('viewInternalPlanning', $event);

        $this->validate([
            'objectives' => ['nullable', 'string', 'max:4000'],
            'sop' => ['nullable', 'string', 'max:8000'],
            'flowNotes' => ['nullable', 'string', 'max:4000'],
            'steps' => ['array', 'max:60'],
            'steps.*.time_label' => ['nullable', 'string', 'max:40'],
            'steps.*.activity' => ['nullable', 'string', 'max:200'],
            'steps.*.responsible' => ['nullable', 'string', 'max:120'],
        ], [], [
            'objectives' => 'objektif',
            'sop' => 'SOP',
            'flowNotes' => 'nota aliran',
            'steps.*.activity' => 'aktiviti',
        ]);

        $event->update([
            'objectives' => $this->objectives ?: null,
            'sop' => $this->sop ?: null,
            'flow_notes' => $this->flowNotes ?: null,
        ]);

        // Baris kosong sengaja diabaikan supaya staf boleh biarkan
        // baris separuh siap tanpa ia menjadi sampah.
        $kept = [];

        foreach (array_values($this->steps) as $order => $row) {
            if (blank($row['activity'] ?? null)) {
                continue;
            }

            $step = EventFlowStep::updateOrCreate(
                ['id' => $row['id'] ?? null, 'event_id' => $event->id],
                [
                    'sort_order' => $order,
                    'time_label' => $row['time_label'] ?: null,
                    'activity' => $row['activity'],
                    'responsible' => $row['responsible'] ?: null,
                ],
            );

            $kept[] = $step->id;
        }

        $event->flowSteps()->whereNotIn('id', $kept ?: [0])->delete();

        ActivityLogger::log('event.planning_updated', $event,
            "Perancangan dalaman dikemas kini untuk {$event->title}.");

        $this->mount($event->fresh());
        $this->notify('Perancangan dalaman disimpan.');
    }

    public function render()
    {
        return view('livewire.admin.internal-planning');
    }
}
