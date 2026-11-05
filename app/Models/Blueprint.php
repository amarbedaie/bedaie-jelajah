<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/** Templat SOP program mengikut jenis lokasi. */
class Blueprint extends Model
{
    public const PHASES = [
        'sebelum' => 'Sebelum Program',
        'semasa' => 'Semasa Program',
        'selepas' => 'Selepas Program',
    ];

    public const VENUE_TYPES = [
        'masjid' => 'Masjid / Surau',
        'sekolah' => 'Sekolah / Tahfiz',
        'institusi' => 'Institusi / Kolej / NGO',
    ];

    protected $fillable = ['name', 'slug', 'venue_type', 'description', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function items(): HasMany
    {
        return $this->hasMany(BlueprintItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('sort_order')->orderBy('name');
    }

    /** Cadangan blueprint berdasarkan nama lokasi / kategori program. */
    public static function suggestFor(Event $event): ?self
    {
        $haystack = Str::lower(implode(' ', array_filter([
            $event->venue?->name, $event->title, $event->category?->name, $event->organizer_name,
        ])));

        $type = match (true) {
            Str::contains($haystack, ['masjid', 'surau', 'musolla', 'musalla']) => 'masjid',
            Str::contains($haystack, ['sekolah', 'tahfiz', 'maahad', 'smk', 'sk ', 'pondok']) => 'sekolah',
            default => 'institusi',
        };

        return static::active()->where('venue_type', $type)->ordered()->first()
            ?? static::active()->ordered()->first();
    }
}
