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
        Schema::create('user_personas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('persona_id')->constrained()->restrictOnUpdate()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->restrictOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_personas');
    }
};
