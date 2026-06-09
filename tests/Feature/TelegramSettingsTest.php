<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Services\Telegram\TelegramService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TelegramSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_telegram_service_uses_server_config_for_bot_credentials(): void
    {
        config([
            'services.telegram.bot_token' => 'server-token',
            'services.telegram.bot_username' => 'server_bot',
            'services.telegram.allowed_chat_id' => '12345',
        ]);

        $service = app(TelegramService::class);

        $this->assertSame('server-token', $service->getToken());
        $this->assertSame('server_bot', $service->getUsername());
        $this->assertSame('12345', $service->getAllowedChatId());
    }

    public function test_settings_form_does_not_store_bot_token(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('settings.update'), [
            'telegram_bot_token' => 'should-not-be-saved',
            'telegram_bot_username' => 'should-not-be-saved',
            'telegram_chat_id' => '98765',
            'ai_api_key' => 'should-not-be-saved',
        ])->assertRedirect(route('settings.index'));

        $this->assertNull(Setting::get('telegram_bot_token'));
        $this->assertNull(Setting::get('telegram_bot_username'));
        $this->assertSame('98765', Setting::get('telegram_chat_id'));
        $this->assertNull(Setting::get('ai_api_key'));
    }
}
