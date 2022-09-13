<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('competition_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('competition_scale_id')->constrained()->onDelete('cascade');
            $table->foreignId('competition_output_id')->constrained()->onDelete('cascade');
            $table->foreignId('competition_time_range_id')->constrained()->onDelete('cascade');
            $table->foreignId('competition_relevance_id')->constrained()->onDelete('cascade');
            $table->foreignId('competition_level_id')->constrained()->onDelete('cascade');
            $table->string('competition_name');
            $table->string('organizer');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('achievements');
    }
};
