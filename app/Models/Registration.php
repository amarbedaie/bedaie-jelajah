<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\RegistrationStatus;
use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Registration extends Model
{
    use HasPublicId, SoftDeletes;

    protected $fillable = [
        'public_id', 'public_token', 'reference_no', 'event_id', 'user_id', 'ticket_id',
        'name', 'phone', 'email', 'gender', 'state_id', 'district_id', 'guests_count',
        'status', 'source', 'invite_code', 'notes', 'privacy_consent_at',
        'registered_at', 'confirmed_at', 'cancelled_at', 'cancel_reason', 'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'status' => RegistrationStatus::class,
            'privacy_consent_at' => 'datetime',
            'registered_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Registration $registration) {
            if (empty($registration->public_token)) {
                $registration->public_token = Str::random(48);
            }
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function guests(): HasMany
    {
        return $this->hasMany(RegistrationGuest::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function attendance(): HasOne
    {
        return $this->hasOne(AttendanceRecord::class);
    }

    /**
     * Sijil semasa. Apabila sijil dijana semula (pembetulan nama), sijil lama
     * kekal sebagai rekod audit — jadi kita sentiasa pulangkan yang terkini.
     */
    public function certificate(): HasOne
    {
        return $this->hasOne(Certificate::class)->latestOfMany();
    }

    /** Semua sijil termasuk yang telah digantikan atau dibatalkan. */
    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class)->latest('id');
    }

    public function feedback(): HasOne
    {
        return $this->hasOne(Feedback::class);
    }

    public function qrTokens(): MorphMany
    {
        return $this->morphMany(QrToken::class, 'tokenable');
    }

    public function activeQrToken(): ?QrToken
    {
        return $this->qrTokens()->whereNull('revoked_at')->latest()->first();
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopeActive(Builder $q): Builder
    {
        return $q->whereIn('status', [
            RegistrationStatus::Disahkan->value,
            RegistrationStatus::MenungguPengesahan->value,
        ]);
    }

    public function scopeConfirmed(Builder $q): Builder
    {
        return $q->where('status', RegistrationStatus::Disahkan->value);
    }

    public function scopeWaitlisted(Builder $q): Builder
    {
        return $q->where('status', RegistrationStatus::SenaraiMenunggu->value);
    }

    public function scopeAttended(Builder $q): Builder
    {
        return $q->whereHas('attendance');
    }

    // ── Helpers ──────────────────────────────────────────────────────

    public function seats(): int
    {
        return 1 + (int) $this->guests_count;
    }

    public function hasAttended(): bool
    {
        return $this->attendance()->exists();
    }

    public function isPaid(): bool
    {
        return (bool) $this->payment?->status->isSettled();
    }

    public function paymentStatus(): PaymentStatus
    {
        return $this->payment?->status ?? PaymentStatus::Dikecualikan;
    }

    public function ticketUrl(): string
    {
        return route('tiket.show', $this->public_token);
    }

    public function cancelUrl(): string
    {
        return route('tiket.cancel', $this->public_token);
    }

    /** Nombor telefon disamarkan untuk paparan yang tidak memerlukan nombor penuh. */
    public function maskedPhone(): string
    {
        $digits = preg_replace('/\D/', '', (string) $this->phone);

        if (strlen($digits) < 6) {
            return str_repeat('•', max(0, strlen($digits)));
        }

        return substr($digits, 0, 3).str_repeat('•', max(0, strlen($digits) - 6)).substr($digits, -3);
    }

    public function maskedEmail(): ?string
    {
        if (! $this->email) {
            return null;
        }

        [$local, $domain] = array_pad(explode('@', $this->email, 2), 2, '');
        $visible = mb_substr($local, 0, min(2, mb_strlen($local)));

        return $visible.str_repeat('•', max(1, mb_strlen($local) - 2)).'@'.$domain;
    }

    /** Nama pertama untuk sapaan mesra. */
    public function firstName(): string
    {
        return explode(' ', trim($this->name))[0] ?: $this->name;
    }
}
