<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Menetapkan atau menukar kata laluan sendiri.
 *
 * Akaun yang dicipta automatik daripada permohonan tidak mempunyai kata
 * laluan yang diketahui pemiliknya, jadi "kata laluan semasa" hanya
 * diwajibkan bagi mereka yang benar-benar pernah menetapkannya.
 */
class PasswordController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $hasSetPassword = (bool) $user->password_set_at;

        $data = $request->validate([
            'current_password' => [$hasSetPassword ? 'required' : 'nullable', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [], [
            'current_password' => 'kata laluan semasa',
            'password' => 'kata laluan baharu',
        ]);

        if ($hasSetPassword && ! Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Kata laluan semasa tidak betul.']);
        }

        $user->forceFill([
            'password' => $data['password'],
            'password_set_at' => now(),
        ])->save();

        ActivityLogger::log('auth.password_changed', $user, "{$user->name} menetapkan kata laluan.");

        return back()->with('success', $hasSetPassword
            ? 'Kata laluan dikemas kini.'
            : 'Kata laluan ditetapkan. Anda kini boleh log masuk dengan kata laluan.');
    }
}
