<?php

namespace App\Http\Controllers\Penggerak;

use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Event;
use App\Services\ImpactStatsService;
use App\Services\QrCodeService;

class DashboardController extends Controller
{
    public function __construct(
        private ImpactStatsService $stats,
        private QrCodeService $qr,
    ) {}

    public function index()
    {
        $user = auth()->user();

        // Program aktif = yang paling hampir dan belum selesai.
        $active = Event::whereHas('mobilizers', fn ($q) => $q->whereKey($user->id))
            ->whereIn('status', [EventStatus::Diterbitkan, EventStatus::Berlangsung, EventStatus::Draf])
            ->with(['venue', 'state', 'district', 'speaker', 'category'])
            ->orderBy('starts_at')
            ->first();

        $applications = Application::where('user_id', $user->id)
            ->with(['state', 'district', 'category', 'event'])
            ->latest('submitted_at')
            ->get();

        $pastEvents = Event::whereHas('mobilizers', fn ($q) => $q->whereKey($user->id))
            ->where('status', EventStatus::Selesai)
            ->with(['venue', 'state'])
            ->orderByDesc('starts_at')
            ->limit(3)->get();

        return view('penggerak.dashboard', [
            'user' => $user,
            'active' => $active,
            'qrSvg' => $active ? $this->qr->svg($active->publicUrl(), 220) : null,
            'applications' => $applications,
            'openApplications' => $applications->reject(fn ($a) => $a->status->isClosed()),
            'pastEvents' => $pastEvents,
            'summary' => $active && $active->status === EventStatus::Selesai
                ? $this->stats->mobilizerSummary($active)
                : null,
        ]);
    }
}
