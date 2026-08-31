<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\State;
use App\Services\ImpactStatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EventController extends Controller
{
    public function __construct(private ImpactStatsService $stats) {}

    public function index(Request $request)
    {
        $events = Event::with(['venue', 'state', 'district', 'speaker', 'category'])
            ->withSeatCounts()
            ->when($request->string('status')->value() === 'perlu_ditutup',
                fn ($q) => $q->where('status', EventStatus::Diterbitkan)
                    ->where('starts_at', '<', now()->subHours(6)))
            ->when($request->filled('status') && $request->string('status')->value() !== 'perlu_ditutup',
                fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('negeri'),
                fn ($q) => $q->whereHas('state', fn ($s) => $s->where('slug', $request->string('negeri'))))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q').'%';
                $q->where(fn ($w) => $w->where('title', 'like', $term)
                    ->orWhere('short_code', 'like', $term));
            })
            ->orderByDesc('starts_at')
            ->paginate(20)->withQueryString();

        return view('admin.events', [
            'events' => $events,
            'states' => State::orderBy('sort_order')->get(),
            'statuses' => EventStatus::cases(),
            'needsClosing' => Event::where('status', EventStatus::Diterbitkan)
                ->where('starts_at', '<', now()->subHours(6))->count(),
        ]);
    }

    public function show(Event $event)
    {
        $event->load([
            'venue', 'state', 'district', 'speaker', 'category', 'mobilizers',
            'application.user', 'certificateTemplate',
        ]);

        return view('admin.event', [
            'event' => $event,
            'report' => $this->stats->eventReport($event),
            'categories' => EventCategory::active()->ordered()->get(),
        ]);
    }

    public function calendar(Request $request)
    {
        $month = $request->filled('bulan')
            ? Carbon::parse($request->string('bulan').'-01')
            : now()->startOfMonth();

        $events = Event::whereBetween('starts_at', [
            $month->clone()->startOfMonth()->startOfWeek(),
            $month->clone()->endOfMonth()->endOfWeek(),
        ])->with(['venue', 'state', 'speaker'])->orderBy('starts_at')->get();

        return view('admin.calendar', [
            'month' => $month,
            'events' => $events->groupBy(fn (Event $e) => $e->starts_at->toDateString()),
        ]);
    }
}
