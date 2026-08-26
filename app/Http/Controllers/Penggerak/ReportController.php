<?php

namespace App\Http\Controllers\Penggerak;

use App\Enums\CertificateType;
use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Event;
use App\Services\ImpactStatsService;

class ReportController extends Controller
{
    public function __construct(private ImpactStatsService $stats) {}

    public function index()
    {
        $user = auth()->user();

        $completed = Event::whereHas('mobilizers', fn ($q) => $q->whereKey($user->id))
            ->where('status', EventStatus::Selesai)
            ->with(['venue', 'state'])
            ->orderByDesc('starts_at')
            ->get();

        return view('penggerak.reports', [
            // Sijil penghargaan Penggerak.
            'certificates' => Certificate::where('user_id', $user->id)
                ->where('type', CertificateType::PenghargaanPenggerak)
                ->with('event.state')
                ->latest()->get(),
            'reports' => $completed->mapWithKeys(
                fn (Event $e) => [$e->id => $this->stats->mobilizerSummary($e)]),
            'events' => $completed,
        ]);
    }
}
