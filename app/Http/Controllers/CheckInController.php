<?php

namespace App\Http\Controllers;

use App\Models\Event;

class CheckInController extends Controller
{
    /** Skrin pengimbas QR — digunakan melalui kamera telefon di pintu masuk. */
    public function scanner(Event $event)
    {
        $this->authorize('checkIn', $event);

        return view('checkin.scanner', [
            'event' => $event->load(['venue', 'state']),
        ]);
    }
}
