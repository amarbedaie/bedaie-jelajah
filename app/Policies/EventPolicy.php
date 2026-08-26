<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    /** Penggerak hanya melihat program yang dia gerakkan. */
    public function view(User $user, Event $event): bool
    {
        return $user->isAdmin() || $this->mobilizes($user, $event);
    }

    /** Kehadiran boleh diuruskan oleh admin atau Penggerak program berkenaan. */
    public function checkIn(User $user, Event $event): bool
    {
        return $user->isAdmin() || $this->mobilizes($user, $event);
    }

    public function manage(User $user): bool
    {
        return $user->isAdmin();
    }

    /** Senarai peserta penuh (termasuk nombor telefon) — admin sahaja. */
    public function viewParticipantContacts(User $user, Event $event): bool
    {
        return $user->isAdmin();
    }

    private function mobilizes(User $user, Event $event): bool
    {
        return $event->mobilizers()->whereKey($user->id)->exists();
    }
}
