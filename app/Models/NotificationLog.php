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

    /** Alamat penerima disamarkan — log tidak perlu mendedahkan nombor penuh. */
    public function maskedAddress(): string
    {
        $value = (string) $this->recipient_address;

        if ($value === '') {
            return '—';
        }

        if (str_contains($value, '@')) {
            [$user, $domain] = explode('@', $value, 2);

            return mb_substr($user, 0, 2).str_repeat('•', max(1, mb_strlen($user) - 2)).'@'.$domain;
        }

        $digits = preg_replace('/\D/', '', $value);

        return mb_strlen($digits) > 6
            ? mb_substr($digits, 0, 3).str_repeat('•', mb_strlen($digits) - 6).mb_substr($digits, -3)
            : $digits;
    }

    /** Petikan ringkas kandungan mesej untuk paparan senarai. */
    public function preview(): string
    {
        return \Illuminate\Support\Str::limit(trim((string) $this->body), 160);
    }
}
