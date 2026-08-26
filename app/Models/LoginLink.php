<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class LoginLink extends Model
{
    protected $fillable = [
        'token', 'user_id', 'channel', 'requested_ip', 'expires_at', 'used_at',
    ];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'used_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Pautan baharu untuk seorang pengguna; pautan lama yang belum diguna dibatalkan. */
    public static function issueFor(User $user, string $channel = 'whatsapp', ?string $ip = null): self
    {
        static::where('user_id', $user->id)->whereNull('used_at')->delete();

        return static::create([
            'token' => Str::random(48),
            'user_id' => $user->id,
            'channel' => $channel,
            'requested_ip' => $ip,
            'expires_at' => now()->addMinutes(30),
        ]);
    }

    public function isUsable(): bool
    {
        return $this->used_at === null && $this->expires_at->isFuture();
    }

    public function url(): string
    {
        return route('masuk.pautan.guna', $this->token);
    }

    public function consume(): void
    {
        $this->forceFill(['used_at' => now()])->save();
    }
}
