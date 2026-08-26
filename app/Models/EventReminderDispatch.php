<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventReminderDispatch extends Model
{
    protected $fillable = ['event_id', 'registration_id', 'reminder_key', 'dispatched_at'];

    protected function casts(): array
    {
        return ['dispatched_at' => 'datetime'];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }
}
