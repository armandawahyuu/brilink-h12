<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\Telegram\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'telegram_chat_id' => Setting::get('telegram_chat_id'),
            'saldo_kas_awal' => Setting::get('saldo_kas_awal', '0'),
            'saldo_brilink_awal' => Setting::get('saldo_brilink_awal', '0'),
            'tanggal_mulai' => Setting::get('tanggal_mulai', date('Y-m-d')),
        ];

        $telegram = [
            'username' => config('services.telegram.bot_username'),
            'configured' => (bool) config('services.telegram.bot_token'),
        ];

        $ai = [
            'configured' => (bool) config('services.ai.api_key'),
            'base_url' => config('services.ai.base_url'),
            'ocr_model' => config('services.ai.ocr_model'),
        ];

        return view('settings.index', compact('settings', 'telegram', 'ai'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'telegram_chat_id' => 'nullable|string|max:255',
        ]);

        Setting::set('telegram_chat_id', $validated['telegram_chat_id'], 'telegram');

        return redirect()->route('settings.index')
            ->with('success', 'Pengaturan berhasil disimpan.');
    }

    public function updateSaldo(Request $request)
    {
        $validated = $request->validate([
            'saldo_kas_awal' => 'required|numeric|min:0',
            'saldo_brilink_awal' => 'required|numeric|min:0',
            'tanggal_mulai' => 'required|date',
        ]);

        Setting::set('saldo_kas_awal', $validated['saldo_kas_awal'], 'saldo');
        Setting::set('saldo_brilink_awal', $validated['saldo_brilink_awal'], 'saldo');
        Setting::set('tanggal_mulai', $validated['tanggal_mulai'], 'saldo');

        return redirect()->route('settings.index')
            ->with('success', 'Saldo awal berhasil disimpan.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(10)->letters()->numbers()],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('settings.index')
            ->with('success', 'Password admin berhasil diganti.');
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
