<?php

namespace App\Models;

use App\Enums\ApplicantBackground;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobilizerProfile extends Model
{
    protected $fillable = [
        'user_id', 'background', 'background_other', 'organization_name', 'about',
        'whatsapp', 'address', 'state_id', 'district_id', 'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'background' => ApplicantBackground::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function backgroundLabel(): string
    {
        if ($this->background === ApplicantBackground::LainLain && $this->background_other) {
            return $this->background_other;
        }

        return $this->background?->label() ?? 'Penggerak Jelajah';
    }
}
