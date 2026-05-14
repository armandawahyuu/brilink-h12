<?php

namespace App\Services\Telegram;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class TelegramService
{
    public function getToken(): ?string
    {
        return Setting::get('telegram_bot_token');
    }

    public function getAllowedChatId(): ?string
    {
        return Setting::get('telegram_chat_id');
    }

    public function sendMessage(int|string $chatId, string $text, array $replyMarkup = []): void
    {
        $token = $this->getToken();
        if (!$token) return;

        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];

        if (!empty($replyMarkup)) {
            $payload['reply_markup'] = json_encode($replyMarkup);
        }

        Http::post("https://api.telegram.org/bot{$token}/sendMessage", $payload);
    }

    public function setWebhook(string $url): array
    {
        $token = $this->getToken();
        if (!$token) return ['ok' => false, 'description' => 'Token not set'];

        $response = Http::post("https://api.telegram.org/bot{$token}/setWebhook", [
            'url' => $url,
        ]);

        return $response->json();
    }
}
