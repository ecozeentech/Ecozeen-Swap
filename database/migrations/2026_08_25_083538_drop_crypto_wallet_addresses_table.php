<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Deposit/sell receiving addresses are now managed centrally by admins per
 * crypto asset (see crypto_wallets), matching the single-vendor model —
 * every user sends to the same admin-controlled address(es) rather than a
 * uniquely generated per-user address. This table is no longer used.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('crypto_wallet_addresses');
    }

    public function down(): void
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
};
