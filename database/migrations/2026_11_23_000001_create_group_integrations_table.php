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
        $groupsTable = config('permission.table_names.roles', 'groups');

        Schema::create('group_integrations', function (Blueprint $table) use ($groupsTable) {
            $table->uuid('id')->primary();
            $table->foreignUuid('group_id')
                ->unique()
                ->constrained($groupsTable)
                ->restrictOnUpdate()
                ->cascadeOnDelete();
            $table->string('docs_group_id')->unique()->nullable();
            $table->string('docs_collection_id')->unique()->nullable();
            $table->timestamp('docs_group_last_synced_at')->nullable();
            $table->timestamp('docs_collection_last_synced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_integrations');
    }
};
