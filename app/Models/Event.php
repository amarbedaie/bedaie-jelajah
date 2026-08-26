<?php

namespace App\Models;

use App\Enums\EventStatus;
use App\Enums\PricingMode;
use App\Enums\RegistrationStatus;
use App\Enums\TargetAudience;
use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Event extends Model
{
    use HasPublicId, SoftDeletes;

    protected $fillable = [
        'public_id', 'short_code', 'slug', 'application_id', 'event_category_id', 'speaker_id',
        'venue_id', 'state_id', 'district_id', 'title', 'theme', 'description',
        'poster_path', 'hero_image_path', 'starts_at', 'ends_at', 'doors_open_at',
        'pricing_mode', 'price', 'currency', 'capacity', 'allow_waiting_list',
        'allow_guest_registration', 'max_guests_per_registration', 'requires_approval', 'invite_code',
        'registration_opens_at', 'registration_closes_at', 'status', 'target_audience',
        'organizer_name', 'contact_phone', 'certificate_enabled', 'certificate_template_id',
        'min_attendance_percent', 'feedback_required_for_certificate', 'learning_hours',
        'tentative', 'faqs', 'parking_info', 'registered_count', 'attended_count', 'waitlist_count',
        'published_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => EventStatus::class,
            'pricing_mode' => PricingMode::class,
            'target_audience' => TargetAudience::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'doors_open_at' => 'datetime',
            'registration_opens_at' => 'datetime',
            'registration_closes_at' => 'datetime',
            'published_at' => 'datetime',
            'completed_at' => 'datetime',
            'tentative' => 'array',
            'faqs' => 'array',
            'price' => 'decimal:2',
            'learning_hours' => 'decimal:2',
            'allow_waiting_list' => 'boolean',
            'allow_guest_registration' => 'boolean',
            'requires_approval' => 'boolean',
            'certificate_enabled' => 'boolean',
            'feedback_required_for_certificate' => 'boolean',
        ];
    }

    // ── Relationships ────────────────────────────────────────────────

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class, 'event_category_id');
    }

    public function speaker(): BelongsTo
    {
        return $this->belongsTo(Speaker::class);
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function certificateTemplate(): BelongsTo
    {
        return $this->belongsTo(CertificateTemplate::class, 'certificate_template_id');
    }

    public function mobilizers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_mobilizers')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class)->orderBy('sort_order');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    public function recordings(): HasMany
    {
        return $this->hasMany(EventRecording::class)->ordered();
    }

    public function gallery(): HasMany
    {
        return $this->hasMany(EventGallery::class)->orderBy('sort_order');
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopePublic(Builder $q): Builder
    {
        return $q->whereIn('status', [
            EventStatus::Diterbitkan->value,
            EventStatus::Berlangsung->value,
            EventStatus::Selesai->value,
        ]);
    }

    public function scopeUpcoming(Builder $q): Builder
    {
        return $q->whereIn('status', [EventStatus::Diterbitkan->value, EventStatus::Berlangsung->value])
            ->where('starts_at', '>=', now()->startOfDay())
            ->orderBy('starts_at');
    }

    public function scopeCompleted(Builder $q): Builder
    {
        return $q->where('status', EventStatus::Selesai->value)->orderByDesc('starts_at');
    }

    // ── Kapasiti & pendaftaran ───────────────────────────────────────

    /** Bilangan tempat yang telah diambil (peserta + tetamu). */
    public function seatsTaken(): int
    {
        return (int) $this->registrations()
            ->whereIn('status', [RegistrationStatus::Disahkan->value, RegistrationStatus::MenungguPengesahan->value])
            ->selectRaw('COALESCE(SUM(1 + guests_count), 0) as total')
            ->value('total');
    }

    public function seatsLeft(): ?int
    {
        if ($this->capacity <= 0) {
            return null; // kapasiti tidak terhad
        }

        return max(0, $this->capacity - $this->seatsTaken());
    }

    public function isFull(): bool
    {
        $left = $this->seatsLeft();

        return $left !== null && $left <= 0;
    }

    public function fillPercent(): int
    {
        if ($this->capacity <= 0) {
            return 0;
        }

        return (int) min(100, round($this->seatsTaken() / $this->capacity * 100));
    }

    public function hasEnded(): bool
    {
        return ($this->ends_at ?? $this->starts_at)->isPast();
    }

    public function isArchived(): bool
    {
        return $this->status === EventStatus::Selesai || $this->hasEnded();
    }

    public function registrationOpen(): bool
    {
        if (! in_array($this->status, [EventStatus::Diterbitkan, EventStatus::Berlangsung], true)) {
            return false;
        }

        if ($this->registration_opens_at && $this->registration_opens_at->isFuture()) {
            return false;
        }

        $closes = $this->registration_closes_at ?? $this->starts_at;

        return $closes->isFuture();
    }

    /** Sebab pendaftaran ditutup — untuk paparan mesra pada landing page. */
    public function registrationClosedReason(): ?string
    {
        if ($this->registrationOpen()) {
            return null;
        }

        if ($this->status === EventStatus::Dibatalkan) {
            return 'Program ini telah dibatalkan.';
        }

        if ($this->status === EventStatus::Ditangguhkan) {
            return 'Program ini ditangguhkan. Tarikh baharu akan diumumkan.';
        }

        if ($this->isArchived()) {
            return 'Program ini telah selesai.';
        }

        if ($this->registration_opens_at?->isFuture()) {
            return 'Pendaftaran akan dibuka pada '.$this->registration_opens_at->translatedFormat('j F Y, g:ia').'.';
        }

        return 'Pendaftaran telah ditutup.';
    }

    public function acceptsWaitlist(): bool
    {
        return $this->allow_waiting_list && $this->isFull();
    }

    // ── Paparan ──────────────────────────────────────────────────────

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function publicUrl(): string
    {
        return route('jelajah.show', [
            'state' => $this->state?->slug ?? 'malaysia',
            'event' => $this->slug,
        ]);
    }

    public function shortUrl(): string
    {
        return url('/j/'.$this->short_code);
    }

    public function priceLabel(): string
    {
        return match ($this->pricing_mode) {
            PricingMode::Percuma => 'Percuma',
            PricingMode::Berbayar => 'RM '.number_format((float) $this->price, 2),
            PricingMode::JemputanSahaja => 'Jemputan Sahaja',
            PricingMode::SumbanganIkhlas => 'Sumbangan Ikhlas',
            PricingMode::Ditaja => 'Ditaja (Percuma)',
        };
    }

    public function dateLabel(): string
    {
        return $this->starts_at->translatedFormat('l, j F Y');
    }

    public function timeLabel(): string
    {
        $start = $this->starts_at->format('g:ia');

        return $this->ends_at ? $start.' – '.$this->ends_at->format('g:ia') : $start;
    }

    public function locationLabel(): string
    {
        return collect([$this->district?->name, $this->state?->name])->filter()->implode(', ');
    }

    /**
     * URL relatif supaya imej berfungsi pada mana-mana hos — APP_URL yang
     * tidak sepadan dengan hos pelayan menjadikan asset() menunjuk ke tempat mati.
     */
    public function posterUrl(): ?string
    {
        return $this->poster_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->poster_path) : null;
    }

    public function heroUrl(): ?string
    {
        $path = $this->hero_image_path ?? $this->poster_path;

        return $path ? \Illuminate\Support\Facades\Storage::disk('public')->url($path) : null;
    }

    /** Mesej WhatsApp siap sedia untuk dikongsi Penggerak. */
    public function whatsappMessage(): string
    {
        $speaker = $this->speaker?->name ?? 'pasukan BeDaie';

        return implode("\n", [
            'Assalamualaikum warahmatullah.',
            '',
            "Anda dijemput menghadiri *{$this->title}* bersama {$speaker}.",
            '',
            '🗓️ '.$this->dateLabel(),
            '🕐 '.$this->timeLabel(),
            '📍 '.($this->venue?->name ?? $this->locationLabel()),
            '🎟️ '.$this->priceLabel(),
            '',
            'Tempat adalah terhad. Daftar di sini:',
            $this->shortUrl(),
            '',
            '_BeDaie Jelajah — Membawa Ilmu, Menghidupkan Ummah._',
        ]);
    }

    public function whatsappShareUrl(): string
    {
        return 'https://wa.me/?text='.rawurlencode($this->whatsappMessage());
    }

    public function countdownTarget(): ?Carbon
    {
        return $this->starts_at->isFuture() ? $this->starts_at : null;
    }

    public function attendanceRate(): float
    {
        if ($this->registered_count <= 0) {
            return 0;
        }

        return round($this->attended_count / $this->registered_count * 100, 1);
    }

    public function averageRating(): ?float
    {
        $avg = $this->feedback()->whereNotNull('rating')->avg('rating');

        return $avg ? round((float) $avg, 1) : null;
    }
}
