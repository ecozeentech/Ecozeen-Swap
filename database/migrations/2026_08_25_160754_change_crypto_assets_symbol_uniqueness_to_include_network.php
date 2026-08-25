<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The same coin symbol legitimately exists on multiple networks (e.g.
 * USDT on ERC20, TRC20, and BEP20 are three different deposit addresses
 * and, in practice, three different assets from the platform's point of
 * view). The original schema had a single-column unique index on
 * `symbol` alone, which made it impossible to add a second "USDT" row
 * for a different network. This replaces it with a composite unique
 * index on (symbol, network), so the same symbol can be added multiple
 * times as long as the network differs.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crypto_assets', function (Blueprint $table) {
            $table->dropUnique('crypto_assets_symbol_unique');
            $table->unique(['symbol', 'network']);
        });
    }

    public function down(): void
    {
        Schema::table('crypto_assets', function (Blueprint $table) {
            $table->dropUnique(['symbol', 'network']);
            $table->unique('symbol');
        });
    }
};
