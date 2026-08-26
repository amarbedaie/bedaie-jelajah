<?php

namespace App\Services\Notifications\Channels;

use App\Mail\TemplatedMail;
use App\Services\Notifications\NotificationRecipient;
use Illuminate\Support\Facades\Mail;
use Throwable;

class MailChannel implements NotificationChannel
{
    public function key(): string
    {
        return 'email';
    }

    public function supports(NotificationRecipient $recipient): bool
    {
        return filter_var($recipient->email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function send(NotificationRecipient $recipient, string $subject, string $body, array $context = []): array
    {
        try {
            Mail::to($recipient->email, $recipient->name)->send(
                new TemplatedMail(
                    subjectLine: $subject ?: 'BeDaie Jelajah',
                    bodyText: $body,
                    recipientName: $recipient->name,
                    actionUrl: $context['url'] ?? null,
                    actionLabel: $context['action_label'] ?? null,
                )
            );

            return ['status' => 'sent', 'error' => null, 'address' => $recipient->email];
        } catch (Throwable $e) {
            return ['status' => 'failed', 'error' => $e->getMessage(), 'address' => $recipient->email];
        }
    }
}
