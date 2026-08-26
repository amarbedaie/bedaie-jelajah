<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    protected $fillable = ['key', 'channel', 'name', 'subject', 'body', 'placeholders', 'is_active'];

    protected function casts(): array
    {
        return ['placeholders' => 'array', 'is_active' => 'boolean'];
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    /** Placeholder yang sentiasa tersedia untuk semua template. */
    public static function commonPlaceholders(): array
    {
        return [
            '{{participant_name}}', '{{mobilizer_name}}', '{{event_name}}', '{{event_date}}',
            '{{event_time}}', '{{venue}}', '{{speaker}}', '{{registration_link}}',
            '{{qr_link}}', '{{certificate_link}}', '{{reference_no}}', '{{status}}',
            '{{status_note}}', '{{support_phone}}', '{{brand}}',
        ];
    }

    public function render(array $data): array
    {
        $replace = [];
        foreach ($data as $key => $value) {
            $replace['{{'.$key.'}}'] = (string) $value;
        }

        return [
            'subject' => strtr((string) $this->subject, $replace),
            'body' => strtr($this->body, $replace),
        ];
    }
}
