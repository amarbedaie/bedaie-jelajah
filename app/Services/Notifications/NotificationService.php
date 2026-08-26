<?php

namespace App\Services\Notifications;

use App\Jobs\SendNotificationJob;
use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use App\Services\Notifications\Channels\InAppChannel;
use App\Services\Notifications\Channels\MailChannel;
use App\Services\Notifications\Channels\NotificationChannel;
use App\Services\Notifications\Channels\WhatsAppChannel;
use Illuminate\Database\Eloquent\Model;

class NotificationService
{
    /** @var array<string, NotificationChannel> */
    private array $channels = [];

    public function __construct()
    {
        foreach ([new InAppChannel, new MailChannel, new WhatsAppChannel] as $channel) {
            $this->channels[$channel->key()] = $channel;
        }
    }

    public function channel(string $key): ?NotificationChannel
    {
        return $this->channels[$key] ?? null;
    }

    /** Menghantar melalui queue — dipanggil dari kod aplikasi. */
    public function queue(string $templateKey, NotificationRecipient $recipient, array $data = [], ?Model $subject = null, array $context = []): void
    {
        SendNotificationJob::dispatch(
            $templateKey,
            [
                'name' => $recipient->name,
                'email' => $recipient->email,
                'phone' => $recipient->phone,
                'user_id' => $recipient->user?->id,
            ],
            $data,
            $subject ? ['type' => $subject::class, 'id' => $subject->getKey()] : null,
            $context,
        );
    }

    /** Penghantaran segera pada semua saluran yang mempunyai template aktif. */
    public function sendNow(string $templateKey, NotificationRecipient $recipient, array $data = [], ?Model $subject = null, array $context = []): array
    {
        $data = array_merge($this->globalData(), $data);
        $templates = NotificationTemplate::active()->where('key', $templateKey)->get();

        if ($templates->isEmpty()) {
            NotificationLog::create([
                'template_key' => $templateKey,
                'channel' => 'sistem',
                'recipient_name' => $recipient->name,
                'status' => 'failed',
                'error' => "Template '{$templateKey}' tidak dijumpai atau tidak aktif.",
            ]);

            return [];
        }

        $results = [];

        foreach ($templates as $template) {
            $channel = $this->channel($template->channel);

            if (! $channel || ! $channel->supports($recipient)) {
                continue;
            }

            $rendered = $template->render($data);
            $result = $channel->send($recipient, $rendered['subject'], $rendered['body'], $context);

            NotificationLog::create([
                'template_key' => $templateKey,
                'channel' => $template->channel,
                'notifiable_type' => $subject ? $subject::class : null,
                'notifiable_id' => $subject?->getKey(),
                'recipient_name' => $recipient->name,
                'recipient_address' => $result['address'],
                'subject' => $rendered['subject'],
                'body' => $rendered['body'],
                'status' => $result['status'],
                'error' => $result['error'],
                'sent_at' => $result['status'] === 'sent' ? now() : null,
            ]);

            $results[$template->channel] = $result;
        }

        return $results;
    }

    /** Nilai yang sentiasa tersedia kepada setiap template. */
    private function globalData(): array
    {
        return [
            'brand' => config('jelajah.brand'),
            'support_phone' => config('jelajah.support.phone'),
            'support_email' => config('jelajah.support.email'),
            'tagline' => config('jelajah.tagline'),
        ];
    }
}
