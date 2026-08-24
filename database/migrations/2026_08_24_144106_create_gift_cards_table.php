<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gift_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('card_type'); // Amazon, iTunes, Steam, Google Play, etc.
            $table->text('card_number_hashed'); // encrypted
            $table->text('pin')->nullable(); // encrypted
            $table->string('card_image')->nullable(); // uploaded proof image
            $table->decimal('face_value', 20, 2);
            $table->string('face_value_currency', 6)->default('USD');
            $table->decimal('rate_applied', 20, 4)->nullable(); // buyback rate applied (percentage of face value)
            $table->decimal('selling_price', 20, 2)->nullable(); // amount to be paid to user
            $table->string('payout_currency', 6)->nullable();
            $table->enum('status', ['pending', 'reviewing', 'approved', 'rejected', 'paid'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_cards');
    }
};
