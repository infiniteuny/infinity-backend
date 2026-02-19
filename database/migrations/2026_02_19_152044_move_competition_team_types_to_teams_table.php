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
        Schema::table('teams', function (Blueprint $table) {
            $table->foreignUuid('team_type_id')->constrained('competition_team_types')->restrictOnUpdate()->restrictOnDelete();
            });
        Schema::table('fund_applications', function (Blueprint $table) {
            $table->dropForeign(['competition_team_type_id']);
            $table->dropColumn('competition_team_type_id');
        });
        Schema::table('achievements', function (Blueprint $table) {
            $table->dropForeign(['competition_team_type_id']);
            $table->dropColumn('competition_team_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropForeign(['team_type_id']);
            $table->dropColumn('team_type_id');
        });
        Schema::table('fund_applications', function (Blueprint $table) {
            $table->foreignUuid('competition_team_type_id')->constrained('competition_team_types')->restrictOnUpdate()->restrictOnDelete();
        });
        Schema::table('achievements', function (Blueprint $table) {
            $table->foreignUuid('competition_team_type_id')->constrained('competition_team_types')->restrictOnUpdate()->restrictOnDelete();
        });
    }
};
