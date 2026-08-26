<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OutreachTarget;

/** Papan dan butiran sasaran — kedua-duanya diuruskan komponen Livewire. */
class OutreachController extends Controller
{
    public function index()
    {
        return view('admin.outreach');
    }

    public function show(OutreachTarget $target)
    {
        return view('admin.outreach-detail', [
            'target' => $target->load(['state', 'district', 'assignee', 'partner', 'referrer']),
        ]);
    }
}
