<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlueprintItem extends Model
{
    protected $fillable = ['blueprint_id', 'phase', 'title', 'details', 'due_offset_days', 'sort_order'];

    protected function casts(): array
    {
        return ['due_offset_days' => 'integer'];
    }

    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class);
    }

    /** Label masa relatif kepada hari program, cth. "14 hari sebelum". */
    public static function offsetLabel(?int $days): ?string
    {
        return match (true) {
            $days === null => null,
            $days === 0 => 'Hari program',
            $days < 0 => abs($days).' hari sebelum',
            default => $days.' hari selepas',
        };
    }
}
