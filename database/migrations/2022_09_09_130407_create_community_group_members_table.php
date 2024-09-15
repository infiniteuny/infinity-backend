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
        Schema::create('community_group_members', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('community_group_id')->constrained()->restrictOnUpdate()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->restrictOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('community_group_members');
    }
};
