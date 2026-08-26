<?php

namespace App\Models;

use App\Enums\CertificateStatus;
use App\Enums\CertificateType;
use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Certificate extends Model
{
    use HasPublicId, SoftDeletes;

    protected $fillable = [
        'public_id', 'certificate_number', 'verification_token', 'type', 'event_id',
        'registration_id', 'user_id', 'certificate_template_id', 'recipient_name',
        'recipient_email', 'recipient_phone', 'organization_name', 'event_title',
        'speaker_name', 'venue_name', 'event_date', 'learning_hours', 'status',
        'superseded_by_id', 'revoke_reason', 'pdf_path', 'issued_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => CertificateType::class,
            'status' => CertificateStatus::class,
            'event_date' => 'date',
            'issued_at' => 'datetime',
            'learning_hours' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Certificate $certificate) {
            if (empty($certificate->verification_token)) {
                $certificate->verification_token = Str::random(40);
            }
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CertificateTemplate::class, 'certificate_template_id');
    }

    public function supersededBy(): BelongsTo
    {
        return $this->belongsTo(Certificate::class, 'superseded_by_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(CertificateStatusHistory::class)->latest();
    }

    public function scopeValid(Builder $q): Builder
    {
        return $q->where('status', CertificateStatus::Dikeluarkan->value);
    }

    public function isValid(): bool
    {
        return $this->status->isValid();
    }

    public function verificationUrl(): string
    {
        return route('sijil.semak.show', $this->certificate_number);
    }

    public function downloadUrl(): string
    {
        return route('sijil.muat-turun', $this->public_id);
    }
}
