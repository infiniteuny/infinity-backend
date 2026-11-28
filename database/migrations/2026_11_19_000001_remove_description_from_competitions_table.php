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
        Schema::table('competitions', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            Schema::table('competitions', function (Blueprint $table) {
                $table->text('description')->nullable()->after('shortname');
            });
        } else {
            Schema::table('competitions', function (Blueprint $table) {
                $table->text('description')->default('')->after('shortname');
            });
        }

        if (Schema::hasTable('competition_instances')) {
            $descriptions = DB::table('competition_instances')
                ->whereNotNull('description')
                ->orderBy('created_at')
                ->pluck('description', 'competition_id');

            foreach ($descriptions as $competitionId => $description) {
                DB::table('competitions')
                    ->where('id', $competitionId)
                    ->update(['description' => $description]);
            }
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::table('competitions')
                ->whereNull('description')
                ->update(['description' => '']);

            Schema::table('competitions', function (Blueprint $table) {
                $table->text('description')->nullable(false)->change();
            });
        }
    }
};
