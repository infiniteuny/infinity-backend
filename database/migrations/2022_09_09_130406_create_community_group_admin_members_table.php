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
        Schema::create('community_group_admin_members', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->restrictOnUpdate()->cascadeOnDelete();
            $table->foreignUuid('community_group_admin_id')->constrained()->restrictOnUpdate()->cascadeOnDelete();
            $table->foreignUuid('community_group_id')->constrained()->restrictOnUpdate()->cascadeOnDelete();
            $table->string('photo');
            $table->string('animation')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'community_group_admin_id', 'community_group_id'], 'community_group_admin_members_user_admin_group_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('community_group_admin_members');
    }
};
