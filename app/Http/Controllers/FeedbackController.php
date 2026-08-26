<?php

namespace App\Http\Controllers;

use App\Models\Registration;

class FeedbackController extends Controller
{
    /** Borang maklum balas — dicapai melalui pautan selamat pada tiket. */
    public function show(Registration $registration)
    {
        $registration->load(['event.venue', 'event.speaker', 'feedback']);

        return view('public.feedback', [
            'registration' => $registration,
            'event' => $registration->event,
        ]);
    }
}
