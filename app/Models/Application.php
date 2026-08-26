<?php

namespace App\Models;

use App\Enums\ApplicantBackground;
use App\Enums\ApplicationStatus;
use App\Enums\AttendeeEstimate;
use App\Enums\TargetAudience;
use App\Enums\VenueConsent;
use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends Model
{
    use HasPublicId, SoftDeletes;

    protected $fillable = [
        'public_id', 'reference_no', 'user_id',
        'applicant_name', 'applicant_phone', 'applicant_email', 'background', 'background_other',
        'state_id', 'district_id',
        'venue_name', 'venue_address', 'venue_maps_url', 'venue_consent', 'venue_pic_name', 'venue_pic_phone',
        'event_category_id', 'topic', 'preferred_date_1', 'preferred_date_2',
        'estimated_attendees', 'target_audience',
        'notes', 'privacy_consent_at',
        'status', 'status_changed_at', 'assigned_admin_id', 'event_id', 'submitted_at', 'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'background' => ApplicantBackground::class,
            'venue_consent' => VenueConsent::class,
            'estimated_attendees' => AttendeeEstimate::class,
            'target_audience' => TargetAudience::class,
            'preferred_date_1' => 'date',
            'preferred_date_2' => 'date',
            'privacy_consent_at' => 'datetime',
            'status_changed_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    /**
     * Dirujuk melalui public_id, bukan ID berjujukan.
     *
     * Notifikasi menjana pautan daripada public_id; tanpa ini pengikatan
     * model mencari mengikut kunci utama dan setiap pautan itu 404.
     */
    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_admin_id');
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class, 'event_category_id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ApplicationStatusHistory::class)->latest();
    }

    /** Garis masa yang selamat dipaparkan kepada Penggerak (tanpa nota dalaman). */
    public function publicTimeline(): HasMany
    {
        return $this->hasMany(ApplicationStatusHistory::class)
            ->whereNotNull('public_note')
            ->oldest();
    }

    /**
     * Nota dalaman pasukan BeDaie. Dinamakan `internalNotes` kerana
     * lajur `notes` menyimpan nota tambahan daripada pemohon sendiri.
     */
    public function internalNotes(): HasMany
    {
        return $this->hasMany(ApplicationNote::class)->latest('occurred_at');
    }

    public function scopeSubmitted(Builder $q): Builder
    {
        return $q->whereNotNull('submitted_at');
    }

    public function scopeOpen(Builder $q): Builder
    {
        return $q->whereIn('status', array_map(fn ($s) => $s->value, ApplicationStatus::openStatuses()));
    }

    public function backgroundLabel(): string
    {
        if ($this->background === ApplicantBackground::LainLain && $this->background_other) {
            return $this->background_other;
        }

        return $this->background?->label() ?? '—';
    }

    public function isConverted(): bool
    {
        return $this->event_id !== null;
    }
}
