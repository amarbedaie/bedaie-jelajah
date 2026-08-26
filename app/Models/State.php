<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class State extends Model
{
    protected $fillable = [
        'name', 'slug', 'code', 'region', 'map_path', 'label_x', 'label_y', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['label_x' => 'float', 'label_y' => 'float'];
    }

    public function districts(): HasMany
    {
        return $this->hasMany(District::class)->orderBy('name');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function areaInterestRequests(): HasMany
    {
        return $this->hasMany(AreaInterestRequest::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
