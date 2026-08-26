<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, HasPublicId, Notifiable, SoftDeletes;

    protected $fillable = [
        'public_id', 'name', 'email', 'phone', 'password', 'role', 'is_active',
        'avatar_path', 'state_id', 'district_id', 'gender', 'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password_set_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'role' => UserRole::class,
        ];
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function mobilizerProfile(): HasOne
    {
        return $this->hasOne(MobilizerProfile::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    /** Program yang dikendalikan sebagai Penggerak Jelajah. */
    public function mobilizedEvents(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_mobilizers')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isPenggerak(): bool
    {
        return $this->role === UserRole::Penggerak;
    }

    public function isPeserta(): bool
    {
        return $this->role === UserRole::Peserta;
    }

    /** Penggerak & admin sama-sama boleh capai ruang Penggerak. */
    public function canAccessPenggerak(): bool
    {
        return $this->isPenggerak() || $this->isAdmin();
    }

    public function firstName(): string
    {
        $clean = preg_replace('/^(ustaz|ustazah|dr\.?|hj\.?|hjh\.?|tuan|puan|encik|cik)\s+/i', '', $this->name);

        return explode(' ', trim($clean))[0] ?: $this->name;
    }

    public function scopeRole(Builder $q, UserRole $role): Builder
    {
        return $q->where('role', $role->value);
    }

    public function homeRoute(): string
    {
        return $this->role->homeRoute();
    }

    /** Domain e-mel tempatan untuk akaun yang dicipta tanpa e-mel sebenar. */
    public const PLACEHOLDER_EMAIL_DOMAIN = '@jelajah.bedaie.local';

    /**
     * E-mel yang benar-benar boleh dihubungi.
     *
     * Akaun yang dicipta automatik daripada permohonan diberi e-mel
     * pemegang tempat supaya lajur unik itu terisi. Menghantar mel ke
     * sana hanya menghasilkan bounce, jadi ia dianggap tiada e-mel.
     */
    public function realEmail(): ?string
    {
        return $this->hasPlaceholderEmail() ? null : $this->email;
    }

    public function hasPlaceholderEmail(): bool
    {
        return str_ends_with((string) $this->email, self::PLACEHOLDER_EMAIL_DOMAIN);
    }
}
