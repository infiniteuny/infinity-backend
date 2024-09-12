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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('sso_id')->unique();
            $table->string('name')->fulltext();
            $table->string('email_address')->unique();
            $table->string('phone_number')->unique();
            $table->string('student_id')->unique();
            $table->foreignUuid('major_id')->constrained()->restrictOnUpdate()->restrictOnDelete();
            $table->json('links');
            $table->enum('role', ['ADMIN', 'STUDENT'])->default('STUDENT');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_member');
            $table->boolean('is_extraordinary')->default(false);
            $table->timestamps();
        });
        Schema::create('tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('external_id')->unique();
            $table->foreignUuid('user_id')->constrained()->restrictOnUpdate()->cascadeOnDelete();
            $table->timestamp('last_used_at');
            $table->timestamp('created_at');
            $table->timestamp('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('tokens');
    }
};
