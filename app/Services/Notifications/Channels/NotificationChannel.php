<?php

namespace App\Services\Notifications\Channels;

use App\Services\Notifications\NotificationRecipient;

interface NotificationChannel
{
    public function key(): string;

    public function supports(NotificationRecipient $recipient): bool;

    /** @return array{status:string, error:?string, address:?string} */
    public function send(NotificationRecipient $recipient, string $subject, string $body, array $context = []): array;
}
