<?php

namespace App\Http\Controllers\Penggerak;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\ImpactStatsService;
use App\Services\QrCodeService;
use Symfony\Component\HttpFoundation\Response;

class EventController extends Controller
{
    public function __construct(
        private QrCodeService $qr,
        private ImpactStatsService $stats,
    ) {}

    public function index()
    {
        return view('penggerak.events', [
            'events' => Event::whereHas('mobilizers', fn ($q) => $q->whereKey(auth()->id()))
                ->with(['venue', 'state', 'district', 'speaker', 'category'])
                ->orderByDesc('starts_at')
                ->paginate(10),
        ]);
    }

    public function show(Event $event)
    {
        $this->authorize('view', $event);

        $event->load(['venue', 'state', 'district', 'speaker', 'category', 'mobilizers']);

        return view('penggerak.event', [
            'event' => $event,
            'qrSvg' => $this->qr->svg($event->publicUrl(), 260),
            'summary' => $event->hasEnded() ? $this->stats->mobilizerSummary($event) : null,
        ]);
    }

    /** Poster rasmi yang dijana daripada template BeDaie. */
    public function poster(Event $event): Response
    {
        $this->authorize('view', $event);

        return response()->view('penggerak.poster', [
            'event' => $event->load(['venue', 'state', 'district', 'speaker', 'category']),
            'qrSvg' => $this->qr->svg($event->publicUrl(), 300, '#0A083B'),
        ])->header('Content-Type', 'text/html; charset=utf-8');
    }

    /** Muat turun QR pendaftaran sebagai SVG. */
    public function qr(Event $event): Response
    {
        $this->authorize('view', $event);

        return response($this->qr->svg($event->publicUrl(), 720), 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="qr-'.$event->short_code.'.svg"',
        ]);
    }
}
