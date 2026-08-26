<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\EventCategory;
use App\Models\State;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        $applications = Application::submitted()
            ->with(['state', 'district', 'category', 'assignedAdmin', 'event'])
            ->when($request->filled('status'),
                fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('negeri'),
                fn ($q) => $q->whereHas('state', fn ($s) => $s->where('slug', $request->string('negeri'))))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q').'%';
                $q->where(fn ($w) => $w->where('applicant_name', 'like', $term)
                    ->orWhere('venue_name', 'like', $term)
                    ->orWhere('reference_no', 'like', $term)
                    ->orWhere('applicant_phone', 'like', $term));
            })
            ->latest('submitted_at')
            ->paginate(20)->withQueryString();

        return view('admin.applications', [
            'applications' => $applications,
            'statuses' => ApplicationStatus::options(),
            'states' => State::orderBy('sort_order')->get(),
            'counts' => Application::submitted()
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function show(Application $application)
    {
        return view('admin.application', [
            'application' => $application->load([
                'state', 'district', 'category', 'user', 'assignedAdmin',
                'statusHistories.user', 'internalNotes.user', 'event.venue',
            ]),
            'categories' => EventCategory::active()->ordered()->get(),
        ]);
    }
}
