<?php

namespace App\Http\Controllers;

use App\Enums\RegistrationStatus;
use App\Models\Registration;
use App\Services\QrCodeService;
use App\Services\RegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Tiket peserta boleh dicapai melalui pautan selamat (public_token)
 * tanpa perlu log masuk — supaya peserta yang mendaftar tanpa akaun
 * tetap boleh menunjukkan QR kehadiran mereka.
 */
class TicketController extends Controller
{
    public function show(Registration $registration, QrCodeService $qr)
    {
        $registration->load(['event.venue', 'event.state', 'event.speaker', 'guests', 'payment', 'attendance']);

        $token = $registration->activeQrToken();

        return view('public.ticket', [
            'registration' => $registration,
            'event' => $registration->event,
            'qrSvg' => $token ? $qr->svg($token->token, 260) : null,
        ]);
    }

    public function cancelForm(Registration $registration)
    {
        return view('public.ticket-cancel', [
            'registration' => $registration->load('event.venue'),
        ]);
    }

    public function cancel(Request $request, Registration $registration, RegistrationService $service): RedirectResponse
    {
        if (in_array($registration->status, [RegistrationStatus::Dibatalkan, RegistrationStatus::Ditolak], true)) {
            return redirect()->route('tiket.show', $registration->public_token)
                ->with('info', 'Pendaftaran ini telah pun dibatalkan.');
        }

        if ($registration->hasAttended()) {
            return redirect()->route('tiket.show', $registration->public_token)
                ->with('warning', 'Kehadiran anda telah direkodkan, jadi pendaftaran tidak boleh dibatalkan.');
        }

        $service->cancel($registration, $request->string('reason')->value() ?: 'Dibatalkan oleh peserta.');

        return redirect()->route('tiket.show', $registration->public_token)
            ->with('success', 'Pendaftaran anda telah dibatalkan. Tempat anda dilepaskan kepada peserta lain.');
    }

    /** Fail .ics supaya peserta boleh tambah ke kalendar telefon. */
    public function calendar(Registration $registration): Response
    {
        $event = $registration->event;

        $fmt = fn ($date) => $date?->clone()->utc()->format('Ymd\THis\Z');
        $escape = fn (?string $text) => str_replace(
            ["\\", "\n", ',', ';'],
            ['\\\\', '\\n', '\,', '\;'],
            (string) $text,
        );

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//BeDaie Jelajah//MS',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:'.$registration->public_token.'@bedaie.com.my',
            'DTSTAMP:'.$fmt(now()),
            'DTSTART:'.$fmt($event->starts_at),
            'DTEND:'.$fmt($event->ends_at ?? $event->starts_at->clone()->addHours(2)),
            'SUMMARY:'.$escape($event->title),
            'DESCRIPTION:'.$escape($event->theme."\n\nTiket: ".$registration->ticketUrl()),
            'LOCATION:'.$escape($event->locationLabel()),
            'URL:'.$event->publicUrl(),
            'BEGIN:VALARM',
            'TRIGGER:-PT2H',
            'ACTION:DISPLAY',
            'DESCRIPTION:'.$escape('Peringatan: '.$event->title),
            'END:VALARM',
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        return response(implode("\r\n", $lines), 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="bedaie-jelajah-'.$event->short_code.'.ics"',
        ]);
    }
}
