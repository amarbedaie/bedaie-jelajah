<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\State;
use App\Support\Phone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();

        return view('peserta.profile', [
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
            'email' => ['nullable', 'email', 'max:180', Rule::unique('users', 'email')->ignore($user->id)],
            'gender' => ['nullable', 'in:lelaki,perempuan'],
            'state_id' => ['nullable', 'exists:states,id'],
            'district_id' => ['nullable', 'exists:districts,id'],
        ], [], [
            'name' => 'nama penuh',
            'phone' => 'nombor WhatsApp',
        ]);

        // E-mel hanya boleh diisi sekali, apabila ia masih pemegang tempat.
        if ($user->hasPlaceholderEmail() && ! empty($data['email'])) {
            $user->forceFill(['email' => $data['email'], 'email_verified_at' => null])->save();
        }

        $user->update([
            'name' => $data['name'],
            'phone' => Phone::normalise($data['phone']) ?? $user->phone,
            'gender' => $data['gender'] ?? null,
            'state_id' => $data['state_id'] ?? null,
            'district_id' => $data['district_id'] ?? null,
        ]);

        return back()->with('success',
            'Profil dikemas kini. Nama ini akan digunakan pada sijil akan datang.');
    }
}
