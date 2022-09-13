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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_study_id')->constrained()->onDelete('cascade');
            $table->string('student_id')->unique();
            $table->string('name');
            $table->boolean('status');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_extraordinary');
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
        Schema::dropIfExists('members');
    }
};
