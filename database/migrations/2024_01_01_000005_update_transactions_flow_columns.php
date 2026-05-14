<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('cash_flow');
            $table->enum('kas_flow', ['in', 'out', 'none'])->default('none')->after('fee');
            $table->enum('saldo_flow', ['in', 'out', 'none'])->default('none')->after('kas_flow');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['kas_flow', 'saldo_flow']);
            $table->enum('cash_flow', ['in', 'out'])->after('fee');
        });
    }
};
