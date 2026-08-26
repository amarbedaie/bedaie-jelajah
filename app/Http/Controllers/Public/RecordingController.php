<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\EventRecording;
use App\Models\Registration;
use App\Models\RecordingView;
use Illuminate\Http\Request;

/**
 * Rakaman program. Diakses melalui token tiket peserta — tiada log masuk
 * diperlukan, sama seperti tiket dan sijil mereka.
 */
class RecordingController extends Controller
{
    public function index(string $token)
    {
        $registration = Registration::where('public_token', $token)
            ->with(['event.speaker', 'event.venue', 'attendance'])
            ->firstOrFail();

        $recordings = $registration->event->recordings()->published()->get();

        abort_if($recordings->isEmpty() && ! $registration->event->hasEnded(), 404);

        return view('public.recordings', [
            'registration' => $registration,
            'event' => $registration->event,
            'recordings' => $recordings,
        ]);
    }

    public function show(string $token, EventRecording $recording, Request $request)
    {
        $registration = Registration::where('public_token', $token)
            ->with(['event.speaker', 'attendance'])
            ->firstOrFail();

        abort_unless($recording->event_id === $registration->event_id, 404);
        abort_unless($recording->viewableBy($registration), 403,
            $recording->lockedReason($registration) ?? 'Rakaman ini tidak tersedia untuk anda.');

        // Rekod tontonan sekali sehari supaya kiraan bermakna.
        RecordingView::firstOrCreate([
            'event_recording_id' => $recording->id,
            'registration_id' => $registration->id,
            'viewed_at' => now()->startOfDay(),
        ], ['user_id' => $registration->user_id]);

        return view('public.recording', [
            'registration' => $registration,
            'event' => $registration->event,
            'recording' => $recording,
            'others' => $recording->event->recordings()->published()
                ->whereKeyNot($recording->id)->get(),
        ]);
    }
}
