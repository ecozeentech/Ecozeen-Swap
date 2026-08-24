<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['deposit', 'withdrawal', 'buy', 'sell', 'swap', 'fee', 'giftcard', 'invoice']);
            $table->decimal('amount', 30, 8);
            $table->decimal('fee', 20, 8)->default(0);
            $table->string('currency_code', 15);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->string('reference')->unique();
            $table->json('metadata')->nullable(); // gateway response, rate used, addresses, related swap pair, etc.
            $table->text('admin_note')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
