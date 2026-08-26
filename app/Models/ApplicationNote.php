<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationNote extends Model
{
    protected $fillable = [
        'application_id', 'user_id', 'body', 'is_internal', 'channel', 'occurred_at',
    ];

    protected function casts(): array
    {
        return ['is_internal' => 'boolean', 'occurred_at' => 'datetime'];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeShareable(Builder $q): Builder
    {
        return $q->where('is_internal', false);
    }

    public static function channels(): array
    {
        return [
            'nota' => 'Nota dalaman',
            'panggilan' => 'Panggilan telefon',
            'whatsapp' => 'WhatsApp',
            'emel' => 'Emel',
            'mesyuarat' => 'Mesyuarat / lawatan',
        ];
    }
}
