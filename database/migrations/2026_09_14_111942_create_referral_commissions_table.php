<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referred_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->enum('type', ['trade_commission', 'signup_bonus', 'manual_adjustment'])->default('trade_commission');
            $table->decimal('amount', 14, 2); // USD equivalent, always positive
            $table->decimal('rate_applied', 6, 3)->nullable(); // % used at the time, for audit
            $table->string('description')->nullable();
            $table->boolean('is_reversed')->default(false);
            $table->foreignId('reversed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reversed_at')->nullable();
            $table->timestamps();

            $table->index(['referrer_id', 'is_reversed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_commissions');
    }
};
