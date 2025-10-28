<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ledgers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('family_id')->constrained()->onDelete('cascade');
            $table->foreignUlid('wallet_id')->constrained()->onDelete('cascade');
            $table->timestamp('occurred_at')->index();
            $table->enum('type', ['earn', 'bonus', 'penalty', 'manual_adjust', 'redeem_hold', 'redeem_release', 'redeem_capture', 'allowance_payout']);
            $table->unsignedInteger('amount');
            $table->string('reference_type')->nullable();
            $table->ulid('reference_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledgers');
    }
};
