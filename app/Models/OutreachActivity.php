<?php

namespace App\Models;

use App\Enums\OutreachActivityType;
use App\Enums\OutreachStage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutreachActivity extends Model
{
    protected $fillable = [
        'outreach_target_id', 'user_id', 'type', 'body',
        'outcome', 'from_stage', 'to_stage', 'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => OutreachActivityType::class,
            'from_stage' => OutreachStage::class,
            'to_stage' => OutreachStage::class,
            'occurred_at' => 'datetime',
        ];
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(OutreachTarget::class, 'outreach_target_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isStageChange(): bool
    {
        return $this->type === OutreachActivityType::Peringkat;
    }
}
