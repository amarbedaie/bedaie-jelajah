<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\State;
use App\Services\ImpactStatsService;

class CatalogController extends Controller
{
    public function __construct(private ImpactStatsService $stats) {}

    public function states()
    {
        return view('admin.states', [
            'states' => $this->stats->stateMap(),
            'raw' => State::withCount(['districts', 'events', 'areaInterestRequests'])
                ->orderBy('sort_order')->get()->keyBy('id'),
        ]);
    }

    public function demand()
    {
        return view('admin.demand', [
            'areas' => $this->stats->topDemandAreas(30),
            'topics' => $this->stats->topRequestedTopics(10),
            'states' => $this->stats->stateMap(),
        ]);
    }

    /** Penceramah diuruskan melalui komponen Livewire. */
    public function speakers()
    {
        return view('admin.speakers');
    }

    /** Kategori diuruskan melalui komponen Livewire. */
    public function categories()
    {
        return view('admin.categories');
    }
}
