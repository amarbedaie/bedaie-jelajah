<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    public function request()
    {
        return view('auth.forgot-password');
    }

    public function email(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']], [], ['email' => 'e-mel']);

        $status = PasswordBroker::sendResetLink($request->only('email'));

        // Jangan dedahkan sama ada e-mel wujud atau tidak.
        return back()->with('success',
            'Jika e-mel itu berdaftar, kami telah menghantar pautan set semula kata laluan.');
    }

    public function reset(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [], ['password' => 'kata laluan', 'email' => 'e-mel']);

        $status = PasswordBroker::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, string $password) {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            },
        );

        if ($status !== PasswordBroker::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => 'Pautan set semula tidak sah atau telah tamat tempoh.',
            ]);
        }

        return redirect()->route('login')->with('success', 'Kata laluan berjaya dikemas kini. Sila log masuk.');
    }
}
