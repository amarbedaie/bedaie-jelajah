<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasPublicId;

    protected $fillable = [
        'public_id', 'registration_id', 'event_id', 'gateway', 'gateway_reference',
        'gateway_payload', 'amount', 'currency', 'status', 'paid_at', 'verified_by',
        'receipt_path', 'note',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'gateway_payload' => 'array',
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function amountLabel(): string
    {
        return 'RM '.number_format((float) $this->amount, 2);
    }
}
