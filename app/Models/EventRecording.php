<?php

namespace App\Models;

use App\Enums\RecordingType;
use App\Enums\RecordingVisibility;
use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class EventRecording extends Model
{
    use HasPublicId;

    protected $fillable = [
        'public_id', 'event_id', 'title', 'description', 'type', 'provider',
        'url', 'file_path', 'duration_minutes', 'visibility', 'is_published',
        'available_from', 'sort_order', 'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => RecordingType::class,
            'visibility' => RecordingVisibility::class,
            'is_published' => 'boolean',
            'available_from' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(RecordingView::class);
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('is_published', true)
            ->where(fn ($w) => $w->whereNull('available_from')->orWhere('available_from', '<=', now()));
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('sort_order')->orderBy('id');
    }

    public function isAvailable(): bool
    {
        return $this->is_published
            && (! $this->available_from || $this->available_from->isPast());
    }

    /**
     * Adakah pendaftaran ini layak menonton?
     * Rakaman "hadir" hanya untuk peserta yang QR-nya benar-benar diimbas —
     * itulah nilai sebenar hadir ke masjid.
     */
    public function viewableBy(?Registration $registration): bool
    {
        if (! $this->isAvailable()) {
            return false;
        }

        return match ($this->visibility) {
            RecordingVisibility::Awam => true,
            RecordingVisibility::Berdaftar => $registration !== null
                && $registration->event_id === $this->event_id,
            RecordingVisibility::Hadir => $registration !== null
                && $registration->event_id === $this->event_id
                && $registration->hasAttended(),
        };
    }

    /** Sebab yang boleh dibaca pengguna apabila akses ditolak. */
    public function lockedReason(?Registration $registration): ?string
    {
        if ($this->viewableBy($registration)) {
            return null;
        }

        if (! $this->isAvailable()) {
            return $this->available_from
                ? 'Rakaman dibuka pada '.$this->available_from->translatedFormat('j F Y, g:ia').'.'
                : 'Rakaman belum diterbitkan.';
        }

        return match ($this->visibility) {
            RecordingVisibility::Hadir => 'Rakaman ini hanya untuk peserta yang hadir ke program.',
            RecordingVisibility::Berdaftar => 'Rakaman ini hanya untuk peserta yang berdaftar.',
            default => 'Rakaman ini tidak tersedia.',
        };
    }

    /** URL benam untuk pemain video; null jika bukan video yang boleh dibenam. */
    public function embedUrl(): ?string
    {
        if (! $this->url) {
            return null;
        }

        if ($this->provider === 'youtube') {
            $id = null;

            if (preg_match('~youtu\.be/([A-Za-z0-9_-]{6,})~', $this->url, $m)) {
                $id = $m[1];
            } elseif (preg_match('~[?&]v=([A-Za-z0-9_-]{6,})~', $this->url, $m)) {
                $id = $m[1];
            } elseif (preg_match('~youtube\.com/embed/([A-Za-z0-9_-]{6,})~', $this->url, $m)) {
                $id = $m[1];
            }

            return $id ? "https://www.youtube-nocookie.com/embed/{$id}" : null;
        }

        if ($this->provider === 'vimeo' && preg_match('~vimeo\.com/(\d+)~', $this->url, $m)) {
            return "https://player.vimeo.com/video/{$m[1]}";
        }

        return null;
    }

    public function downloadUrl(): ?string
    {
        return $this->file_path
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->file_path)
            : $this->url;
    }

    public function durationLabel(): ?string
    {
        if (! $this->duration_minutes) {
            return null;
        }

        $h = intdiv($this->duration_minutes, 60);
        $m = $this->duration_minutes % 60;

        return $h > 0 ? "{$h} jam {$m} minit" : "{$m} minit";
    }

    public function summary(): string
    {
        return Str::limit((string) $this->description, 140);
    }
}
