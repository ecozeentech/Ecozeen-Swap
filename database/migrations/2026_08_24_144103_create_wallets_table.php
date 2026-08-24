<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('currency_type', ['fiat', 'crypto']);
            $table->string('currency_code', 15); // NGN, GHS, USD, BTC, ETH, USDT ...
            $table->decimal('balance', 30, 8)->default(0);
            $table->decimal('reserved_balance', 30, 8)->default(0); // held for pending trades/withdrawals
            $table->timestamps();

            $table->unique(['user_id', 'currency_type', 'currency_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
