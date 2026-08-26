<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    protected $table = 'feedback';

    protected $fillable = [
        'event_id', 'registration_id', 'rating', 'most_beneficial',
        'next_topic', 'wants_advanced', 'comments', 'is_published',
    ];

    protected function casts(): array
    {
        return ['wants_advanced' => 'boolean', 'is_published' => 'boolean'];
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
