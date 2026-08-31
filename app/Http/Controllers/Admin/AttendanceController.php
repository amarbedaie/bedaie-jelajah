<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\AttendanceService;

class AttendanceController extends Controller
{
    public function __construct(private AttendanceService $attendance) {}

    public function index()
    {
        return view('admin.attendance-index', [
            'today' => Event::withSeatCounts()->whereBetween('starts_at', [now()->startOfDay(), now()->endOfDay()])
                ->with(['venue', 'state'])->orderBy('starts_at')->get(),
            'upcoming' => Event::upcoming()->withSeatCounts()->with(['venue', 'state'])->limit(10)->get(),
            'recent' => Event::withSeatCounts()->where('status', EventStatus::Selesai)
                ->with(['venue', 'state'])->orderByDesc('starts_at')->limit(10)->get(),
        ]);
    }

    public function show(Event $event)
    {
        return view('admin.attendance', [
            'event' => $event->load(['venue', 'state', 'speaker']),
            'stats' => $this->attendance->liveStats($event),
        ]);
    }
}
