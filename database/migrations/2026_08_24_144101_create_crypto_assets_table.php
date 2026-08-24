<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crypto_assets', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Bitcoin
            $table->string('symbol', 15)->unique(); // BTC
            $table->string('logo')->nullable(); // storage path or URL
            $table->string('network')->nullable(); // e.g. Bitcoin, ERC20, TRC20, BEP20
            $table->integer('decimal_places')->default(8);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crypto_assets');
    }
};
