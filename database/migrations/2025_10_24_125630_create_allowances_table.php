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
        Schema::create('allowances', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('family_id')->constrained()->onDelete('cascade');
            $table->foreignUlid('wallet_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['active', 'paused', 'ended'])->default('active');
            // Scheduling
            $table->enum('frequency', ['weekly', 'biweekly', 'monthly', 'custom'])->default('weekly');
            $table->unsignedTinyInteger('day_of_week')->nullable();
            $table->unsignedTinyInteger('day_of_month')->nullable();
            $table->string('rrule_text')->nullable(); // optional override
            $table->string('timezone')->nullable();
            // Calculation Mode
            $table->enum('mode', ['fixed', 'earned', 'min_threshold', 'mixed'])->default('fixed');
            $table->integer('fixed_points')->nullable();
            $table->unsignedInteger('min_approved_occurrences')->nullable();
            $table->integer('bonus_points_on_threshold')->nullable();
            // Dates
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('allowance_runs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('allowance_id')->constrained()->onDelete('cascade');
            $table->dateTime('started_at');
            $table->dateTime('ended_at');
            $table->enum('status', ['calculated', 'paid', 'skipped', 'failed'])->default('calculated');
            $table->ulid('ledger_id')->nullable();
            $table->json('calc_summary')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allowance_runs');
        Schema::dropIfExists('allowances');
    }
};
