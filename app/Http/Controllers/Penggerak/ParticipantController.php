<?php

namespace App\Http\Controllers\Penggerak;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Http\Request;

class ParticipantController extends Controller
{
    public function index(Request $request)
    {
        $events = Event::whereHas('mobilizers', fn ($q) => $q->whereKey(auth()->id()))
            ->orderByDesc('starts_at')->get();

        $selected = $request->filled('program')
            ? $events->firstWhere('short_code', $request->string('program'))
            : $events->first();

        $registrations = $selected
            ? Registration::where('event_id', $selected->id)
                ->active()
                ->with(['attendance', 'state', 'district'])
                ->orderBy('name')
                ->paginate(25)->withQueryString()
            : null;

        return view('penggerak.participants', [
            'events' => $events,
            'selected' => $selected,
            'registrations' => $registrations,
        ]);
    }
}
