<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\NotifiesUser;
use App\Models\Setting;
use App\Services\ActivityLogger;
use Livewire\Component;

/**
 * Menyunting tetapan sistem yang disimpan dalam pangkalan data.
 * Kredensial (kunci API, rahsia gateway) sengaja TIDAK diuruskan di sini —
 * ia kekal dalam fail .env.
 */
class SettingsEditor extends Component
{
    use NotifiesUser;

    /** @var array<string, string> */
    public array $values = [];

    public string $group = '';

    public function mount(string $group = ''): void
    {
        $this->group = $group;
        $this->loadValues();
    }

    /**
     * Kunci tetapan mengandungi titik (cth. legal.privacy) yang akan ditafsir
     * oleh Livewire sebagai laluan array bersarang. Kami bind mengikut ID
     * rekod supaya nilai kekal rata dan selamat.
     */
    private function loadValues(): void
    {
        $this->values = $this->query()->get()
            ->mapWithKeys(fn (Setting $s) => [(string) $s->id => (string) $s->value])
            ->all();
    }

    private function query()
    {
        return Setting::query()
            ->when($this->group !== '', fn ($q) => $q->where('group', $this->group))
            ->orderBy('group')->orderBy('key');
    }

    public function save(): void
    {
        $settings = $this->query()->get();

        $rules = [];
        $labels = [];

        foreach ($settings as $setting) {
            $rules["values.{$setting->id}"] = match ($setting->type) {
                'url' => ['nullable', 'url', 'max:500'],
                'email' => ['nullable', 'email', 'max:180'],
                'number' => ['nullable', 'numeric'],
                'longtext' => ['nullable', 'string', 'max:20000'],
                default => ['nullable', 'string', 'max:1000'],
            };

            $labels["values.{$setting->id}"] = $setting->label ?: $setting->key;
        }

        $this->validate($rules, [], $labels);

        $changed = 0;

        foreach ($settings as $setting) {
            $new = $this->values[$setting->id] ?? '';

            if ((string) $setting->value === $new) {
                continue;
            }

            $setting->update(['value' => $new !== '' ? $new : null]);
            $changed++;
        }

        if ($changed > 0) {
            ActivityLogger::log('settings.updated', null,
                "{$changed} tetapan dikemas kini oleh ".auth()->user()->name.'.');
        }

        $this->notify($changed > 0
            ? "{$changed} tetapan dikemas kini."
            : 'Tiada perubahan untuk disimpan.', 'success');
    }

    public function render()
    {
        return view('livewire.admin.settings-editor', [
            'groups' => $this->query()->get()->groupBy('group'),
        ]);
    }
}
