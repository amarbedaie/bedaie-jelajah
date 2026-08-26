<?php

namespace App\Models;

use App\Enums\CertificateType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CertificateTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'type', 'orientation', 'background_path', 'accent_color',
        'intro_text', 'closing_text', 'signature_name', 'signature_title',
        'signature_path', 'is_default', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => CertificateType::class,
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public static function resolveFor(CertificateType $type): ?self
    {
        return static::active()->where('type', $type->value)->orderByDesc('is_default')->first()
            ?? static::active()->where('is_default', true)->first();
    }
}
