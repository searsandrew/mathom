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
        Schema::create('streaks', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('family_id')->constrained()->onDelete('cascade');
            $table->foreignUlid('assignment_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('current_streak')->default(0);
            $table->unsignedInteger('best_streak')->default(0);
            $table->date('last_completed_on')->nullable();
            $table->date('streak_started_on')->nullable();
            $table->timestamps();
        });

        Schema::create('streak_events', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('streak_id')->constrained()->onDelete('cascade');
            $table->date('for_date');
            $table->enum('event', ['increment', 'reset', 'best_updated']);
            $table->json('meta')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('streak_events');
        Schema::dropIfExists('streaks');
    }
};
