<?php

namespace App\Models;

use App\Enums\AttendanceMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    protected $fillable = [
        'registration_id', 'event_id', 'checked_in_at', 'checked_in_by',
        'method', 'guests_present', 'device_info', 'notes',
    ];

    protected function casts(): array
    {
        return ['checked_in_at' => 'datetime', 'method' => AttendanceMethod::class];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }
}
