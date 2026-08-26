<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class QrToken extends Model
{
    protected $fillable = ['token', 'tokenable_type', 'tokenable_id', 'purpose', 'expires_at', 'revoked_at'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'revoked_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (QrToken $token) {
            if (empty($token->token)) {
                $token->token = static::generateToken();
            }
        });
    }

    public static function generateToken(): string
    {
        do {
            $token = strtoupper(Str::random(10)).'-'.strtoupper(Str::random(10));
        } while (static::where('token', $token)->exists());

        return $token;
    }

    public function tokenable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isUsable(): bool
    {
        if ($this->revoked_at) {
            return false;
        }

        return ! $this->expires_at || $this->expires_at->isFuture();
    }

    public function revoke(): void
    {
        $this->update(['revoked_at' => now()]);
    }
}
