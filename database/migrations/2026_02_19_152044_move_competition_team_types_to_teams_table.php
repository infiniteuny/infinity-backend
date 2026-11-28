<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! DB::table('teams')->exists()) {
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

            return;
        }

        Schema::table('teams', function (Blueprint $table) {
            $table->uuid('team_type_id')->nullable();
        });

        foreach (DB::table('achievements')->whereNotNull('competition_team_type_id')->pluck('competition_team_type_id', 'team_id') as $teamId => $teamTypeId) {
            DB::table('teams')
                ->where('id', $teamId)
                ->whereNull('team_type_id')
                ->update(['team_type_id' => $teamTypeId]);
        }

        foreach (DB::table('fund_applications')->whereNotNull('competition_team_type_id')->pluck('competition_team_type_id', 'team_id') as $teamId => $teamTypeId) {
            DB::table('teams')
                ->where('id', $teamId)
                ->whereNull('team_type_id')
                ->update(['team_type_id' => $teamTypeId]);
        }

        $fallbackTeamTypeId = DB::table('competition_team_types')->value('id');
        if ($fallbackTeamTypeId !== null) {
            DB::table('teams')
                ->whereNull('team_type_id')
                ->update(['team_type_id' => $fallbackTeamTypeId]);
        }

        Schema::table('teams', function (Blueprint $table) {
            $table->uuid('team_type_id')->nullable(false)->change();
            $table->foreign('team_type_id')
                ->references('id')
                ->on('competition_team_types')
                ->restrictOnUpdate()
                ->restrictOnDelete();
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
        Schema::table('fund_applications', function (Blueprint $table) {
            $table->uuid('competition_team_type_id')->nullable();
        });

        Schema::table('achievements', function (Blueprint $table) {
            $table->uuid('competition_team_type_id')->nullable();
        });

        $teamTypeByTeamId = DB::table('teams')->pluck('team_type_id', 'id');
        foreach ($teamTypeByTeamId as $teamId => $teamTypeId) {
            DB::table('fund_applications')
                ->where('team_id', $teamId)
                ->update(['competition_team_type_id' => $teamTypeId]);

            DB::table('achievements')
                ->where('team_id', $teamId)
                ->update(['competition_team_type_id' => $teamTypeId]);
        }

        $fallbackTeamTypeId = DB::table('competition_team_types')->value('id');
        if ($fallbackTeamTypeId !== null) {
            DB::table('fund_applications')
                ->whereNull('competition_team_type_id')
                ->update(['competition_team_type_id' => $fallbackTeamTypeId]);

            DB::table('achievements')
                ->whereNull('competition_team_type_id')
                ->update(['competition_team_type_id' => $fallbackTeamTypeId]);
        }

        Schema::table('fund_applications', function (Blueprint $table) {
            $table->uuid('competition_team_type_id')->nullable(false)->change();
            $table->foreign('competition_team_type_id')
                ->references('id')
                ->on('competition_team_types')
                ->restrictOnUpdate()
                ->restrictOnDelete();
        });

        Schema::table('achievements', function (Blueprint $table) {
            $table->uuid('competition_team_type_id')->nullable(false)->change();
            $table->foreign('competition_team_type_id')
                ->references('id')
                ->on('competition_team_types')
                ->restrictOnUpdate()
                ->restrictOnDelete();
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropForeign(['team_type_id']);
            $table->dropColumn('team_type_id');
        });
    }
};
