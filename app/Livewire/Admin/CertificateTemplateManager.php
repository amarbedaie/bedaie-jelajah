<?php

namespace App\Livewire\Admin;

use App\Enums\CertificateDesign;
use App\Enums\CertificateType;
use App\Livewire\Concerns\NotifiesUser;
use App\Models\CertificateTemplate;
use App\Services\ActivityLogger;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * Urus templat sijil: gaya reka bentuk, orientasi A4, warna dan teks.
 * Templat lama tidak dipadam — hanya boleh dinyahaktifkan.
 */
class CertificateTemplateManager extends Component
{
    use NotifiesUser;

    public ?int $editingId = null;

    public bool $showForm = false;

    public string $name = '';

    public string $type = 'penyertaan';

    public string $design = 'klasik';

    public string $orientation = 'landscape';

    public string $accent_color = '#7A3BB8';

    public string $intro_text = '';

    public string $closing_text = '';

    public string $signature_name = '';

    public string $signature_title = '';

    public bool $is_default = false;

    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'type' => ['required', Rule::enum(CertificateType::class)],
            'design' => ['required', Rule::enum(CertificateDesign::class)],
            'orientation' => ['required', Rule::in(['landscape', 'portrait'])],
            'accent_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'intro_text' => ['nullable', 'string', 'max:300'],
            'closing_text' => ['nullable', 'string', 'max:300'],
            'signature_name' => ['nullable', 'string', 'max:120'],
            'signature_title' => ['nullable', 'string', 'max:120'],
            'is_default' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'nama templat', 'type' => 'jenis sijil', 'design' => 'reka bentuk',
            'orientation' => 'orientasi', 'accent_color' => 'warna utama',
            'intro_text' => 'teks pembuka', 'closing_text' => 'teks penutup',
            'signature_name' => 'nama penandatangan', 'signature_title' => 'jawatan penandatangan',
        ];
    }

    public function create(): void
    {
        $this->resetForm();
        $this->signature_name = 'Pengarah BeDaie Jelajah';
        $this->signature_title = (string) config('jelajah.org');
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $t = CertificateTemplate::findOrFail($id);

        $this->editingId = $t->id;
        $this->name = $t->name;
        $this->type = $t->type->value;
        $this->design = $t->design ?: CertificateDesign::Klasik->value;
        $this->orientation = $t->orientation ?: 'landscape';
        $this->accent_color = $t->accent_color ?: '#7A3BB8';
        $this->intro_text = (string) $t->intro_text;
        $this->closing_text = (string) $t->closing_text;
        $this->signature_name = (string) $t->signature_name;
        $this->signature_title = (string) $t->signature_title;
        $this->is_default = (bool) $t->is_default;
        $this->is_active = (bool) $t->is_active;
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        $template = $this->editingId ? CertificateTemplate::findOrFail($this->editingId) : new CertificateTemplate;

        if (! $template->exists) {
            $slug = Str::slug($data['name']);
            $template->slug = CertificateTemplate::withTrashed()->where('slug', $slug)->exists()
                ? $slug.'-'.Str::lower(Str::random(4)) : $slug;
        }

        $template->fill([
            'name' => $data['name'],
            'type' => $data['type'],
            'design' => $data['design'],
            'orientation' => $data['orientation'],
            'accent_color' => $data['accent_color'],
            'intro_text' => $data['intro_text'] ?: null,
            'closing_text' => $data['closing_text'] ?: null,
            'signature_name' => $data['signature_name'] ?: null,
            'signature_title' => $data['signature_title'] ?: null,
            'is_default' => $data['is_default'],
            'is_active' => $data['is_active'],
        ])->save();

        ActivityLogger::log($this->editingId ? 'certificate_template.updated' : 'certificate_template.created',
            $template, "Templat sijil {$template->name} disimpan.");

        $this->resetForm();
        $this->notify('Templat sijil disimpan. Sijil sedia ada akan dijana semula dengan reka bentuk baharu.', 'success');
    }

    public function toggleActive(int $id): void
    {
        $t = CertificateTemplate::findOrFail($id);
        $t->update(['is_active' => ! $t->is_active]);
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'showForm', 'name', 'type', 'design', 'orientation', 'accent_color',
            'intro_text', 'closing_text', 'signature_name', 'signature_title', 'is_default', 'is_active']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.certificate-template-manager', [
            'templates' => CertificateTemplate::orderBy('type')->orderByDesc('is_default')->orderBy('name')->get(),
            'types' => CertificateType::cases(),
            'designs' => CertificateDesign::cases(),
            'orientations' => CertificateDesign::orientations(),
        ]);
    }
}
