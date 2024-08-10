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
        Schema::create('core_team_members', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->restrictOnUpdate()->cascadeOnDelete();
            $table->foreignUuid('core_team_id')->constrained()->restrictOnUpdate()->cascadeOnDelete();
            $table->foreignUuid('core_team_division_id')->constrained()->restrictOnUpdate()->cascadeOnDelete();
            $table->string('photo');
            $table->string('animation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('core_team_members');
    }
};
