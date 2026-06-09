<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TelegramWebhookController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::resource('transactions', TransactionController::class)->except(['show']);

    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    Route::put('settings/saldo', [SettingController::class, 'updateSaldo'])->name('settings.update.saldo');
    Route::put('settings/password', [SettingController::class, 'updatePassword'])->name('settings.update.password');
    Route::post('settings/telegram/set-webhook', [SettingController::class, 'setWebhook'])->name('settings.telegram.webhook');
});

Route::post('webhook/telegram', [TelegramWebhookController::class, 'handle'])->name('telegram.webhook')->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
