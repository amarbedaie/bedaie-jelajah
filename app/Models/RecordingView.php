<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecordingView extends Model
{
    protected $fillable = ['event_recording_id', 'registration_id', 'user_id', 'viewed_at'];

    protected function casts(): array
    {
        return ['viewed_at' => 'datetime'];
    }

    public function recording(): BelongsTo
    {
        return $this->belongsTo(EventRecording::class, 'event_recording_id');
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }
}
