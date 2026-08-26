<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Notifications\NotificationRecipient;
use App\Services\Notifications\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [30, 120, 300];

    public function __construct(
        public string $templateKey,
        public array $recipient,
        public array $data = [],
        public ?array $subject = null,
        public array $context = [],
    ) {}

    public function handle(NotificationService $notifications): void
    {
        $user = ! empty($this->recipient['user_id']) ? User::find($this->recipient['user_id']) : null;

        $recipient = new NotificationRecipient(
            name: $this->recipient['name'] ?? 'Sahabat BeDaie',
            email: $this->recipient['email'] ?? null,
            phone: $this->recipient['phone'] ?? null,
            user: $user,
        );

        $subject = null;
        if ($this->subject && class_exists($this->subject['type'])) {
            $subject = $this->subject['type']::find($this->subject['id']);
        }

        $notifications->sendNow($this->templateKey, $recipient, $this->data, $subject, $this->context);
    }
}
