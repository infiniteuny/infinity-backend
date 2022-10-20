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
        Schema::create('freepiks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('freepik_download_id')->constrained()->onDelete('cascade');
            $table->string('url');
            $table->string('file_name')->nullable();
            $table->string('file_path')->nullable()->unique();
            $table->enum('status', ['waiting', 'failed', 'completed'])->default('waiting');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('freepiks');
    }
};
