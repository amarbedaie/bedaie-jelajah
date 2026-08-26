<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partner extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'type', 'logo_path', 'website_url', 'tier',
        'description', 'is_featured', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['is_featured' => 'boolean', 'is_active' => 'boolean'];
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public static function types(): array
    {
        return ['rakan' => 'Rakan Kerjasama', 'penaja' => 'Penaja'];
    }

    public function logoUrl(): ?string
    {
        return $this->logo_path ? asset('storage/'.$this->logo_path) : null;
    }

    /** Susunan paparan yang ditetapkan admin. */
    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('sort_order')->orderBy('name');
    }
}
