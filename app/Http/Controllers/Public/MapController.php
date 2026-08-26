<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\State;
use App\Services\ImpactStatsService;

class MapController extends Controller
{
    public function __construct(private ImpactStatsService $stats) {}

    public function index()
    {
        return view('public.map', [
            'states' => $this->stats->stateMap(),
            'headline' => $this->stats->headline(),
        ]);
    }

    public function show(State $state)
    {
        return view('public.state', [
            'detail' => $this->stats->stateDetail($state),
            'states' => $this->stats->stateMap(),
        ]);
    }
}
