<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\NotifiesUser;
use App\Models\Event;
use App\Models\EventFlowStep;
use App\Models\EventTeamMember;
use App\Models\Package;
use App\Services\ActivityLogger;
use App\Support\Phone;
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

    /** Pasukan bertugas: peranan, nama, nombor. */
    public array $team = [];

    /** Pakej yang dipilih: id => ['target' => ?int, 'focus' => bool]. */
    public array $packages = [];

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

        $this->team = $event->teamMembers()->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'role' => (string) $m->role,
                'name' => (string) $m->name,
                'phone' => (string) $m->phone,
            ])->all();

        if ($this->team === []) {
            $this->addMember();
        }

        $this->packages = $event->packages()->get()
            ->mapWithKeys(fn ($p) => [$p->id => [
                'target' => $p->pivot->target_units,
                'focus' => (bool) $p->pivot->is_focus,
            ]])->all();
    }

    public function addMember(): void
    {
        $this->authorize('viewInternalPlanning', $this->event);

        $this->team[] = ['id' => null, 'role' => '', 'name' => '', 'phone' => ''];
    }

    public function removeMember(int $index): void
    {
        $this->authorize('viewInternalPlanning', $this->event);

        unset($this->team[$index]);
        $this->team = array_values($this->team);

        if ($this->team === []) {
            $this->addMember();
        }
    }

    /** Pilih atau buang satu pakej daripada program ini. */
    public function togglePackage(int $packageId): void
    {
        $this->authorize('viewInternalPlanning', $this->event);

        if (array_key_exists($packageId, $this->packages)) {
            unset($this->packages[$packageId]);

            return;
        }

        $this->packages[$packageId] = ['target' => null, 'focus' => false];
    }

    /** Tandakan satu pakej sebagai tumpuan utama jualan. */
    public function setFocus(int $packageId): void
    {
        $this->authorize('viewInternalPlanning', $this->event);

        if (! array_key_exists($packageId, $this->packages)) {
            return;
        }

        $sudah = $this->packages[$packageId]['focus'] ?? false;

        foreach ($this->packages as $id => $row) {
            $this->packages[$id]['focus'] = false;
        }

        $this->packages[$packageId]['focus'] = ! $sudah;
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
            'team' => ['array', 'max:40'],
            'team.*.role' => ['nullable', 'string', 'max:100'],
            'team.*.name' => ['nullable', 'string', 'max:140'],
            'team.*.phone' => ['nullable', 'string', 'max:20'],
            'packages' => ['array'],
            'packages.*.target' => ['nullable', 'integer', 'min:0', 'max:100000'],
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

        // Pasukan bertugas — baris tanpa nama diabaikan.
        $keptTeam = [];

        foreach (array_values($this->team) as $order => $row) {
            if (blank($row['name'] ?? null)) {
                continue;
            }

            $member = EventTeamMember::updateOrCreate(
                ['id' => $row['id'] ?? null, 'event_id' => $event->id],
                [
                    'sort_order' => $order,
                    'role' => $row['role'] ?: 'Ahli pasukan',
                    'name' => $row['name'],
                    'phone' => Phone::normalise($row['phone'] ?? null),
                ],
            );

            $keptTeam[] = $member->id;
        }

        $event->teamMembers()->whereNotIn('id', $keptTeam ?: [0])->delete();

        // Pakej jualan.
        $event->packages()->sync(
            collect($this->packages)->mapWithKeys(fn ($row, $id) => [$id => [
                'target_units' => $row['target'] !== null && $row['target'] !== ''
                    ? (int) $row['target'] : null,
                'is_focus' => (bool) ($row['focus'] ?? false),
            ]])->all()
        );

        ActivityLogger::log('event.planning_updated', $event,
            "Perancangan dalaman dikemas kini untuk {$event->title}.");

        $this->mount($event->fresh());
        $this->notify('Perancangan dalaman disimpan.');
    }

    public function render()
    {
        return view('livewire.admin.internal-planning', [
            'katalog' => Package::active()->ordered()->get(),
        ]);
    }
}
