<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Partner;
use App\Models\Testimonial;
use App\Services\ImpactStatsService;

class HomeController extends Controller
{
    public function __construct(private ImpactStatsService $stats) {}

    public function index()
    {
        return view('public.home', [
            'headline' => $this->stats->headline(),
            'states' => $this->stats->stateMap(),
            'upcoming' => Event::upcoming()
                ->with(['category', 'speaker', 'venue', 'state', 'district'])
                ->limit(6)->get(),
            'recent' => Event::completed()
                ->with(['category', 'venue', 'state', 'district'])
                ->withCount('gallery')
                ->limit(3)->get(),
            'categories' => EventCategory::active()->ordered()->get(),
            'testimonials' => Testimonial::featured()->limit(6)->get(),
            'partners' => Partner::active()->ordered()->get(),
        ]);
    }
}
