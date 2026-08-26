<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\Registration;

class CheckInResult
{
    public const OK = 'ok';

    public const CHECKED_IN = 'checked_in';

    public const DUPLICATE = 'duplicate';

    public const FAIL = 'fail';

    private function __construct(
        public string $outcome,
        public ?Registration $registration = null,
        public ?string $message = null,
        public ?AttendanceRecord $record = null,
    ) {}

    public static function ok(Registration $registration): self
    {
        return new self(self::OK, $registration);
    }

    public static function checkedIn(Registration $registration, AttendanceRecord $record): self
    {
        return new self(self::CHECKED_IN, $registration, 'Kehadiran direkodkan.', $record);
    }

    public static function duplicate(Registration $registration, string $message, ?AttendanceRecord $record = null): self
    {
        return new self(self::DUPLICATE, $registration, $message, $record);
    }

    public static function fail(string $message, ?Registration $registration = null): self
    {
        return new self(self::FAIL, $registration, $message);
    }

    public function successful(): bool
    {
        return in_array($this->outcome, [self::OK, self::CHECKED_IN], true);
    }

    public function toArray(): array
    {
        return [
            'outcome' => $this->outcome,
            'message' => $this->message,
            'registration' => $this->registration ? [
                'name' => $this->registration->name,
                'reference_no' => $this->registration->reference_no,
                'phone' => $this->registration->maskedPhone(),
                'guests' => $this->registration->guests_count,
                'status' => $this->registration->status->label(),
            ] : null,
            'checked_in_at' => $this->record?->checked_in_at?->format('g:ia'),
        ];
    }
}
