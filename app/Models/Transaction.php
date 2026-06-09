<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'quantity',
        'fee',
        'kas_flow',
        'saldo_flow',
        'note',
        'source',
        'receipt_image',
        'transaction_date',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'fee' => 'decimal:2',
            'transaction_date' => 'date',
        ];
    }

    // Asumsi model BRILink umum: pelanggan membayar tunai untuk transfer/topup/
    // pembayaran, sehingga kas fisik MASUK dan saldo BRILink agen KELUAR.
    // Tarik tunai: agen kasih uang tunai (kas keluar), saldo BRILink masuk.
    // Setor tunai: pelanggan kasih tunai (kas masuk), saldo BRILink keluar.
    const TYPE_FLOW_MAP = [
        'tarik_tunai' => ['kas_flow' => 'out', 'saldo_flow' => 'in'],
        'setor_tunai' => ['kas_flow' => 'in', 'saldo_flow' => 'out'],
        'transfer' => ['kas_flow' => 'in', 'saldo_flow' => 'out'],
        'topup_ewallet' => ['kas_flow' => 'in', 'saldo_flow' => 'out'],
        'pembayaran' => ['kas_flow' => 'in', 'saldo_flow' => 'out'],
        'lainnya' => ['kas_flow' => 'none', 'saldo_flow' => 'none'],
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getFlows(string $type): array
    {
        return self::TYPE_FLOW_MAP[$type] ?? ['kas_flow' => 'none', 'saldo_flow' => 'none'];
    }

    public function getTotalAmountAttribute(): float
    {
        return $this->amount * $this->quantity;
    }
}
