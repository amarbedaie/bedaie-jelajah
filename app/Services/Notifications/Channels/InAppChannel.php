<?php

namespace App\Services\Notifications\Channels;

use App\Notifications\SystemNotification;
use App\Services\Notifications\NotificationRecipient;

class InAppChannel implements NotificationChannel
{
    public function key(): string
    {
        return 'inapp';
    }

    public function supports(NotificationRecipient $recipient): bool
    {
        return $recipient->user !== null;
    }

    public function send(NotificationRecipient $recipient, string $subject, string $body, array $context = []): array
    {
        $recipient->user->notify(new SystemNotification(
            title: $subject ?: 'Pemberitahuan BeDaie Jelajah',
            body: $body,
            url: $context['url'] ?? null,
            icon: $context['icon'] ?? 'bell',
        ));

        return ['status' => 'sent', 'error' => null, 'address' => (string) $recipient->user->id];
    }
}
