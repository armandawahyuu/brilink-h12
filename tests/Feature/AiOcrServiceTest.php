<?php

namespace Tests\Feature;

use App\Services\AiOcrService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiOcrServiceTest extends TestCase
{
    public function test_receipt_ocr_uses_sumopod_openai_compatible_endpoint(): void
    {
        config([
            'services.ai.api_key' => 'sk-test',
            'services.ai.base_url' => 'https://ai.sumopod.com/v1',
            'services.ai.ocr_model' => 'gpt-4o-mini',
        ]);

        Http::fake([
            'https://ai.sumopod.com/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => '{"type":"tarik_tunai","amount":100000,"quantity":1}',
                        ],
                    ],
                ],
            ]),
        ]);

        $result = app(AiOcrService::class)->parseReceipt('https://example.test/receipt.jpg');

        $this->assertSame('tarik_tunai', $result['type']);
        $this->assertSame(100000, $result['amount']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://ai.sumopod.com/v1/chat/completions'
                && $request->hasHeader('Authorization', 'Bearer sk-test')
                && $request['model'] === 'gpt-4o-mini';
        });
    }
}
