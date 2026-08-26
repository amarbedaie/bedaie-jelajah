<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $notifications = $user->notifications()->paginate(20);

        $user->unreadNotifications->markAsRead();

        return view('shared.notifications', [
            'notifications' => $notifications,
            'nav' => 'peserta',
        ]);
    }
}
