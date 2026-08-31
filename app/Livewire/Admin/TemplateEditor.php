<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\NotifiesUser;
use App\Models\NotificationTemplate;
use App\Services\ActivityLogger;
use Livewire\Attributes\Locked;
use Livewire\Component;

/** Menyunting satu template notifikasi (satu pencetus, satu saluran). */
class TemplateEditor extends Component
{
    use NotifiesUser;

    #[Locked]
    public int $templateId;

    public bool $open = false;

    public string $subject = '';

    public string $body = '';

    public bool $is_active = true;

    /** Data paparan, disalin sekali supaya render tidak menyentuh DB. */
    public string $channel = '';

    public array $placeholders = [];

    public function mount(NotificationTemplate $template): void
    {
        $this->templateId = $template->id;
        $this->fillFrom($template);
    }

    /**
     * Model diambil hanya apabila satu tindakan berlaku.
     *
     * Halaman ini memaparkan puluhan templat, setiap satu satu komponen.
     * Jika render menyentuh model, itu satu pertanyaan setiap baris.
     */
    public function getTemplateProperty(): NotificationTemplate
    {
        return NotificationTemplate::findOrFail($this->templateId);
    }

    private function fillFrom(NotificationTemplate $template): void
    {
        $this->subject = (string) $template->subject;
        $this->body = (string) $template->body;
        $this->is_active = (bool) $template->is_active;
        $this->channel = (string) $template->channel;
        $this->placeholders = (array) ($template->placeholders ?? []);
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;

        if ($this->open) {
            $this->fillFrom($this->template);
            $this->resetValidation();
        }
    }

    public function save(): void
    {
        $data = $this->validate([
            'subject' => ['nullable', 'string', 'max:200'],
            'body' => ['required', 'string', 'min:10', 'max:5000'],
            'is_active' => ['boolean'],
        ], [], ['subject' => 'tajuk', 'body' => 'kandungan']);

        $template = $this->template;

        $template->update([
            'subject' => $data['subject'] ?: null,
            'body' => $data['body'],
            'is_active' => $data['is_active'],
        ]);

        ActivityLogger::log('notification_template.updated', $template,
            "Template {$template->key} ({$template->channel}) dikemas kini.");

        $this->open = false;
        $this->notify('Template notifikasi disimpan.', 'success');
    }

    public function toggleActive(): void
    {
        $template = $this->template;
        $template->update(['is_active' => ! $template->is_active]);
        $this->is_active = (bool) $template->fresh()->is_active;
    }

    public function render()
    {
        return view('livewire.admin.template-editor');
    }
}
