<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NotificationLog extends Model
{
    protected $fillable = [
        'template_key', 'channel', 'notifiable_type', 'notifiable_id', 'recipient_name',
        'recipient_address', 'subject', 'body', 'status', 'error', 'sent_at',
    ];

    protected function casts(): array
    {
        return ['sent_at' => 'datetime'];
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }
}
