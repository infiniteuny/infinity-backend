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
        Schema::create('majors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('degree_id')->constrained()->restrictOnUpdate()->restrictOnDelete();
            $table->foreignUuid('faculty_id')->constrained()->restrictOnUpdate()->restrictOnDelete();
            $table->string('code')->unique();
            $table->string('name')->fulltext();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('majors');
    }
};
