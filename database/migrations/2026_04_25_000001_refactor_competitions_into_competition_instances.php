<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Uid\Uuid;

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

        $hasExistingRows = DB::table('competitions')->exists()
            || DB::table('achievements')->exists()
            || DB::table('fund_applications')->exists();

        if (! $hasExistingRows) {
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

            return;
        }

        $hasDescription = Schema::hasColumn('competitions', 'description');
        $instanceIdByCompetition = [];

        foreach (DB::table('competitions')->get() as $competition) {
            $instanceId = (string) Uuid::v7();
            $instanceIdByCompetition[$competition->id] = $instanceId;

            DB::table('competition_instances')->insert([
                'id' => $instanceId,
                'competition_id' => $competition->id,
                'name' => $competition->name,
                'description' => $hasDescription ? ($competition->description ?? '') : '',
                'url' => $competition->url,
                'organizer' => $competition->organizer,
                'organizer_type_id' => $competition->organizer_type_id,
                'logo' => $competition->logo,
                'start_date' => '2026-01-01',
                'end_date' => '2026-01-01',
                'location' => '',
                'created_at' => $competition->created_at,
                'updated_at' => $competition->updated_at,
            ]);
        }

        Schema::table('achievements', function (Blueprint $table) {
            $table->uuid('competition_instance_id')->nullable();
        });

        Schema::table('fund_applications', function (Blueprint $table) {
            $table->uuid('competition_instance_id')->nullable();
        });

        foreach ($instanceIdByCompetition as $competitionId => $instanceId) {
            DB::table('achievements')
                ->where('competition_id', $competitionId)
                ->update(['competition_instance_id' => $instanceId]);

            DB::table('fund_applications')
                ->where('competition_id', $competitionId)
                ->update(['competition_instance_id' => $instanceId]);
        }

        Schema::table('achievements', function (Blueprint $table) {
            $table->uuid('competition_instance_id')->nullable(false)->change();
            $table->foreign('competition_instance_id')
                ->references('id')
                ->on('competition_instances')
                ->restrictOnUpdate()
                ->restrictOnDelete();
        });

        Schema::table('achievements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('competition_id');
        });

        Schema::table('fund_applications', function (Blueprint $table) {
            $table->uuid('competition_instance_id')->nullable(false)->change();
            $table->foreign('competition_instance_id')
                ->references('id')
                ->on('competition_instances')
                ->restrictOnUpdate()
                ->restrictOnDelete();
        });

        Schema::table('fund_applications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('competition_id');
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
        $hasDescription = Schema::hasColumn('competitions', 'description');

        Schema::table('competitions', function (Blueprint $table) use ($hasDescription) {
            $urlColumn = $table->text('url')->nullable();
            if ($hasDescription) {
                $urlColumn->after('description');
            }

            $table->string('organizer')->nullable()->after('url');
            $table->uuid('organizer_type_id')->nullable()->after('organizer');
            $table->string('logo')->nullable()->after('organizer_type_id');
        });

        $instancesByCompetition = DB::table('competition_instances')
            ->orderBy('created_at')
            ->get()
            ->keyBy('competition_id');

        foreach ($instancesByCompetition as $competitionId => $instance) {
            DB::table('competitions')
                ->where('id', $competitionId)
                ->update([
                    'url' => $instance->url,
                    'organizer' => $instance->organizer,
                    'organizer_type_id' => $instance->organizer_type_id,
                    'logo' => $instance->logo,
                ]);
        }

        DB::table('competitions')
            ->whereNull('organizer')
            ->update(['organizer' => '']);

        DB::table('competitions')
            ->whereNull('logo')
            ->update(['logo' => '']);

        $fallbackOrganizerTypeId = DB::table('competition_organizer_types')->value('id');
        if ($fallbackOrganizerTypeId !== null) {
            DB::table('competitions')
                ->whereNull('organizer_type_id')
                ->update(['organizer_type_id' => $fallbackOrganizerTypeId]);
        }

        Schema::table('fund_applications', function (Blueprint $table) {
            $table->uuid('competition_id')->nullable();
        });

        Schema::table('achievements', function (Blueprint $table) {
            $table->uuid('competition_id')->nullable();
        });

        $instanceCompetitionMap = DB::table('competition_instances')
            ->pluck('competition_id', 'id');

        foreach ($instanceCompetitionMap as $instanceId => $competitionId) {
            DB::table('fund_applications')
                ->where('competition_instance_id', $instanceId)
                ->update(['competition_id' => $competitionId]);

            DB::table('achievements')
                ->where('competition_instance_id', $instanceId)
                ->update(['competition_id' => $competitionId]);
        }

        $fallbackCompetitionId = DB::table('competitions')->value('id');
        if ($fallbackCompetitionId !== null) {
            DB::table('fund_applications')
                ->whereNull('competition_id')
                ->update(['competition_id' => $fallbackCompetitionId]);

            DB::table('achievements')
                ->whereNull('competition_id')
                ->update(['competition_id' => $fallbackCompetitionId]);
        }

        Schema::table('fund_applications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('competition_instance_id');
        });

        Schema::table('achievements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('competition_instance_id');
        });

        Schema::dropIfExists('competition_instances');

        if ($fallbackOrganizerTypeId === null) {
            DB::table('competitions')->whereNull('organizer_type_id')->delete();
        }

        Schema::table('competitions', function (Blueprint $table) {
            $table->string('organizer')->nullable(false)->change();
            $table->uuid('organizer_type_id')->nullable(false)->change();
            $table->string('logo')->nullable(false)->change();
            $table->foreign('organizer_type_id')
                ->references('id')
                ->on('competition_organizer_types')
                ->restrictOnUpdate()
                ->restrictOnDelete();
        });

        Schema::table('fund_applications', function (Blueprint $table) {
            $table->uuid('competition_id')->nullable(false)->change();
            $table->foreign('competition_id')
                ->references('id')
                ->on('competitions')
                ->restrictOnUpdate()
                ->restrictOnDelete();
        });

        Schema::table('achievements', function (Blueprint $table) {
            $table->uuid('competition_id')->nullable(false)->change();
            $table->foreign('competition_id')
                ->references('id')
                ->on('competitions')
                ->restrictOnUpdate()
                ->restrictOnDelete();
        });
    }
};
