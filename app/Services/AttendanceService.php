<?php

namespace App\Services;

use App\Enums\AttendanceMethod;
use App\Enums\RegistrationStatus;
use App\Models\AttendanceRecord;
use App\Models\Event;
use App\Models\QrToken;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function __construct(private RegistrationService $registrations) {}

    /**
     * Mencari pendaftaran daripada token QR yang diimbas.
     * Token tidak mendedahkan ID database.
     */
    public function resolveToken(string $token, Event $event): CheckInResult
    {
        $qrToken = QrToken::where('token', trim(strtoupper($token)))
            ->where('tokenable_type', Registration::class)
            ->where('purpose', 'checkin')
            ->first();

        if (! $qrToken) {
            return CheckInResult::fail('Kod QR tidak dikenali. Sila cuba carian manual.');
        }

        if (! $qrToken->isUsable()) {
            return CheckInResult::fail('Kod QR ini telah dibatalkan.');
        }

        $registration = $qrToken->tokenable;

        if (! $registration) {
            return CheckInResult::fail('Rekod pendaftaran tidak dijumpai.');
        }

        if ($registration->event_id !== $event->id) {
            return CheckInResult::fail('Kod QR ini untuk program lain: '.$registration->event->title);
        }

        return CheckInResult::ok($registration);
    }

    /** Merekod kehadiran. Check-in berganda ditolak. */
    public function checkIn(
        Registration $registration,
        ?User $actor = null,
        AttendanceMethod $method = AttendanceMethod::Qr,
        ?int $guestsPresent = null,
    ): CheckInResult {
        if (in_array($registration->status, [RegistrationStatus::Dibatalkan, RegistrationStatus::Ditolak], true)) {
            return CheckInResult::fail("Pendaftaran {$registration->name} telah dibatalkan.", $registration);
        }

        return DB::transaction(function () use ($registration, $actor, $method, $guestsPresent) {
            $existing = AttendanceRecord::where('registration_id', $registration->id)->lockForUpdate()->first();

            if ($existing) {
                return CheckInResult::duplicate(
                    $registration,
                    'Sudah check-in pada '.$existing->checked_in_at->format('g:ia').'.',
                    $existing,
                );
            }

            // Peserta senarai menunggu yang hadir dinaikkan taraf secara automatik.
            if ($registration->status === RegistrationStatus::SenaraiMenunggu) {
                $registration->update([
                    'status' => RegistrationStatus::Disahkan,
                    'confirmed_at' => now(),
                ]);
            }

            $record = AttendanceRecord::create([
                'registration_id' => $registration->id,
                'event_id' => $registration->event_id,
                'checked_in_at' => now(),
                'checked_in_by' => $actor?->id,
                'method' => $method,
                'guests_present' => (int) ($guestsPresent ?? $registration->guests_count ?? 0),
            ]);

            $this->registrations->refreshCounts($registration->event);

            ActivityLogger::log(
                'attendance.checked_in',
                $registration,
                "{$registration->name} hadir ({$method->label()}).",
            );

            return CheckInResult::checkedIn($registration, $record);
        });
    }

    /** Carian manual apabila kamera tidak dapat digunakan. */
    public function search(Event $event, string $term): \Illuminate\Support\Collection
    {
        $term = trim($term);

        if (mb_strlen($term) < 3) {
            return collect();
        }

        $digits = preg_replace('/\D/', '', $term);

        return $event->registrations()
            ->with('attendance')
            ->where(function ($q) use ($term, $digits) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('reference_no', 'like', "%{$term}%");

                if ($digits !== '') {
                    $q->orWhere('phone', 'like', "%{$digits}%");
                }
            })
            ->orderBy('name')
            ->limit(25)
            ->get();
    }

    /** Kiraan langsung untuk skrin scanner. */
    public function liveStats(Event $event): array
    {
        $registered = $event->registrations()->active()->count();
        $attended = $event->attendanceRecords()->count();
        $walkIn = $event->registrations()->where('source', 'walk_in')->count();

        return [
            'berdaftar' => $registered,
            'hadir' => $attended,
            'belum_hadir' => max(0, $registered - $attended),
            'walk_in' => $walkIn,
            'senarai_menunggu' => $event->registrations()->waitlisted()->count(),
        ];
    }
}
