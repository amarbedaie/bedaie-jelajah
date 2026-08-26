<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationGuest extends Model
{
    protected $fillable = ['registration_id', 'name', 'gender', 'age_group'];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public static function ageGroups(): array
    {
        return [
            'kanak_kanak' => 'Kanak-kanak (bawah 12)',
            'remaja' => 'Remaja (12–17)',
            'dewasa' => 'Dewasa (18+)',
            'warga_emas' => 'Warga emas (60+)',
        ];
    }
}
