<?php

namespace App\Http\Controllers\Penggerak;

use App\Http\Controllers\Controller;
use App\Models\Application;

class ApplicationController extends Controller
{
    public function index()
    {
        return view('penggerak.applications', [
            'applications' => Application::where('user_id', auth()->id())
                ->with(['state', 'district', 'category', 'event'])
                ->latest('submitted_at')
                ->paginate(10),
        ]);
    }

    public function show(Application $application)
    {
        $this->authorize('view', $application);

        return view('penggerak.application', [
            // publicTimeline() menapis nota dalaman BeDaie.
            'application' => $application->load([
                'state', 'district', 'category', 'event.venue', 'publicTimeline',
            ]),
        ]);
    }
}
