<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AiOcrService
{
    public function parseReceipt(string $imageUrl): ?array
    {
        $apiKey = config('services.ai.api_key');
        if (!$apiKey) return null;

        $baseUrl = config('services.ai.base_url');
        $model = config('services.ai.ocr_model');

        $prompt = <<<PROMPT
Kamu adalah asisten yang membaca struk transaksi BRILink. Dari gambar struk ini, extract informasi berikut dalam format JSON:

{
  "type": "tarik_tunai|setor_tunai|transfer|topup_ewallet|pembayaran|lainnya",
  "amount": angka nominal per transaksi (tanpa titik/koma),
  "quantity": jumlah transaksi (default 1 jika tidak terlihat)
}

Aturan:
- type harus salah satu dari: tarik_tunai, setor_tunai, transfer, topup_ewallet, pembayaran, lainnya
- amount dalam Rupiah, angka bulat tanpa separator
- quantity adalah jumlah transaksi yang terlihat di struk
- Jika tidak bisa membaca, kembalikan null
- HANYA kembalikan JSON, tanpa penjelasan lain
PROMPT;

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
        ])->post("{$baseUrl}/chat/completions", [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $prompt,
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => $imageUrl,
                            ],
                        ],
                    ],
                ],
            ],
            'temperature' => 0.1,
            'max_tokens' => 200,
        ]);

        if (!$response->successful()) return null;

        $content = $response->json('choices.0.message.content');
        if (!$content) return null;

        $json = $this->extractJson($content);
        if (!$json) return null;

        if (!isset($json['type'], $json['amount'])) return null;

        $validTypes = ['tarik_tunai', 'setor_tunai', 'transfer', 'topup_ewallet', 'pembayaran', 'lainnya'];
        if (!in_array($json['type'], $validTypes)) {
            $json['type'] = 'lainnya';
        }

        $json['quantity'] = $json['quantity'] ?? 1;

        return $json;
    }

    private function extractJson(string $text): ?array
    {
        if (preg_match('/\{[^}]+\}/', $text, $matches)) {
            $decoded = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }
        return null;
    }
}
