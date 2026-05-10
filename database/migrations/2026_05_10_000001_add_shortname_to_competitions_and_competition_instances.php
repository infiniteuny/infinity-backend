<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('competitions', function (Blueprint $table) {
            $table->string('shortname')->nullable()->after('name');
        });

        Schema::table('competition_instances', function (Blueprint $table) {
            $table->string('shortname')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('competition_instances', function (Blueprint $table) {
            $table->dropColumn('shortname');
        });

        Schema::table('competitions', function (Blueprint $table) {
            $table->dropColumn('shortname');
        });
    }
};