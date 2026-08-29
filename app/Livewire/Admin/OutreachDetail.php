<?php

namespace App\Livewire\Admin;

use App\Enums\AttendeeEstimate;
use App\Enums\OutreachActivityType;
use App\Enums\OutreachPriority;
use App\Enums\OutreachStage;
use App\Enums\TargetAudience;
use App\Models\EventCategory;
use App\Models\OutreachTarget;
use App\Models\User;
use App\Services\OutreachService;
use App\Support\Phone;
use Livewire\Attributes\Locked;
use Livewire\Component;

/** Satu sasaran: garis masa, kontak, peringkat dan penukaran kepada permohonan. */
class OutreachDetail extends Component
{
    #[Locked]
    public int $targetId;

    // Log aktiviti
    public string $activityType = 'panggilan';

    public string $activityBody = '';

    public string $activityOutcome = '';

    // Peringkat
    public string $stage = '';

    public string $stageNote = '';

    // Kontak
    public bool $editingContact = false;

    public string $contact_name = '';

    public string $contact_role = '';

    public string $contact_phone = '';

    public string $contact_email = '';

    public string $contact_note = '';

    // Tugasan & susulan
    public string $assigned_to = '';

    public string $priority = '';

    public string $next_action_at = '';

    public string $next_action_note = '';

    // Penukaran kepada permohonan
    public bool $converting = false;

    public string $event_category_id = '';

    public string $topic = '';

    public string $preferred_date_1 = '';

    public string $preferred_date_2 = '';

    public string $estimated_attendees = '101_300';

    public string $target_audience = 'umum';

    public function mount(OutreachTarget $target): void
    {
        $this->targetId = $target->id;
        $this->fillFrom($target);
    }

    public function getTargetProperty(): OutreachTarget
    {
        return OutreachTarget::with([
            'state', 'district', 'assignee', 'partner', 'referrer',
            'application', 'activities.user', 'creator',
        ])->findOrFail($this->targetId);
    }

    private function fillFrom(OutreachTarget $target): void
    {
        $this->stage = $target->stage->value;
        $this->contact_name = (string) $target->contact_name;
        $this->contact_role = (string) $target->contact_role;
        $this->contact_phone = (string) $target->contact_phone;
        $this->contact_email = (string) $target->contact_email;
        $this->contact_note = (string) $target->contact_note;
        $this->assigned_to = (string) ($target->assigned_to ?? '');
        $this->priority = $target->priority->value;
        $this->next_action_at = $target->next_action_at?->toDateString() ?? '';
        $this->next_action_note = (string) $target->next_action_note;
        $this->topic = $this->topic ?: 'Program jelajah untuk komuniti '.$target->name.'.';
    }

    // ── Aktiviti ─────────────────────────────────────────────

    public function logActivity(OutreachService $outreach): void
    {
        $this->validate([
            'activityType' => ['required', 'string'],
            'activityBody' => ['required', 'string', 'min:3', 'max:2000'],
            'activityOutcome' => ['nullable', 'string', 'max:200'],
        ], [], [
            'activityType' => 'jenis aktiviti',
            'activityBody' => 'catatan',
            'activityOutcome' => 'hasil',
        ]);

        $outreach->log(
            $this->target,
            OutreachActivityType::from($this->activityType),
            $this->activityBody,
            $this->activityOutcome ?: null,
            auth()->user(),
        );

        $this->reset('activityBody', 'activityOutcome');
        session()->flash('success', 'Aktiviti direkodkan.');
    }

    // ── Peringkat ────────────────────────────────────────────

    public function changeStage(OutreachService $outreach): void
    {
        $this->validate([
            'stage' => ['required', 'string'],
            'stageNote' => ['nullable', 'string', 'max:500'],
        ], [], ['stage' => 'peringkat', 'stageNote' => 'nota']);

        $next = OutreachStage::from($this->stage);

        if ($next === OutreachStage::TidakBerminat && $this->stageNote === '') {
            $this->addError('stageNote', 'Sila nyatakan sebab sasaran ini ditutup.');

            return;
        }

        $next === OutreachStage::TidakBerminat
            ? $outreach->close($this->target, $this->stageNote, auth()->user())
            : $outreach->moveStage($this->target, $next, $this->stageNote ?: null, auth()->user());

        $this->reset('stageNote');
        session()->flash('success', 'Peringkat dikemas kini.');
    }

    // ── Kontak ───────────────────────────────────────────────

