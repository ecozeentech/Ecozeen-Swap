<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crypto_asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fiat_currency_id')->constrained()->cascadeOnDelete();
            $table->decimal('buy_rate', 24, 8); // rate at which the platform BUYS crypto from users (in fiat)
            $table->decimal('sell_rate', 24, 8); // rate at which the platform SELLS crypto to users (in fiat)
            $table->timestamp('starts_at')->useCurrent();
            $table->timestamp('expires_at'); // starts_at + 24 hours by default
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['crypto_asset_id', 'fiat_currency_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_rates');
    }
};
