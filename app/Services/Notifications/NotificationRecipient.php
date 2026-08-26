<?php

namespace App\Services\Notifications;

use App\Models\Registration;
use App\Models\User;

class NotificationRecipient
{
    public function __construct(
        public string $name,
        public ?string $email = null,
        public ?string $phone = null,
        public ?User $user = null,
    ) {}

    public static function fromUser(User $user): self
    {
        // realEmail() supaya akaun tanpa e-mel sebenar jatuh ke WhatsApp.
        return new self($user->name, $user->realEmail(), $user->phone, $user);
    }

    public static function fromRegistration(Registration $registration): self
    {
        return new self(
            $registration->name,
            $registration->email,
            $registration->phone,
            $registration->user,
        );
    }

    public static function make(string $name, ?string $email = null, ?string $phone = null): self
    {
        return new self($name, $email, $phone);
    }
}