    public function saveContact(OutreachService $outreach): void
    {
        $data = $this->validate([
            'contact_name' => ['nullable', 'string', 'max:150'],
            'contact_role' => ['nullable', 'string', 'max:120'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'contact_email' => ['nullable', 'email', 'max:180'],
            'contact_note' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'contact_name' => 'nama kontak', 'contact_role' => 'jawatan',
            'contact_phone' => 'telefon', 'contact_email' => 'e-mel',
            'contact_note' => 'nota kontak',
        ]);

        $outreach->recordContact($this->target, [
            'contact_name' => $data['contact_name'] ?: null,
            'contact_role' => $data['contact_role'] ?: null,
            'contact_phone' => $data['contact_phone'] ? Phone::normalise($data['contact_phone']) : null,
            'contact_email' => $data['contact_email'] ?: null,
            'contact_note' => $data['contact_note'] ?: null,
        ], auth()->user());

        $this->editingContact = false;
        $this->stage = $this->target->fresh()->stage->value;
        session()->flash('success', 'Kontak dikemas kini.');
    }

    // ── Tugasan ──────────────────────────────────────────────

    public function saveAssignment(): void
    {
        $data = $this->validate([
            'assigned_to' => ['nullable', 'exists:users,id'],
            'priority' => ['required', 'string'],
            'next_action_at' => ['nullable', 'date'],
            'next_action_note' => ['nullable', 'string', 'max:200'],
        ], [], [
            'assigned_to' => 'staf', 'priority' => 'keutamaan',
            'next_action_at' => 'tarikh tindakan', 'next_action_note' => 'nota tindakan',
        ]);

        $this->target->update([
            'assigned_to' => $data['assigned_to'] ?: null,
            'priority' => OutreachPriority::from($data['priority']),
            'next_action_at' => $data['next_action_at'] ?: null,
            'next_action_note' => $data['next_action_note'] ?: null,
        ]);

        session()->flash('success', 'Tugasan dikemas kini.');
    }

    // ── Penukaran ────────────────────────────────────────────

    public function startConvert(): void
    {
        $this->converting = true;
        $this->event_category_id = (string) EventCategory::active()->value('id');
        $this->preferred_date_1 = now()->addDays(45)->toDateString();
        $this->resetValidation();
    }

    public function convert(OutreachService $outreach)
    {
        $data = $this->validate([
            'event_category_id' => ['required', 'exists:event_categories,id'],
            'topic' => ['required', 'string', 'min:10', 'max:1000'],
            'preferred_date_1' => ['required', 'date', 'after:today'],
            'preferred_date_2' => ['nullable', 'date', 'after:today'],
            'estimated_attendees' => ['required', 'string'],
            'target_audience' => ['required', 'string'],
        ], [], [
            'event_category_id' => 'jenis program', 'topic' => 'topik',
            'preferred_date_1' => 'cadangan tarikh pertama',
            'preferred_date_2' => 'cadangan tarikh kedua',
            'estimated_attendees' => 'anggaran peserta', 'target_audience' => 'sasaran peserta',
        ]);

        $target = $this->target;

        if (! $target->contact_phone) {
            $this->addError('topic', 'Rekod nombor telefon kontak dahulu sebelum menukar kepada permohonan.');

            return null;
        }

        $application = $outreach->convertToApplication($target, [
            'event_category_id' => (int) $data['event_category_id'],
            'topic' => $data['topic'],
            'preferred_date_1' => $data['preferred_date_1'],
            'preferred_date_2' => $data['preferred_date_2'] ?: null,
            'estimated_attendees' => AttendeeEstimate::from($data['estimated_attendees']),
            'target_audience' => TargetAudience::from($data['target_audience']),
        ], auth()->user());

        session()->flash('success', "Permohonan {$application->reference_no} dijana daripada sasaran ini.");

        return redirect()->route('admin.permohonan.show', $application);
    }

    public function delete()
    {
        $target = $this->target;
        $name = $target->name;
        $target->delete();

        session()->flash('success', "Sasaran \"{$name}\" dibuang daripada papan.");

        return redirect()->route('admin.sasaran');
    }

    public function render()
    {
        return view('livewire.admin.outreach-detail', [
            'target' => $this->target,
            'stages' => OutreachStage::cases(),
            'activityTypes' => OutreachActivityType::manual(),
            'staff' => User::where('role', 'admin')->orderBy('name')->get(),
            'priorities' => OutreachPriority::cases(),
            'categories' => EventCategory::active()->ordered()->get(),
            'estimates' => AttendeeEstimate::cases(),
            'audiences' => TargetAudience::cases(),
        ]);
    }
}
