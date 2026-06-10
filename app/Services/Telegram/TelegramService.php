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

    public function deleteWebhook(bool $dropPendingUpdates = false): array
    {
        $token = $this->getToken();
        if (!$token) return ['ok' => false, 'description' => 'Token not set'];

        $response = Http::post("https://api.telegram.org/bot{$token}/deleteWebhook", [
            'drop_pending_updates' => $dropPendingUpdates,
        ]);

        return $response->json();
    }

    public function getUpdates(int $offset = 0, int $timeout = 25): array
    {
        $token = $this->getToken();
        if (!$token) return ['ok' => false, 'description' => 'Token not set'];

        $response = Http::timeout($timeout + 5)->get("https://api.telegram.org/bot{$token}/getUpdates", [
            'offset' => $offset,
            'timeout' => $timeout,
            'allowed_updates' => json_encode(['message', 'callback_query']),
        ]);

        return $response->json();
    }

    public function answerCallbackQuery(string $callbackQueryId, string $text = ''): void
    {
        $token = $this->getToken();
        if (!$token) return;

        $payload = [
            'callback_query_id' => $callbackQueryId,
        ];

        if ($text !== '') {
            $payload['text'] = $text;
        }

        Http::post("https://api.telegram.org/bot{$token}/answerCallbackQuery", $payload);
    }
}
