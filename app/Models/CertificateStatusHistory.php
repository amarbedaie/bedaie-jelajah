<?php

namespace App\Models;

use App\Enums\CertificateStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificateStatusHistory extends Model
{
    protected $fillable = ['certificate_id', 'from_status', 'to_status', 'user_id', 'reason'];

    protected function casts(): array
    {
        return [
            'from_status' => CertificateStatus::class,
            'to_status' => CertificateStatus::class,
        ];
    }

    public function certificate(): BelongsTo
    {
        return $this->belongsTo(Certificate::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
