<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiat_currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 6)->unique(); // NGN, GHS, USD
            $table->string('name');
            $table->string('symbol', 5);
            $table->boolean('is_active')->default(true);
            $table->decimal('exchange_rate_to_usd', 20, 8)->default(1); // 1 unit of this currency in USD
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiat_currencies');
    }
};
