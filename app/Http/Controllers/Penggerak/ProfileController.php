<?php

namespace App\Http\Controllers\Penggerak;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\State;
use App\Support\Phone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user()->load('mobilizerProfile');

        return view('penggerak.profile', [
            'user' => $user,
            'states' => State::orderBy('sort_order')->get(),
            'districts' => District::where('state_id', $user->state_id)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:20'],
            'state_id' => ['required', 'exists:states,id'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'organization_name' => ['nullable', 'string', 'max:180'],
            'about' => ['nullable', 'string', 'max:500'],
        ], [], [
            'name' => 'nama penuh',
            'phone' => 'nombor WhatsApp',
            'state_id' => 'negeri',
        ]);

        $user->update([
            'name' => $data['name'],
            'phone' => Phone::normalise($data['phone']) ?? $user->phone,
            'state_id' => $data['state_id'],
            'district_id' => $data['district_id'] ?? null,
        ]);

        $user->mobilizerProfile()->updateOrCreate([], [
            'organization_name' => $data['organization_name'] ?? null,
            'about' => $data['about'] ?? null,
            'whatsapp' => $user->phone,
            'state_id' => $data['state_id'],
            'district_id' => $data['district_id'] ?? null,
        ]);

        return back()->with('success', 'Profil anda telah dikemas kini.');
    }
}
