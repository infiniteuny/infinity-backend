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
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('competition_id')->constrained('competitions')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('competition_team_type_id')->constrained('competition_team_types')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('competition_scale_id')->constrained('competition_scales')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('competition_time_range_id')->constrained('competition_time_ranges')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('competition_output_id')->constrained('competition_outputs')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('competition_rank_id')->constrained('competition_ranks')->onUpdate('cascade')->onDelete('cascade');
            $table->string('competition_branch');
            $table->date('competition_date');
            $table->text('description');
            $table->string('image');
            $table->enum('status', ['pending', 'rejected', 'accepted'])->default('pending');
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
