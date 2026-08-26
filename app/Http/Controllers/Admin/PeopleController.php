<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\LoginLink;
use App\Models\Registration;
use App\Models\State;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\Notifications\NotificationRecipient;
use App\Services\Notifications\NotificationService;
use Illuminate\Http\Request;

class PeopleController extends Controller
{
    public function mobilizers(Request $request)
    {
        $users = User::role(UserRole::Penggerak)
            ->with(['state', 'district', 'mobilizerProfile'])
            ->withCount(['applications', 'mobilizedEvents'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q').'%';
                $q->where(fn ($w) => $w->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term));
            })
            ->orderBy('name')
            ->paginate(20)->withQueryString();

        return view('admin.mobilizers', ['users' => $users]);
    }

    public function mobilizer(User $user)
    {
        abort_unless($user->canAccessPenggerak(), 404);

        return view('admin.mobilizer', [
            'user' => $user->load([
                'state', 'district', 'mobilizerProfile',
                'applications.state', 'applications.event',
                'mobilizedEvents.venue', 'mobilizedEvents.state',
            ]),
        ]);
    }

    /**
     * Direktori peserta. Nombor telefon penuh sengaja tidak dipaparkan
     * di sini — hanya pada halaman program yang berkaitan.
     */
    public function participants(Request $request)
    {
        $registrations = Registration::with(['event.state', 'event.venue', 'state', 'attendance', 'payment'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q').'%';
                $q->where(fn ($w) => $w->where('name', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('reference_no', 'like', $term));
            })
            ->when($request->filled('negeri'),
                fn ($q) => $q->whereHas('state', fn ($s) => $s->where('slug', $request->string('negeri'))))
            ->when($request->filled('status'),
                fn ($q) => $q->where('status', $request->string('status')))
            ->latest('registered_at')
            ->paginate(25)->withQueryString();

        return view('admin.participants', [
            'registrations' => $registrations,
            'states' => State::orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Menghantar pautan log masuk kepada Penggerak yang tersekat.
     *
     * Akaun mereka dicipta automatik daripada permohonan tanpa kata laluan
     * yang diketahui, jadi ini selalunya satu-satunya jalan masuk.
     */
    public function sendLoginLink(User $user,
        NotificationService $notifications)
    {
        // Hanya akaun Penggerak. Admin tidak boleh mencipta pautan masuk
        // untuk akaun admin lain.
        abort_unless($user->isPenggerak(), 404);

        $link = LoginLink::issueFor($user, 'whatsapp', request()->ip());

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

        ActivityLogger::log('auth.login_link_sent', $user,
            "Pautan log masuk dihantar kepada {$user->name} oleh ".auth()->user()->name.'.');

        return back()->with('success',
            "Pautan log masuk dihantar kepada {$user->name}. Sah selama 30 minit.");
    }
}
