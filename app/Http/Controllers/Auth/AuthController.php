<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Support\Phone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [], [
            'email' => 'e-mel atau nombor telefon',
            'password' => 'kata laluan',
        ]);

        // Pengguna boleh log masuk menggunakan e-mel atau nombor WhatsApp.
        $field = filter_var($data['email'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $identifier = $field === 'phone' ? Phone::normalise($data['email']) : $data['email'];

        if (! Auth::attempt([$field => $identifier, 'password' => $data['password']], $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Maklumat log masuk tidak sepadan dengan rekod kami.',
            ]);
        }

        $request->session()->regenerate();

        ActivityLogger::log('auth.login', Auth::user(), Auth::user()->name.' log masuk.');

        return redirect()->intended(route(Auth::user()->homeRoute()));
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:180', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'privacy' => ['accepted'],
        ], [
            'privacy.accepted' => 'Sila setuju dengan polisi privasi untuk meneruskan.',
        ], [
            'name' => 'nama penuh',
            'phone' => 'nombor WhatsApp',
            'email' => 'e-mel',
            'password' => 'kata laluan',
        ]);

        $phone = Phone::normalise($data['phone']);

        if (User::where('phone', $phone)->exists()) {
            throw ValidationException::withMessages([
                'phone' => 'Nombor ini telah didaftarkan. Sila log masuk.',
            ]);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $phone,
            'password' => $data['password'],
            'password_set_at' => now(),
            'role' => UserRole::Peserta,
        ]);

        Auth::login($user, true);
        $request->session()->regenerate();

        ActivityLogger::log('auth.registered', $user, "{$user->name} mencipta akaun.");

        return redirect()->route('peserta.dashboard')
            ->with('success', 'Selamat datang ke BeDaie Jelajah, '.$user->firstName().'.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Anda telah log keluar.');
    }
}
