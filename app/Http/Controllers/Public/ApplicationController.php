<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\EventCategory;
use App\Models\State;

class ApplicationController extends Controller
{
    /** Borang "Jemput BeDaie" — 4 langkah, dikendalikan oleh Livewire. */
    public function create()
    {
        return view('public.apply', [
            'categories' => EventCategory::active()->ordered()->get(),
        ]);
    }

    public function success(Application $application)
    {
        return view('public.apply-success', [
            'application' => $application->load(['state', 'district', 'category']),
        ]);
    }

    /** "Bawa BeDaie ke kawasan saya" — borang minat ringkas. */
    public function interest()
    {
        return view('public.interest', [
            'states' => State::orderBy('sort_order')->get(),
            'categories' => EventCategory::active()->ordered()->get(),
        ]);
    }
}
