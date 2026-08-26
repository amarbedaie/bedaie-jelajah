<?php

namespace App\Policies;

use App\Models\Registration;
use App\Models\User;

class RegistrationPolicy
{
    public function view(User $user, Registration $registration): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($registration->user_id !== null && $registration->user_id === $user->id) {
            return true;
        }

        return $registration->event->mobilizers()->whereKey($user->id)->exists();
    }

    public function cancel(User $user, Registration $registration): bool
    {
        return $user->isAdmin()
            || ($registration->user_id !== null && $registration->user_id === $user->id);
    }
}
