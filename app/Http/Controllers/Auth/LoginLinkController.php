<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginLink;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\ApplicationService;
use App\Services\Notifications\NotificationRecipient;
use App\Services\Notifications\NotificationService;
use App\Support\Phone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * Log masuk melalui pautan sekali-guna yang dihantar ke WhatsApp.
 *
 * Diperlukan kerana akaun Penggerak dicipta secara automatik apabila
 * permohonan dihantar — tanpa kata laluan yang diketahui pemohon, dan
 * selalunya tanpa e-mel sebenar. WhatsApp ialah saluran utama mereka.
 */
class LoginLinkController extends Controller
{
    public function create()
    {
        return view('auth.login-link');
    }

    public function store(Request $request, NotificationService $notifications)
    {
        $data = $request->validate([
            'contact' => ['required', 'string', 'max:180'],
        ], [], ['contact' => 'nombor WhatsApp atau e-mel']);

        $key = 'pautan-masuk:'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'contact' => 'Terlalu banyak percubaan. Sila cuba sebentar lagi.',
            ]);
        }

        RateLimiter::hit($key, 900);

        $user = $this->findUser($data['contact']);

        if ($user && ! $user->is_active) {
            $user = null;
        }

        // Jawapan yang sama sama ada akaun wujud atau tidak — mengelak
        // orang luar menguji nombor mana yang berdaftar dengan kami.
        if ($user) {
            $link = LoginLink::issueFor($user, 'whatsapp', $request->ip());

            $notifications->queue(
                'pautan_log_masuk',
                NotificationRecipient::fromUser($user),
                [
                    'participant_name' => $user->name,
                    'registration_link' => $link->url(),
                    'status' => '30 minit',
                ],
                $user,
                ['url' => $link->url(), 'action_label' => 'Log Masuk'],
            );

            ActivityLogger::log('auth.login_link_requested', $user,
                "Pautan log masuk dihantar kepada {$user->name}.");
        }

        return back()->with('success',
            'Jika nombor atau e-mel itu berdaftar, kami telah menghantar pautan log masuk. '
            .'Pautan sah selama 30 minit.');
    }

    public function consume(string $token)
    {
        $link = LoginLink::with('user')->where('token', $token)->first();

        // Akaun yang dinyahaktifkan tidak boleh masuk melalui pintu ini pun.
        if ($link && ! $link->user?->is_active) {
            $link->consume();
            $link = null;
        }

        if (! $link || ! $link->isUsable()) {
            return redirect()->route('masuk.pautan')
                ->withErrors(['contact' => 'Pautan ini telah tamat tempoh atau sudah digunakan. Sila minta pautan baharu.']);
        }

        $link->consume();

        // Peranti mungkin masih log masuk sebagai orang lain — telefon
        // dikongsi antara ahli jawatankuasa adalah perkara biasa.
        if (Auth::check() && Auth::id() !== $link->user_id) {
            Auth::logout();
        }

        Auth::login($link->user, remember: true);
        request()->session()->regenerate();

        // Pemilikan kini terbukti, jadi pemohon boleh dinaikkan dengan selamat.
        ApplicationService::promoteIfApplicant($link->user);

        ActivityLogger::log('auth.login_link_used', $link->user,
            "{$link->user->name} log masuk melalui pautan.");

        return redirect()->route($link->user->homeRoute())
            ->with('success', 'Selamat datang, '.$link->user->firstName().'.');
    }

    /** Cari melalui nombor telefon (dinormalkan) atau e-mel. */
    private function findUser(string $contact): ?User
    {
        $contact = trim($contact);

        if (str_contains($contact, '@')) {
            return User::where('email', $contact)->first();
        }

        $phone = Phone::normalise($contact);

        return $phone ? User::where('phone', $phone)->first() : null;
    }
}
