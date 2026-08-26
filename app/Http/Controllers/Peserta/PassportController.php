<?php

namespace App\Http\Controllers\Peserta;

use App\Enums\RegistrationStatus;
use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Event;
use App\Models\Registration;

/**
 * Pasport Ilmu BeDaie — rekod pembelajaran peserta.
 * Sengaja ringkas: tiada gamifikasi berat pada MVP.
 */
class PassportController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $registrations = Registration::where('user_id', $user->id)
            ->with(['event.state', 'event.venue', 'event.category', 'attendance', 'certificate'])
            ->orderByDesc('registered_at')
            ->get();

        $attended = $registrations->filter->hasAttended();

        return view('peserta.passport', [
            'user' => $user,
            'joined' => $registrations->count(),
            'completed' => $attended->count(),
            'learningHours' => round($attended->sum(fn ($r) => (float) ($r->event->learning_hours ?? 0)), 1),
            'certificates' => Certificate::where('user_id', $user->id)->valid()
                ->with('event.state')->latest()->get(),
            'upcoming' => $registrations
                ->filter(fn ($r) => $r->event->starts_at->isFuture()
                    && $r->status !== RegistrationStatus::Dibatalkan)
                ->sortBy(fn ($r) => $r->event->starts_at)
                ->values(),
            'history' => $registrations
                ->filter(fn ($r) => $r->event->starts_at->isPast())
                ->values(),
            'suggested' => $this->suggestions($attended->pluck('event.event_category_id')->filter()->all(),
                $registrations->pluck('event_id')->all()),
        ]);
    }

    public function events()
    {
        return view('peserta.events', [
            'registrations' => Registration::where('user_id', auth()->id())
                ->with(['event.state', 'event.venue', 'event.speaker', 'attendance', 'payment', 'guests'])
                ->orderByDesc('registered_at')
                ->paginate(12),
        ]);
    }

    public function certificates()
    {
        return view('peserta.certificates', [
            'certificates' => Certificate::where('user_id', auth()->id())
                ->with(['event.state', 'event.speaker', 'event.venue'])
                ->latest()
                ->paginate(12),
        ]);
    }

    /** Cadangan program berdasarkan kategori yang pernah disertai. */
    private function suggestions(array $categoryIds, array $excludeEventIds)
    {
        return Event::upcoming()
            ->whereNotIn('id', $excludeEventIds)
            ->when($categoryIds !== [], fn ($q) => $q->orderByRaw(
                'CASE WHEN event_category_id IN ('.implode(',', array_fill(0, count($categoryIds), '?')).') THEN 0 ELSE 1 END',
                $categoryIds,
            ))
            ->with(['state', 'venue', 'category'])
            ->limit(3)
            ->get();
    }
}
