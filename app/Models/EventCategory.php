<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'icon', 'tagline', 'description', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function areaInterestRequests(): HasMany
    {
        return $this->hasMany(AreaInterestRequest::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Susunan paparan yang ditetapkan admin. */
    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('sort_order')->orderBy('name');
    }
}
