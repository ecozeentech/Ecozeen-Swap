<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_trusted_ips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('ip_address', 45);
            $table->string('verification_code', 6)->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'ip_address']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_trusted_ips');
    }
};
