<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\Telegram\TelegramService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'telegram_bot_token' => Setting::get('telegram_bot_token'),
            'telegram_bot_username' => Setting::get('telegram_bot_username'),
            'telegram_chat_id' => Setting::get('telegram_chat_id'),
            'groq_api_key' => Setting::get('groq_api_key'),
        ];

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'telegram_bot_token' => 'nullable|string|max:255',
            'telegram_bot_username' => 'nullable|string|max:255',
            'telegram_chat_id' => 'nullable|string|max:255',
            'groq_api_key' => 'nullable|string|max:255',
        ]);

        Setting::set('telegram_bot_token', $validated['telegram_bot_token'], 'telegram');
        Setting::set('telegram_bot_username', $validated['telegram_bot_username'], 'telegram');
        Setting::set('telegram_chat_id', $validated['telegram_chat_id'], 'telegram');
        Setting::set('groq_api_key', $validated['groq_api_key'], 'ai');

        return redirect()->route('settings.index')
            ->with('success', 'Pengaturan berhasil disimpan.');
    }

    public function setWebhook(TelegramService $telegram)
    {
        $webhookUrl = route('telegram.webhook');

        $result = $telegram->setWebhook($webhookUrl);

        if ($result['ok'] ?? false) {
            return redirect()->route('settings.index')
                ->with('success', 'Webhook Telegram berhasil diset: ' . $webhookUrl);
        }

        return redirect()->route('settings.index')
            ->with('error', 'Gagal set webhook: ' . ($result['description'] ?? 'Unknown error'));
    }
}
