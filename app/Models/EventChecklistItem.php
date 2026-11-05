<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/** Item SOP bagi satu program (disalin daripada blueprint atau ditambah sendiri). */
class EventChecklistItem extends Model
{
    protected $fillable = [
        'event_id', 'blueprint_item_id', 'phase', 'title', 'details',
        'due_offset_days', 'sort_order', 'completed_at', 'completed_by',
    ];

    protected function casts(): array
    {
        return ['completed_at' => 'datetime', 'due_offset_days' => 'integer'];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function isDone(): bool
    {
        return $this->completed_at !== null;
    }

    public function dueDate(?Carbon $startsAt): ?Carbon
    {
        if ($this->due_offset_days === null || ! $startsAt) {
            return null;
        }

        return $startsAt->copy()->startOfDay()->addDays($this->due_offset_days);
    }
}
