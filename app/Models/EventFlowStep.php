<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu langkah dalam aliran acara — dokumen dalaman pasukan BeDaie.
 *
 * Tidak pernah didedahkan kepada peserta, orang awam atau Penggerak.
 */
class EventFlowStep extends Model
{
    protected $fillable = [
        'event_id', 'sort_order', 'time_label', 'activity', 'responsible', 'note',
    ];

    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
