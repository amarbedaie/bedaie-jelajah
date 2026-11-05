<?php

namespace App\Models;

use App\Enums\CertificateDesign;
use App\Enums\CertificateType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CertificateTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'type', 'orientation', 'design', 'background_path', 'accent_color',
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

    /**
     * Gaya & orientasi berkesan bagi sijil: program → kategori program →
     * templat → lalai (klasik, landskap).
     *
     * @return array{0: CertificateDesign, 1: string}
     */
    public static function appearance(?self $template, ?Event $event = null): array
    {
        $category = $event?->category;

        $design = CertificateDesign::tryFrom((string) ($event?->certificate_design
            ?: $category?->certificate_design
            ?: $template?->design)) ?? CertificateDesign::Klasik;

        $orientation = $event?->certificate_orientation
            ?: $category?->certificate_orientation
            ?: $template?->orientation
            ?: 'landscape';

        return [$design, $orientation === 'portrait' ? 'portrait' : 'landscape'];
    }

    public static function resolveFor(CertificateType $type): ?self
    {
        return static::active()->where('type', $type->value)->orderByDesc('is_default')->first()
            ?? static::active()->where('is_default', true)->first();
    }
}
