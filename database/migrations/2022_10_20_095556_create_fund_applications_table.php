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
        Schema::create('fund_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('competition_name');
            $table->string('competition_url');
            $table->date('competition_date');
            $table->string('competition_branch');
            $table->string('team_name');
            $table->json('team_leader')->comment('name, student_id, phone');
            $table->json('team_members')->comment('name, student_id, phone');
            $table->string('student_id_card');
            $table->string('letter_of_acceptance');
            $table->string('budget_plan');
            $table->enum('status', ['waiting', 'rejected', 'accepted'])->default('waiting');
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
        Schema::dropIfExists('fund_applications');
    }
};
