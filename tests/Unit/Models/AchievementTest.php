<?php

namespace Tests\Unit\Models;

use App\Models\Achievement;
use App\Models\AchievementLeaderboard;
use App\Models\Competition;
use App\Models\CompetitionInstance;
use App\Models\CompetitionOrganizerType;
use App\Models\CompetitionOutput;
use App\Models\CompetitionRank;
use App\Models\CompetitionScale;
use App\Models\CompetitionTeamType;
use App\Models\CompetitionTimeRange;
use App\Models\Degree;
use App\Models\Faculty;
use App\Models\FundApplication;
use App\Models\Major;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AchievementTest extends TestCase
{
    use RefreshDatabase;

    private function seedBaseContext(array $weights = []): array
    {
        $degree = Degree::create(['code' => 'S1', 'name' => 'Sarjana']);
        $faculty = Faculty::create(['code' => 'FT', 'name' => 'Teknik']);
        $major = Major::create([
            'code' => 'PTI',
            'name' => 'Pendidikan Teknik Informatika',
            'degree_id' => $degree->id,
            'faculty_id' => $faculty->id,
        ]);
        $user = User::create([
            'name' => 'Alice',
            'username' => 'alice',
            'email_address' => 'alice@infiniteuny.id',
            'phone_number' => '+6281111111111',
            'student_id' => '24520241001',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);

        $organizerType = CompetitionOrganizerType::create([
            'name' => 'Organizer Type',
            'weight' => $weights['cot'] ?? 2,
        ]);
        $teamType = CompetitionTeamType::create([
            'name' => 'Team Type',
            'weight' => $weights['ctt'] ?? 3,
        ]);
        $scale = CompetitionScale::create([
            'name' => 'Scale',
            'weight' => $weights['cs'] ?? 5,
        ]);
        $timeRange = CompetitionTimeRange::create([
            'name' => 'Time Range',
            'weight' => $weights['ctr'] ?? 7,
        ]);
        $output = CompetitionOutput::create([
            'name' => 'Output',
            'weight' => $weights['co'] ?? 11,
        ]);
        $rank = CompetitionRank::create([
            'name' => 'Rank',
            'weight' => $weights['cr'] ?? 13,
        ]);

        $team = Team::create([
            'leader_id' => $user->id,
            'name' => 'Team Alpha',
            'team_type_id' => $teamType->id,
        ]);

        $competition = Competition::create([
            'name' => 'Gemastik',
            'shortname' => 'GEM',
        ]);

        $instance = CompetitionInstance::create([
            'competition_id' => $competition->id,
            'name' => 'Gemastik 2026',
            'shortname' => 'GEM26',
            'description' => 'Gemastik Instance Description',
            'url' => 'https://gemastik.example.com',
            'organizer' => 'Puspresnas',
            'organizer_type_id' => $organizerType->id,
            'logo' => 'logo-manifest',
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-03',
            'location' => 'Yogyakarta',
        ]);

        return compact(
            'user',
            'organizerType',
            'teamType',
            'scale',
            'timeRange',
            'output',
            'rank',
            'team',
            'competition',
            'instance',
        );
    }

    public function test_with_computed_point_avoids_extra_queries_and_hides_computed_point(): void
    {
        $this->assertSame(0, (new Achievement)->point);

        $ctx = $this->seedBaseContext();
        $secondRank = CompetitionRank::create(['name' => 'Runner Up', 'weight' => 17]);

        $first = Achievement::create([
            'team_id' => $ctx['team']->id,
            'competition_instance_id' => $ctx['instance']->id,
            'competition_scale_id' => $ctx['scale']->id,
            'competition_time_range_id' => $ctx['timeRange']->id,
            'competition_output_id' => $ctx['output']->id,
            'competition_rank_id' => $ctx['rank']->id,
            'competition_branch' => 'Branch A',
            'competition_start_date' => '2026-05-01',
            'competition_end_date' => '2026-05-03',
            'description' => 'First achievement',
            'image' => '',
            'status' => 'ACCEPTED',
        ]);

        Achievement::create([
            'team_id' => $ctx['team']->id,
            'competition_instance_id' => $ctx['instance']->id,
            'competition_scale_id' => $ctx['scale']->id,
            'competition_time_range_id' => $ctx['timeRange']->id,
            'competition_output_id' => $ctx['output']->id,
            'competition_rank_id' => $secondRank->id,
            'competition_branch' => 'Branch B',
            'competition_start_date' => '2026-05-01',
            'competition_end_date' => '2026-05-03',
            'description' => 'Second achievement',
            'image' => '',
            'status' => 'ACCEPTED',
        ]);

        $achievements = Achievement::withComputedPoint()->orderBy('competition_branch')->get();
        $this->assertCount(2, $achievements);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $serialized = $achievements->map(fn (Achievement $a) => $a->toArray())->all();

        $this->assertCount(0, DB::getQueryLog());
        $this->assertSame(30030, $serialized[0]['point']);
        $this->assertSame(39270, $serialized[1]['point']);
        $this->assertArrayNotHasKey('computed_point', $serialized[0]);
        $this->assertArrayNotHasKey('computed_point', $serialized[1]);

        DB::disableQueryLog();

        $this->assertSame(30030, $first->fresh()->point);
    }

    public function test_point_supports_64_bit_products_and_clamps_overflow_cleanly(): void
    {
        $ctx = $this->seedBaseContext([
            'cot' => 1000,
            'ctt' => 1000,
            'cs' => 1000,
            'ctr' => 10,
            'co' => 1,
            'cr' => 1,
        ]);

        $achievement = Achievement::create([
            'team_id' => $ctx['team']->id,
            'competition_instance_id' => $ctx['instance']->id,
            'competition_scale_id' => $ctx['scale']->id,
            'competition_time_range_id' => $ctx['timeRange']->id,
            'competition_output_id' => $ctx['output']->id,
            'competition_rank_id' => $ctx['rank']->id,
            'competition_branch' => '64-bit',
            'competition_start_date' => '2026-05-01',
            'competition_end_date' => '2026-05-03',
            'description' => 'Exceeds 32-bit int',
            'image' => '',
            'status' => 'ACCEPTED',
        ]);

        $this->assertSame(10_000_000_000, $achievement->fresh()->point);
        $this->assertSame(10_000_000_000, Achievement::withComputedPoint()->findOrFail($achievement->id)->point);

        CompetitionOrganizerType::whereKey($ctx['organizerType']->id)->update(['weight' => 2147483647]);
        CompetitionTeamType::whereKey($ctx['teamType']->id)->update(['weight' => 2147483647]);
        CompetitionScale::whereKey($ctx['scale']->id)->update(['weight' => 2147483647]);
        CompetitionTimeRange::whereKey($ctx['timeRange']->id)->update(['weight' => 2147483647]);
        CompetitionOutput::whereKey($ctx['output']->id)->update(['weight' => 2147483647]);
        CompetitionRank::whereKey($ctx['rank']->id)->update(['weight' => 2147483647]);

        $this->assertSame(PHP_INT_MAX, $achievement->fresh()->point);
        $this->assertSame(PHP_INT_MAX, Achievement::withComputedPoint()->findOrFail($achievement->id)->point);
    }

    public function test_achievement_leaderboards_supports_bigint_and_cascades_on_user_delete(): void
    {
        $ctx = $this->seedBaseContext();

        $leaderboard = AchievementLeaderboard::create([
            'user_id' => $ctx['user']->id,
            'year' => 2026,
            'total_points' => 10_000_000_000,
        ]);

        $this->assertSame(10_000_000_000, $leaderboard->fresh()->total_points);

        $ctx['user']->delete();

        $this->assertFalse(AchievementLeaderboard::whereKey($leaderboard->id)->exists());
    }

    public function test_migrations_roll_back_and_re_apply_cleanly_with_populated_rows(): void
    {
        $previousDefault = DB::getDefaultConnection();
        config([
            'database.connections.migration_roundtrip' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);
        DB::setDefaultConnection('migration_roundtrip');
        Schema::clearResolvedInstances();

        try {
            $this->assertSame(0, Artisan::call('migrate', ['--database' => 'migration_roundtrip']));

            $ctx = $this->seedBaseContext();

            $fundApplication = FundApplication::create([
                'team_id' => $ctx['team']->id,
                'competition_instance_id' => $ctx['instance']->id,
                'competition_scale_id' => $ctx['scale']->id,
                'competition_branch' => 'Software',
                'competition_start_date' => '2026-05-01',
                'competition_end_date' => '2026-05-03',
                'letter_of_acceptance' => '',
                'proposal' => '',
                'status' => 'PENDING',
            ]);

            $achievement = Achievement::create([
                'team_id' => $ctx['team']->id,
                'competition_instance_id' => $ctx['instance']->id,
                'competition_scale_id' => $ctx['scale']->id,
                'competition_time_range_id' => $ctx['timeRange']->id,
                'competition_output_id' => $ctx['output']->id,
                'competition_rank_id' => $ctx['rank']->id,
                'competition_branch' => 'Software',
                'competition_start_date' => '2026-05-01',
                'competition_end_date' => '2026-05-03',
                'description' => 'First place',
                'image' => '',
                'status' => 'ACCEPTED',
            ]);

            $leaderboard = AchievementLeaderboard::create([
                'user_id' => $ctx['user']->id,
                'year' => 2026,
                'total_points' => 5_000_000_000,
            ]);

            // Roll back 7 latest migrations covering:
            // 2026_11_28_000001, 2026_11_23_000001, 2026_11_19_000002,
            // 2026_11_19_000001, 2026_05_10_000001, 2026_04_25_000001, 2026_02_19_152044
            $exitCode = Artisan::call('migrate:rollback', [
                '--database' => 'migration_roundtrip',
                '--step' => 7,
            ]);
            $this->assertSame(0, $exitCode);

            $this->assertFalse(Schema::hasTable('competition_instances'));
            $this->assertFalse(Schema::hasColumn('teams', 'team_type_id'));

            $rolledBackCompetition = DB::table('competitions')->where('id', $ctx['competition']->id)->first();
            $this->assertNotNull($rolledBackCompetition);
            $this->assertSame('Gemastik Instance Description', $rolledBackCompetition->description);
            $this->assertSame('Puspresnas', $rolledBackCompetition->organizer);
            $this->assertSame($ctx['organizerType']->id, $rolledBackCompetition->organizer_type_id);

            $rolledBackFund = DB::table('fund_applications')->where('id', $fundApplication->id)->first();
            $this->assertSame($ctx['competition']->id, $rolledBackFund->competition_id);
            $this->assertSame($ctx['teamType']->id, $rolledBackFund->competition_team_type_id);

            $rolledBackAchievement = DB::table('achievements')->where('id', $achievement->id)->first();
            $this->assertSame($ctx['competition']->id, $rolledBackAchievement->competition_id);
            $this->assertSame($ctx['teamType']->id, $rolledBackAchievement->competition_team_type_id);

            $this->assertSame(2147483647, (int) DB::table('achievement_leaderboards')->where('id', $leaderboard->id)->value('total_points'));

            // Re-apply all rolled-back migrations with populated rows
            $reMigrateExitCode = Artisan::call('migrate', ['--database' => 'migration_roundtrip']);
            $this->assertSame(0, $reMigrateExitCode);

            $this->assertTrue(Schema::hasTable('competition_instances'));
            $this->assertSame($ctx['teamType']->id, DB::table('teams')->where('id', $ctx['team']->id)->value('team_type_id'));
            $this->assertNotNull(DB::table('achievements')->where('id', $achievement->id)->value('competition_instance_id'));
            $this->assertNotNull(DB::table('fund_applications')->where('id', $fundApplication->id)->value('competition_instance_id'));

            // Verify permission tables down() uses dropIfExists cleanly even when partially dropped
            $permissionMigration = require base_path('database/migrations/2022_09_09_130352_create_permission_tables.php');
            Schema::dropIfExists(config('permission.table_names.role_has_permissions'));
            Schema::dropIfExists('group_integrations');
            $permissionMigration->down();
            $this->assertFalse(Schema::hasTable(config('permission.table_names.roles')));
            $permissionMigration->up();
            $this->assertTrue(Schema::hasTable(config('permission.table_names.roles')));
        } finally {
            DB::setDefaultConnection($previousDefault);
            DB::purge('migration_roundtrip');
            Schema::clearResolvedInstances();
        }
    }
}
