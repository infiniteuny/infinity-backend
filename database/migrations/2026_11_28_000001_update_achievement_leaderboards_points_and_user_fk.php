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
        Schema::table('achievement_leaderboards', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->bigInteger('total_points')->change();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->restrictOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('achievement_leaderboards')
            ->where('total_points', '>', 2147483647)
            ->update(['total_points' => 2147483647]);

        DB::table('achievement_leaderboards')
            ->where('total_points', '<', -2147483648)
            ->update(['total_points' => -2147483648]);

        Schema::table('achievement_leaderboards', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->integer('total_points')->change();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->restrictOnUpdate()
                ->restrictOnDelete();
        });
    }
};
