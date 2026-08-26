<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Certificate;
use App\Models\Event;
use App\Models\OutreachTarget;
use App\Models\Registration;
use Illuminate\Support\Str;

class ReferenceGenerator
{
    /** BDJ-P-2026-000123 */
    public function application(): string
    {
        $prefix = config('jelajah.reference.application').'-'.now()->year;
        $seq = Application::whereYear('created_at', now()->year)->withTrashed()->count() + 1;

        return $this->unique(Application::class, 'reference_no', $prefix, $seq);
    }

    /** BDJ-S-2026-000123 — sasaran jelajah (aliran keluar) */
    public function outreachTarget(): string
    {
        $prefix = config('jelajah.reference.outreach', 'BDJ-S').'-'.now()->year;
        $seq = OutreachTarget::whereYear('created_at', now()->year)->withTrashed()->count() + 1;

        return $this->unique(OutreachTarget::class, 'reference_no', $prefix, $seq);
    }

    /** BDJ-R-2026-000123 */
    public function registration(): string
    {
        $prefix = config('jelajah.reference.registration').'-'.now()->year;
        $seq = Registration::whereYear('created_at', now()->year)->withTrashed()->count() + 1;

        return $this->unique(Registration::class, 'reference_no', $prefix, $seq);
    }

    /** BDJ-2026-KEL-000123 */
    public function certificate(?string $stateCode = null): string
    {
        $prefix = config('jelajah.reference.certificate').'-'.now()->year.'-'.strtoupper($stateCode ?: 'MYS');
        $seq = Certificate::whereYear('created_at', now()->year)->withTrashed()->count() + 1;

        return $this->unique(Certificate::class, 'certificate_number', $prefix, $seq);
    }

    /** Kod pendek untuk URL /j/BDJ1026 */
    public function eventShortCode(): string
    {
        do {
            $code = 'BDJ'.strtoupper(Str::random(5));
        } while (Event::withTrashed()->where('short_code', $code)->exists());

        return $code;
    }

    /** Slug unik untuk landing page program. */
    public function eventSlug(string $title, ?int $year = null): string
    {
        $base = Str::slug($title);
        $year = $year ?: now()->year;
        $slug = $base.'-'.$year;
        $i = 2;

        while (Event::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$year.'-'.$i++;
        }

        return $slug;
    }

    private function unique(string $model, string $column, string $prefix, int $seq): string
    {
        do {
            $reference = $prefix.'-'.str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
            $seq++;
        } while ($model::withTrashed()->where($column, $reference)->exists());

        return $reference;
    }
}
