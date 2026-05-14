<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use App\Services\GroqOcrService;
use App\Services\Telegram\ConversationState;
use App\Services\Telegram\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TelegramWebhookController extends Controller
{
    private TelegramService $telegram;

    private const TYPE_LABELS = [
        'tarik_tunai' => 'Tarik Tunai',
        'setor_tunai' => 'Setor Tunai',
        'transfer' => 'Transfer',
        'topup_ewallet' => 'Topup E-Wallet',
        'pembayaran' => 'Pembayaran',
        'lainnya' => 'Lainnya',
    ];

    public function __construct(TelegramService $telegram)
    {
        $this->telegram = $telegram;
    }

    public function handle(Request $request): Response
    {
        $update = $request->all();
        $message = $update['message'] ?? $update['callback_query']['message'] ?? null;
        $callbackQuery = $update['callback_query'] ?? null;

        if ($callbackQuery) {
            $chatId = $callbackQuery['message']['chat']['id'];
            $data = $callbackQuery['data'];
            $this->handleCallback($chatId, $data);
            return response('ok');
        }

        if (!$message) {
            return response('ok');
        }

        $chatId = $message['chat']['id'];
        $text = trim($message['text'] ?? '');

        $allowedChatId = $this->telegram->getAllowedChatId();
        if ($allowedChatId && (string)$chatId !== $allowedChatId) {
            $this->telegram->sendMessage($chatId, "Akses ditolak. Chat ID kamu: <code>{$chatId}</code>");
            return response('ok');
        }

        // Handle photo (OCR struk)
        if (isset($update['message']['photo'])) {
            $this->handlePhoto($chatId, $update['message']['photo']);
            return response('ok');
        }

        if ($text === '/start') {
            $this->handleStart($chatId);
        } elseif ($text === '/catat') {
            $this->handleCatat($chatId);
        } elseif ($text === '/batal') {
            $this->handleBatal($chatId);
        } else {
            $this->handleConversation($chatId, $text);
        }

        return response('ok');
    }

    private function handleStart(string $chatId): void
    {
        $msg = "Halo! Ini bot pencatatan transaksi BRILink H12.\n\n";
        $msg .= "Chat ID kamu: <code>{$chatId}</code>\n\n";
        $msg .= "Perintah:\n";
        $msg .= "/catat - Catat transaksi baru (manual)\n";
        $msg .= "/batal - Batalkan input yang sedang berjalan\n\n";
        $msg .= "Atau langsung kirim <b>foto struk</b> untuk input otomatis via AI.";

        $this->telegram->sendMessage($chatId, $msg);
    }

    private function handleCatat(string $chatId): void
    {
        ConversationState::set($chatId, ['step' => 'type']);

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => 'Tarik Tunai', 'callback_data' => 'type:tarik_tunai'],
                    ['text' => 'Setor Tunai', 'callback_data' => 'type:setor_tunai'],
                ],
                [
                    ['text' => 'Transfer', 'callback_data' => 'type:transfer'],
                    ['text' => 'Topup E-Wallet', 'callback_data' => 'type:topup_ewallet'],
                ],
                [
                    ['text' => 'Pembayaran', 'callback_data' => 'type:pembayaran'],
                    ['text' => 'Lainnya', 'callback_data' => 'type:lainnya'],
                ],
            ],
        ];

        $this->telegram->sendMessage($chatId, "Pilih jenis transaksi:", $keyboard);
    }

    private function handleBatal(string $chatId): void
    {
        ConversationState::clear($chatId);
        $this->telegram->sendMessage($chatId, "Input dibatalkan.");
    }

    private function handlePhoto(string $chatId, array $photos): void
    {
        $this->telegram->sendMessage($chatId, "Membaca struk...");

        $photo = end($photos);
        $fileId = $photo['file_id'];

        $token = $this->telegram->getToken();
        $fileResponse = \Illuminate\Support\Facades\Http::get("https://api.telegram.org/bot{$token}/getFile", [
            'file_id' => $fileId,
        ]);

        if (!$fileResponse->successful()) {
            $this->telegram->sendMessage($chatId, "Gagal mengambil foto. Coba lagi.");
            return;
        }

        $filePath = $fileResponse->json('result.file_path');
        $imageUrl = "https://api.telegram.org/file/bot{$token}/{$filePath}";

        $ocr = new GroqOcrService();
        $result = $ocr->parseReceipt($imageUrl);

        if (!$result) {
            $this->telegram->sendMessage($chatId, "Tidak bisa membaca struk. Coba foto lebih jelas, atau gunakan /catat untuk input manual.");
            return;
        }

        $label = self::TYPE_LABELS[$result['type']] ?? $result['type'];
        $amountFormatted = number_format($result['amount'], 0, ',', '.');

        ConversationState::set($chatId, [
            'step' => 'fee',
            'type' => $result['type'],
            'amount' => $result['amount'],
            'quantity' => $result['quantity'],
            'source' => 'ocr',
        ]);

        $msg = "Hasil baca struk:\n\n";
        $msg .= "Jenis: <b>{$label}</b>\n";
        $msg .= "Nominal: Rp {$amountFormatted} x {$result['quantity']}\n\n";
        $msg .= "Fee yang didapat berapa? (angka, misal: 5000)";

        $this->telegram->sendMessage($chatId, $msg);
    }

    private function handleCallback(string $chatId, string $data): void
    {
        if (str_starts_with($data, 'type:')) {
            $type = str_replace('type:', '', $data);
            $label = self::TYPE_LABELS[$type] ?? $type;

            ConversationState::set($chatId, [
                'step' => 'amount',
                'type' => $type,
            ]);

            $this->telegram->sendMessage($chatId, "Jenis: <b>{$label}</b>\n\nNominal per transaksi? (angka saja, misal: 1000000)");
        }
    }

    private function handleConversation(string $chatId, string $text): void
    {
        $state = ConversationState::get($chatId);

        if (!$state) {
            $this->telegram->sendMessage($chatId, "Ketik /catat untuk mulai mencatat transaksi.");
            return;
        }

        switch ($state['step']) {
            case 'amount':
                $amount = $this->parseNumber($text);
                if (!$amount || $amount <= 0) {
                    $this->telegram->sendMessage($chatId, "Nominal tidak valid. Masukkan angka, misal: 1000000");
                    return;
                }
                $state['amount'] = $amount;
                $state['step'] = 'quantity';
                ConversationState::set($chatId, $state);
                $this->telegram->sendMessage($chatId, "Jumlah transaksi? (misal: 1, 2, 3)");
                break;

            case 'quantity':
                $qty = (int) $text;
                if ($qty <= 0) {
                    $this->telegram->sendMessage($chatId, "Jumlah tidak valid. Masukkan angka, misal: 1");
                    return;
                }
                $state['quantity'] = $qty;
                $state['step'] = 'fee';
                ConversationState::set($chatId, $state);
                $this->telegram->sendMessage($chatId, "Fee yang didapat? (angka, misal: 5000. Ketik 0 jika tidak ada)");
                break;

            case 'fee':
                $fee = $this->parseNumber($text);
                if ($fee === null || $fee < 0) {
                    $this->telegram->sendMessage($chatId, "Fee tidak valid. Masukkan angka, misal: 5000");
                    return;
                }
                $state['fee'] = $fee;
                $this->saveTransaction($chatId, $state);
                break;

            default:
                ConversationState::clear($chatId);
                $this->telegram->sendMessage($chatId, "Terjadi kesalahan. Ketik /catat untuk mulai ulang.");
                break;
        }
    }

    private function saveTransaction(string $chatId, array $state): void
    {
        $flows = Transaction::getFlows($state['type']);

        $user = User::first();

        Transaction::create([
            'user_id' => $user->id,
            'type' => $state['type'],
            'amount' => $state['amount'],
            'quantity' => $state['quantity'],
            'fee' => $state['fee'],
            'kas_flow' => $flows['kas_flow'],
            'saldo_flow' => $flows['saldo_flow'],
            'source' => 'telegram',
            'transaction_date' => now()->toDateString(),
        ]);

        ConversationState::clear($chatId);

        $label = self::TYPE_LABELS[$state['type']] ?? $state['type'];
        $total = number_format($state['amount'] * $state['quantity'], 0, ',', '.');
        $fee = number_format($state['fee'], 0, ',', '.');

        $msg = "Tercatat!\n\n";
        $msg .= "Jenis: <b>{$label}</b>\n";
        $msg .= "Nominal: Rp " . number_format($state['amount'], 0, ',', '.') . " x {$state['quantity']}\n";
        $msg .= "Total: Rp {$total}\n";
        $msg .= "Fee: Rp {$fee}\n\n";
        $msg .= "/catat - Catat lagi";

        $this->telegram->sendMessage($chatId, $msg);
    }

    private function parseNumber(string $text): ?float
    {
        $cleaned = str_replace(['.', ',', ' ', 'rp', 'Rp', 'RP'], '', $text);
        if (!is_numeric($cleaned)) return null;
        return (float) $cleaned;
    }
}
