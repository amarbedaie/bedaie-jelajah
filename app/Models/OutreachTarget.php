<?php

namespace App\Models;

use App\Enums\OutreachPriority;
use App\Enums\OutreachSource;
use App\Enums\OutreachStage;
use App\Enums\OutreachTargetType;
use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Satu lokasi yang dikejar oleh pasukan BeDaie — aliran KELUAR,
 * pelengkap kepada permohonan awam yang datang MASUK.
 */
class OutreachTarget extends Model
{
    use HasPublicId, SoftDeletes;

    protected $fillable = [
        'public_id', 'reference_no', 'name', 'type',
        'state_id', 'district_id', 'address', 'postcode', 'google_maps_url',
        'estimated_attendees',
        'contact_name', 'contact_role', 'contact_phone', 'contact_email',
        'contact_note', 'contact_found_at',
        'source', 'partner_id', 'referrer_user_id', 'referrer_name', 'referrer_phone',
        'assigned_to', 'stage', 'stage_changed_at', 'priority',
        'next_action_at', 'next_action_note', 'notes', 'closed_reason',
        'application_id', 'created_by', 'won_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => OutreachTargetType::class,
            'source' => OutreachSource::class,
            'stage' => OutreachStage::class,
            'priority' => OutreachPriority::class,
            'contact_found_at' => 'datetime',
            'stage_changed_at' => 'datetime',
            'next_action_at' => 'date',
            'won_at' => 'datetime',
        ];
    }

    // ── Relasi ───────────────────────────────────────────────

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_user_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(OutreachActivity::class)->latest('occurred_at');
    }

    // ── Skop ─────────────────────────────────────────────────

    public function scopeOpen(Builder $q): Builder
    {
        return $q->whereNotIn('stage', [
            OutreachStage::Berjaya->value,
            OutreachStage::TidakBerminat->value,
        ]);
    }

    public function scopeWon(Builder $q): Builder
    {
        return $q->where('stage', OutreachStage::Berjaya->value);
    }

    public function scopeAssignedTo(Builder $q, int $userId): Builder
    {
        return $q->where('assigned_to', $userId);
    }

    /** Sasaran yang tindakan susulannya sudah tiba atau terlepas. */
    public function scopeDueForAction(Builder $q): Builder
    {
        return $q->open()
            ->whereNotNull('next_action_at')
            ->whereDate('next_action_at', '<=', now());
    }

    // ── Bantuan paparan ──────────────────────────────────────

    public function locationLabel(): string
    {
        return collect([$this->district?->name, $this->state?->name])
            ->filter()->join(', ');
    }

    public function hasContact(): bool
    {
        return filled($this->contact_phone) || filled($this->contact_email);
    }

    public function isOverdue(): bool
    {
        return $this->next_action_at
            && $this->stage->isOpen()
            && $this->next_action_at->isPast();
    }

    /** Label ringkas untuk sumber, termasuk nama rakan jika ada. */
    public function sourceLabel(): string
    {
        return match (true) {
            $this->source === OutreachSource::Rakan && $this->partner
                => 'Rakan: '.$this->partner->name,
            $this->source === OutreachSource::Penggerak && $this->referrer
                => 'Penggerak: '.$this->referrer->name,
            $this->source === OutreachSource::Rujukan && filled($this->referrer_name)
                => 'Rujukan: '.$this->referrer_name,
            default => $this->source->label(),
        };
    }

    /** Nombor telefon kontak disamarkan untuk paparan senarai. */
    public function maskedContactPhone(): ?string
    {
        if (! $this->contact_phone) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $this->contact_phone);

        return mb_strlen($digits) > 6
            ? mb_substr($digits, 0, 3).str_repeat('•', mb_strlen($digits) - 6).mb_substr($digits, -3)
            : $digits;
    }

    public function whatsappUrl(?string $message = null): ?string
    {
        if (! $this->contact_phone) {
            return null;
        }

        $phone = preg_replace('/\D/', '', $this->contact_phone);

        return 'https://wa.me/'.$phone.($message ? '?text='.rawurlencode($message) : '');
    }
}
