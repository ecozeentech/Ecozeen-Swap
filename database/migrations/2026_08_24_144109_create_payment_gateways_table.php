<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // paystack, flutterwave, bank_transfer
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(false);
            $table->text('credentials')->nullable(); // encrypted JSON: public/secret keys
            $table->json('metadata')->nullable(); // bank account details for bank_transfer, extra config
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
