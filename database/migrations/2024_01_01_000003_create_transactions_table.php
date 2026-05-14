<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['tarik_tunai', 'setor_tunai', 'transfer', 'topup_ewallet', 'pembayaran', 'lainnya']);
            $table->decimal('amount', 15, 2);
            $table->integer('quantity')->default(1);
            $table->decimal('fee', 15, 2)->default(0);
            $table->enum('cash_flow', ['in', 'out']);
            $table->string('note')->nullable();
            $table->enum('source', ['web', 'telegram'])->default('web');
            $table->string('receipt_image')->nullable();
            $table->date('transaction_date');
            $table->timestamps();

            $table->index('transaction_date');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
