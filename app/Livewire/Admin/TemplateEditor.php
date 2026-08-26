<?php

namespace App\Livewire\Admin;

use App\Models\NotificationTemplate;
use App\Services\ActivityLogger;
use Livewire\Attributes\Locked;
use Livewire\Component;

/** Menyunting satu template notifikasi (satu pencetus, satu saluran). */
class TemplateEditor extends Component
{
    #[Locked]
    public int $templateId;

    public bool $open = false;

    public string $subject = '';

    public string $body = '';

    public bool $is_active = true;

    public function mount(NotificationTemplate $template): void
    {
        $this->templateId = $template->id;
        $this->fillFrom($template);
    }

    public function getTemplateProperty(): NotificationTemplate
    {
        return NotificationTemplate::findOrFail($this->templateId);
    }

    private function fillFrom(NotificationTemplate $template): void
    {
        $this->subject = (string) $template->subject;
        $this->body = (string) $template->body;
        $this->is_active = (bool) $template->is_active;
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
        session()->flash('success', 'Template notifikasi disimpan.');
    }

    public function toggleActive(): void
    {
        $template = $this->template;
        $template->update(['is_active' => ! $template->is_active]);
        $this->is_active = (bool) $template->fresh()->is_active;
    }

    public function render()
    {
        return view('livewire.admin.template-editor', [
            'template' => $this->template,
        ]);
    }
}
