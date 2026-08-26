<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AreaInterestRequest extends Model
{
    protected $fillable = [
        'name', 'phone', 'state_id', 'district_id', 'postcode',
        'event_category_id', 'notes', 'status', 'ip_address',
    ];

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class, 'event_category_id');
    }

    public static function statuses(): array
    {
        return [
            'baharu' => 'Baharu',
            'disemak' => 'Telah Disemak',
            'dijadual' => 'Dijadualkan',
            'ditutup' => 'Ditutup',
        ];
    }
}
