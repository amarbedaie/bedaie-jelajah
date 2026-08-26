<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    /** Penggerak hanya melihat permohonan sendiri. */
    public function view(User $user, Application $application): bool
    {
        return $user->isAdmin()
            || ($application->user_id !== null && $application->user_id === $user->id);
    }

    public function manage(User $user): bool
    {
        return $user->isAdmin();
    }
}
