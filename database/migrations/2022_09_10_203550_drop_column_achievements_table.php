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
        Schema::table(
            'achievements',
            function (Blueprint $table) {
                $table->dropForeign(['competition_relevance_id']);
                $table->dropColumn('competition_relevance_id');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(
            'achievements',
            function (Blueprint $table) {
                $table->foreignId('competition_relevance_id')->after('competition_time_range_id')->constrained()->onDelete('cascade');
            }
        );
    }
};
