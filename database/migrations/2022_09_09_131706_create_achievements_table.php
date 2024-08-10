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
        Schema::create('achievements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('team_id')->constrained()->restrictOnUpdate()->restrictOnDelete();
            $table->foreignUuid('competition_id')->constrained()->restrictOnUpdate()->restrictOnDelete();
            $table->foreignUuid('competition_team_type_id')->constrained()->restrictOnUpdate()->restrictOnDelete();
            $table->foreignUuid('competition_scale_id')->constrained()->restrictOnUpdate()->restrictOnDelete();
            $table->foreignUuid('competition_time_range_id')->constrained()->restrictOnUpdate()->restrictOnDelete();
            $table->foreignUuid('competition_output_id')->constrained()->restrictOnUpdate()->restrictOnDelete();
            $table->foreignUuid('competition_rank_id')->constrained()->restrictOnUpdate()->restrictOnDelete();
            $table->string('competition_branch');
            $table->date('competition_start_date');
            $table->date('competition_end_date');
            $table->text('description');
            $table->string('image');
            $table->enum('status', ['PENDING', 'REJECTED', 'ACCEPTED'])->default('PENDING');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};
