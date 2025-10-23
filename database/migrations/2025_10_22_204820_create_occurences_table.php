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
        Schema::create('occurrences', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('assignment_id')->constrained()->onDelete('cascade');
            $table->foreignUlid('family_id')->constrained()->onDelete('cascade');
            $table->dateTime('due_date');
            $table->enum('status', ['pending', 'submitted', 'approved', 'rejected', 'missed'])->default('pending');
            $table->integer('points_awarded')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('occurences');
    }
};
