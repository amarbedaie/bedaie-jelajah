<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EventStatus;
use App\Enums\RegistrationStatus;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\AttendanceService;
use Illuminate\Http\Request;

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

    public function show(Event $event, Request $request)
    {
        // Pada malam program, soalan sebenar staf ialah "siapa belum
        // sampai" — bukan berapa peratus. Jadi senarai nama, bukan CSV.
        $registrations = $event->registrations()
            ->with(['attendance', 'state'])
            ->whereIn('status', [
                RegistrationStatus::Disahkan->value,
                RegistrationStatus::MenungguPengesahan->value,
            ])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q').'%';
                $q->where(fn ($w) => $w->where('name', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('reference_no', 'like', $term));
            })
            ->orderBy('name')
            ->get();

        $filter = $request->string('hadir')->toString();

        if ($filter === 'belum') {
            $registrations = $registrations->filter(fn ($r) => ! $r->hasAttended())->values();
        } elseif ($filter === 'sudah') {
            $registrations = $registrations->filter->hasAttended()->values();
        }

        return view('admin.attendance', [
            'event' => $event->load(['venue', 'state', 'speaker']),
            'stats' => $this->attendance->liveStats($event),
            'registrations' => $registrations,
            'filter' => $filter,
        ]);
    }
}
