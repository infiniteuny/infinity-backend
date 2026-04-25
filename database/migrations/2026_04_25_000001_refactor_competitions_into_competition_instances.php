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
        Schema::create('competition_instances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('competition_id')->constrained()->restrictOnUpdate()->restrictOnDelete();
            $table->string('name');
            $table->text('description');
            $table->text('url')->nullable();
            $table->string('organizer');
            $table->foreignUuid('organizer_type_id')->constrained('competition_organizer_types')->restrictOnUpdate()->restrictOnDelete();
            $table->string('logo');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('location');
            $table->timestamps();
        });

        Schema::table('achievements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('competition_id');
            $table->foreignUuid('competition_instance_id')->constrained('competition_instances')->restrictOnUpdate()->restrictOnDelete();
        });

        Schema::table('fund_applications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('competition_id');
            $table->foreignUuid('competition_instance_id')->constrained('competition_instances')->restrictOnUpdate()->restrictOnDelete();
        });

        Schema::table('competitions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organizer_type_id');
            $table->dropColumn(['url', 'organizer', 'logo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('competitions', function (Blueprint $table) {
            $table->text('url')->nullable()->after('description');
            $table->string('organizer')->after('url');
            $table->foreignUuid('organizer_type_id')->constrained('competition_organizer_types')->restrictOnUpdate()->restrictOnDelete();
            $table->string('logo')->after('organizer_type_id');
        });

        Schema::table('fund_applications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('competition_instance_id');
            $table->foreignUuid('competition_id')->constrained()->restrictOnUpdate()->restrictOnDelete();
        });

        Schema::table('achievements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('competition_instance_id');
            $table->foreignUuid('competition_id')->constrained()->restrictOnUpdate()->restrictOnDelete();
        });

        Schema::dropIfExists('competition_instances');
    }
};
