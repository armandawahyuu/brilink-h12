<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\TelegramWebhookController;
use App\Services\Telegram\TelegramService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('telegram:poll {--once : Poll once and exit} {--sleep=1 : Sleep seconds after errors}', function (TelegramService $telegram) {
    $this->info('Starting Telegram polling for BRILink H12...');

    $telegram->deleteWebhook(false);

    do {
        $offset = (int) Cache::get('telegram_poll_offset', 0);
        $result = $telegram->getUpdates($offset);

        if (!($result['ok'] ?? false)) {
            $this->warn('getUpdates failed: ' . ($result['description'] ?? 'Unknown error'));
            sleep((int) $this->option('sleep'));
            continue;
        }

        foreach ($result['result'] ?? [] as $update) {
            Cache::put('telegram_poll_offset', ((int) $update['update_id']) + 1);

            $request = Request::create(
                '/webhook/telegram',
                'POST',
                [],
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode($update)
            );

            app(TelegramWebhookController::class)->handle($request);
        }
    } while (!$this->option('once'));
})->purpose('Poll Telegram updates when HTTPS webhook is not available');
