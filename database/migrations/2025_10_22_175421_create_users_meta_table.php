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
        Schema::create('users_meta', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignUlid('user_id')->constrained()->onDelete('cascade');
            $table->string('type')->default('null');
            $table->string('key')->index();
            $table->text('value')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_meta');
    }
};
