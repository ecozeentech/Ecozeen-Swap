<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('crypto_asset_id')->constrained();
            $table->decimal('amount_crypto', 30, 8);
            $table->decimal('fiat_amount', 20, 2)->nullable();
            $table->string('fiat_currency', 6)->nullable();
            $table->string('recipient_email');
            $table->string('recipient_name')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'sent', 'paid', 'expired', 'cancelled'])->default('draft');
            $table->timestamp('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
