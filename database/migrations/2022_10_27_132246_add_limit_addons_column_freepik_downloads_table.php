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
        Schema::table('freepik_downloads', function (Blueprint $table) {
            $table->integer('limit_addons')->default(0)->after('limit');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('freepik_downloads', function (Blueprint $table) {
            $table->dropColumn('limit_addons');
        });
    }
};
