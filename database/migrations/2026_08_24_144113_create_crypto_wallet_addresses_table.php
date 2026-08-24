<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crypto_wallet_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('crypto_asset_id')->constrained()->cascadeOnDelete();
            $table->string('address');
            $table->string('memo_tag')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'crypto_asset_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crypto_wallet_addresses');
    }
};
