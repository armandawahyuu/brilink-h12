<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Cache;

class ConversationState
{
    public static function get(string $chatId): ?array
    {
        return Cache::get("telegram_state_{$chatId}");
    }

    public static function set(string $chatId, array $state, int $ttl = 300): void
    {
        Cache::put("telegram_state_{$chatId}", $state, $ttl);
    }

    public static function clear(string $chatId): void
    {
        Cache::forget("telegram_state_{$chatId}");
    }
}
