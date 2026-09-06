<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

/**
 * Pakej jualan yang dibawa ke program — katalog boleh guna semula.
 *
 * Dokumen dalaman: staf sahaja. Peserta tidak pernah melihatnya.
 */
class Package extends Model
{
    protected $fillable = [
        'public_id', 'name', 'tagline', 'price', 'items', 'pitch', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    protected static function booted(): void
    {
        static::creating(function (self $package) {
            $package->public_id ??= (string) Str::uuid();
        });
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class)
            ->withPivot(['target_units', 'is_focus'])
            ->withTimestamps();
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('sort_order')->orderBy('name');
    }

    /** Barang dalam pakej, satu baris satu barang. */
    public function itemLines(): array
    {
        return collect(preg_split('/\r?\n/', (string) $this->items))
            ->map(fn ($l) => trim($l))
            ->filter()
            ->values()
            ->all();
    }

    public function priceLabel(): string
    {
        return $this->price > 0 ? 'RM '.number_format((float) $this->price, 2) : 'Percuma';
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }
}
