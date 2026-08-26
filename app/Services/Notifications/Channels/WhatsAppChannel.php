<?php

namespace App\Services\Notifications\Channels;

use App\Services\Notifications\NotificationRecipient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Penghantaran WhatsApp secara modular.
 *
 * driver=log   → hanya direkod (default pembangunan, tiada kredensial diperlukan)
 * driver=waha  → WAHA self-hosted (POST /api/sendText)
 * driver=cloud → WhatsApp Cloud API rasmi Meta
 */
class WhatsAppChannel implements NotificationChannel
{
    public function key(): string
    {
        return 'whatsapp';
    }

    public function supports(NotificationRecipient $recipient): bool
    {
        return ! empty($this->normalise($recipient->phone));
    }

    public function send(NotificationRecipient $recipient, string $subject, string $body, array $context = []): array
    {
        $phone = $this->normalise($recipient->phone);
        $message = trim(($subject ? "*{$subject}*\n\n" : '').$body);

        if (! config('jelajah.whatsapp.enabled')) {
            Log::info('[WhatsApp dimatikan] mesej tidak dihantar', ['to' => $phone, 'body' => $message]);

            return ['status' => 'skipped', 'error' => 'Saluran WhatsApp dimatikan (WHATSAPP_ENABLED=false)', 'address' => $phone];
        }

        try {
            return match (config('jelajah.whatsapp.driver')) {
                'waha' => $this->sendViaWaha($phone, $message),
                'cloud' => $this->sendViaCloudApi($phone, $message),
                default => $this->sendViaLog($phone, $message),
            };
        } catch (Throwable $e) {
            return ['status' => 'failed', 'error' => $e->getMessage(), 'address' => $phone];
        }
    }

    private function sendViaLog(string $phone, string $message): array
    {
        Log::channel(config('logging.default'))->info('[WhatsApp] '.$phone."\n".$message);

        return ['status' => 'sent', 'error' => null, 'address' => $phone];
    }

    private function sendViaWaha(string $phone, string $message): array
    {
        $base = rtrim((string) config('jelajah.whatsapp.base_url'), '/');

        $response = Http::timeout(20)
            ->withHeaders(array_filter(['X-Api-Key' => config('jelajah.whatsapp.api_key')]))
            ->post($base.'/api/sendText', [
                'session' => config('jelajah.whatsapp.session', 'default'),
                'chatId' => $phone.'@c.us',
                'text' => $message,
            ]);

        return $response->successful()
            ? ['status' => 'sent', 'error' => null, 'address' => $phone]
            : ['status' => 'failed', 'error' => $response->body(), 'address' => $phone];
    }

    private function sendViaCloudApi(string $phone, string $message): array
    {
        $base = rtrim((string) config('jelajah.whatsapp.base_url'), '/');

        $response = Http::timeout(20)
            ->withToken((string) config('jelajah.whatsapp.api_key'))
            ->post($base.'/messages', [
                'messaging_product' => 'whatsapp',
                'to' => $phone,
                'type' => 'text',
                'text' => ['body' => $message],
            ]);

        return $response->successful()
            ? ['status' => 'sent', 'error' => null, 'address' => $phone]
            : ['status' => 'failed', 'error' => $response->body(), 'address' => $phone];
    }

    /** Menormalkan nombor Malaysia kepada format antarabangsa tanpa tanda. */
    public function normalise(?string $phone): ?string
    {
        return \App\Support\Phone::normalise($phone);
    }
}
