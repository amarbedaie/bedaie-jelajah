<?php

namespace App\Support;

/**
 * Satu-satunya tempat logik nombor telefon Malaysia dinormalkan.
 * Format keluaran: antarabangsa tanpa tanda, cth. 60123456789.
 */
class Phone
{
    public static function normalise(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $phone) ?? '';

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '6'.$digits;
        }

        if (! str_starts_with($digits, '6')) {
            $digits = '60'.ltrim($digits, '0');
        }

        return strlen($digits) >= 10 ? $digits : null;
    }

    /** Menyamarkan nombor untuk paparan yang tidak memerlukan nombor penuh. */
    public static function mask(?string $phone): string
    {
        $digits = preg_replace('/\D/', '', (string) $phone) ?? '';

        if (strlen($digits) < 6) {
            return '—';
        }

        return substr($digits, 0, 4).str_repeat('•', max(3, strlen($digits) - 7)).substr($digits, -3);
    }
}
