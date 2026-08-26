<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venue extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'address', 'state_id', 'district_id', 'postcode', 'google_maps_url',
        'latitude', 'longitude', 'pic_name', 'pic_phone', 'parking_info',
    ];

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function mapsUrl(): string
    {
        if ($this->google_maps_url) {
            return $this->google_maps_url;
        }

        $query = urlencode(trim($this->name.' '.$this->address));

        return "https://www.google.com/maps/search/?api=1&query={$query}";
    }
}
