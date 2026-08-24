<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gift_cards', function (Blueprint $table) {
            $table->foreignId('gift_card_product_id')->nullable()->after('user_id')
                ->constrained('gift_card_products')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('gift_cards', function (Blueprint $table) {
            $table->dropConstrainedForeignId('gift_card_product_id');
        });
    }
};
