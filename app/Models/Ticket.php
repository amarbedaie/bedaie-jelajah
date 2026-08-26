<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    protected $fillable = [
        'event_id', 'name', 'description', 'price', 'quantity', 'sold_count', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'is_active' => 'boolean'];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function remaining(): ?int
    {
        return $this->quantity > 0 ? max(0, $this->quantity - $this->sold_count) : null;
    }

    public function isSoldOut(): bool
    {
        $remaining = $this->remaining();

        return $remaining !== null && $remaining <= 0;
    }

    public function priceLabel(): string
    {
        return $this->price > 0 ? 'RM '.number_format((float) $this->price, 2) : 'Percuma';
    }
}
