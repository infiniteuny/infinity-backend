<?php

namespace Tests\Unit\Http\Controllers\Api\V1;

use App\Facades\Storage;
use App\Jobs\DeleteBlob;
use App\Jobs\SyncAchievementLeaderboards;
use App\Jobs\SyncActiveInactiveMemberGroup;
use App\Jobs\SyncActiveInactiveMemberSsoGroup;
use App\Jobs\SyncAdminSsoGroup;
use App\Jobs\SyncCGAdminMembersGroup;
use App\Jobs\SyncCGAdminSsoGroups;
use App\Jobs\SyncCoreTeamDocsCollections;
use App\Jobs\SyncCoreTeamDocsGroups;
use App\Jobs\SyncCoreTeamMembersGroup;
use App\Jobs\SyncCoreTeamSsoGroups;
use App\Jobs\SyncCustomSsoGroups;
use App\Jobs\SyncMemberGroup;
use App\Jobs\SyncMemberSsoGroup;
use App\Jobs\SyncSsoUsers;
use App\Jobs\SyncXCGAdminSsoGroups;
use App\Jobs\SyncXCoreTeamDocsGroups;
use App\Jobs\SyncXCoreTeamSsoGroups;
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
use App\Models\Major;
use App\Models\Permission;
use App\Models\Team;
use App\Models\User;
use Illuminate\Bus\UniqueLock;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\CallQueuedClosure;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AchievementControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Team $team;

    private CompetitionInstance $instance;

    private CompetitionScale $scale;

    private CompetitionTimeRange $timeRange;

    private CompetitionOutput $output;

    private CompetitionRank $rank;

    protected function setUp(): void
    {
        parent::setUp();

        $degree = Degree::create(['code' => 'S1', 'name' => 'Sarjana']);
        $faculty = Faculty::create(['code' => 'FT', 'name' => 'Teknik']);
        $major = Major::create([
            'code' => 'PTI',
            'name' => 'Pendidikan Teknik Informatika',
            'degree_id' => $degree->id,
            'faculty_id' => $faculty->id,
        ]);

        $this->admin = User::create([
            'name' => 'Admin',
            'username' => 'adminuser',
            'email_address' => 'admin@infiniteuny.id',
            'phone_number' => '+6281111111101',
            'student_id' => '24520241001',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);

        foreach (['create-achievement', 'update-achievement', 'delete-achievement', 'approve-achievement'] as $perm) {
            $this->admin->givePermissionTo(Permission::create([
                'name' => $perm,
                'guard_name' => 'api',
            ]));
        }

        $teamType = CompetitionTeamType::create(['name' => 'Team', 'weight' => 2]);
        $this->team = Team::create([
            'name' => 'Alpha',
            'leader_id' => $this->admin->id,
            'team_type_id' => $teamType->id,
        ]);
        $this->team->members()->attach($this->admin->id);

        $competition = Competition::create(['name' => 'Gemastik']);
        $organizerType = CompetitionOrganizerType::create(['name' => 'Ministry', 'weight' => 3]);
        $this->instance = CompetitionInstance::create([
            'competition_id' => $competition->id,
            'name' => 'Gemastik 2026',
            'shortname' => 'Gemastik',
            'description' => 'National IT Competition',
            'url' => 'https://gemastik.id',
            'organizer' => 'Kemdikbud',
            'organizer_type_id' => $organizerType->id,
            'logo' => '{"disk":"local","path":"ci","name":"gemastik.png","visibility":"private"}',
            'start_date' => '2026-05-01',
            'end_date' => '2026-06-01',
            'location' => 'Indonesia',
        ]);
        $this->scale = CompetitionScale::create(['name' => 'National', 'weight' => 5]);
        $this->timeRange = CompetitionTimeRange::create(['name' => '1 Month', 'weight' => 2]);
        $this->output = CompetitionOutput::create(['name' => 'Software', 'weight' => 3]);
        $this->rank = CompetitionRank::create(['name' => '1st Place', 'weight' => 4]);
    }

    public function test_index_and_show_precompute_points_without_n_plus_1_and_preserve_sparse_fields(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            Achievement::create([
                'team_id' => $this->team->id,
                'competition_instance_id' => $this->instance->id,
                'competition_scale_id' => $this->scale->id,
                'competition_time_range_id' => $this->timeRange->id,
                'competition_output_id' => $this->output->id,
                'competition_rank_id' => $this->rank->id,
                'competition_branch' => "Branch {$i}",
                'competition_start_date' => '2026-05-01',
                'competition_end_date' => '2026-05-10',
                'description' => "Achievement {$i}",
                'image' => '{"disk":"local","path":"achievements/images","name":"a.png","visibility":"private"}',
                'status' => 'ACCEPTED',
            ]);
        }

        // Expected point = 3 * 2 * 5 * 2 * 3 * 4 = 720
        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->getJson('/v1/achievements?fields=description&per_page=2');
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $response->assertOk()->assertJsonCount(2, 'data.achievements');
        $this->assertLessThanOrEqual(2, $queryCount);
        $firstItem = $response->json('data.achievements.0');
        $this->assertSame(720, $firstItem['point']);
        $this->assertArrayHasKey('description', $firstItem);
        $this->assertArrayNotHasKey('id', $firstItem);
        $this->assertArrayNotHasKey('computed_point', $firstItem);
        $this->assertNotNull($response->json('data.meta.next_cursor'));

        $achievement = Achievement::firstOrFail();
        DB::flushQueryLog();
        DB::enableQueryLog();

        $showResponse = $this->getJson("/v1/achievements/{$achievement->id}?fields=description");
        $showQueryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $showResponse->assertOk()
            ->assertJsonPath('data.achievement.point', 720)
            ->assertJsonMissingPath('data.achievement.computed_point');
        $this->assertLessThanOrEqual(2, $showQueryCount);
    }

    public function test_store_update_destroy_and_schedules_dispatch_leaderboard_and_isolated_integration_syncs(): void
    {
        Bus::fake([SyncAchievementLeaderboards::class, DeleteBlob::class]);

        Storage::shouldReceive('store')
            ->andReturn('{"disk":"local","path":"achievements/images","name":"cert.png","visibility":"private"}');
        Storage::shouldReceive('url')
            ->andReturn('https://example.com/achievements/cert.png');

        // PENDING creation does not dispatch leaderboard sync
        $pendingResp = $this->actingAs($this->admin, 'api_headers')
            ->post('/v1/achievements', [
                'team_id' => $this->team->id,
                'competition_instance_id' => $this->instance->id,
                'competition_scale_id' => $this->scale->id,
                'competition_time_range_id' => $this->timeRange->id,
                'competition_output_id' => $this->output->id,
                'competition_rank_id' => $this->rank->id,
                'competition_branch' => 'CTF',
                'competition_start_date' => '2025-11-10',
                'competition_end_date' => '2025-11-12',
                'description' => 'Finalist',
                'image' => UploadedFile::fake()->image('cert.png'),
                'status' => 'PENDING',
            ], ['Accept' => 'application/json'])
            ->assertCreated();

        Bus::assertNotDispatched(SyncAchievementLeaderboards::class);
        $achievementId = $pendingResp->json('data.achievement.id');

        // Transition PENDING -> ACCEPTED dispatches for 2025
        $this->actingAs($this->admin, 'api_headers')
            ->putJson("/v1/achievements/{$achievementId}", [
                'status' => 'ACCEPTED',
            ])
            ->assertOk();

        Bus::assertDispatched(SyncAchievementLeaderboards::class, fn (SyncAchievementLeaderboards $j) => $j->year === 2025);

        // Moving ACCEPTED achievement from 2025 -> 2026 dispatches for BOTH 2025 and 2026
        (new UniqueLock(Cache::store()))->release(new SyncAchievementLeaderboards(2025));
        Bus::fake([SyncAchievementLeaderboards::class, DeleteBlob::class]);

        $this->actingAs($this->admin, 'api_headers')
            ->putJson("/v1/achievements/{$achievementId}", [
                'competition_start_date' => '2026-02-01',
                'competition_end_date' => '2026-02-05',
            ])
            ->assertOk();

        Bus::assertDispatched(SyncAchievementLeaderboards::class, fn (SyncAchievementLeaderboards $j) => $j->year === 2025);
        Bus::assertDispatched(SyncAchievementLeaderboards::class, fn (SyncAchievementLeaderboards $j) => $j->year === 2026);

        // Deleting ACCEPTED achievement dispatches for 2026
        (new UniqueLock(Cache::store()))->release(new SyncAchievementLeaderboards(2025));
        (new UniqueLock(Cache::store()))->release(new SyncAchievementLeaderboards(2026));
        Bus::fake([SyncAchievementLeaderboards::class, DeleteBlob::class]);

        $this->actingAs($this->admin, 'api_headers')
            ->deleteJson("/v1/achievements/{$achievementId}")
            ->assertOk();

        Bus::assertDispatched(SyncAchievementLeaderboards::class, fn (SyncAchievementLeaderboards $j) => $j->year === 2026);

        // Verify daily schedule dispatches across accepted achievement years, leaderboard years, current & previous year
        Carbon::setTestNow('2026-01-01 00:05:00');
        try {
            Achievement::create([
                'team_id' => $this->team->id,
                'competition_instance_id' => $this->instance->id,
                'competition_scale_id' => $this->scale->id,
                'competition_time_range_id' => $this->timeRange->id,
                'competition_output_id' => $this->output->id,
                'competition_rank_id' => $this->rank->id,
                'competition_branch' => 'AI',
                'competition_start_date' => '2023-08-01',
                'competition_end_date' => '2023-08-05',
                'description' => 'Historical',
                'image' => '{"disk":"local","path":"achievements/images","name":"h.png","visibility":"private"}',
                'status' => 'ACCEPTED',
            ]);
            AchievementLeaderboard::create([
                'user_id' => $this->admin->id,
                'year' => 2022,
                'total_points' => 100,
            ]);

            (new UniqueLock(Cache::store()))->release(new SyncAchievementLeaderboards(2026));
            $this->artisan('schedule:list');
            Bus::fake();
            $events = collect(app(Schedule::class)->events());

            $leaderboardEvent = $events->first(fn ($event) => $event->description === 'sync-achievement-leaderboards');
            $this->assertNotNull($leaderboardEvent);
            $leaderboardEvent->run($this->app);

            foreach ([2022, 2023, 2025, 2026] as $expectedYear) {
                Bus::assertDispatched(
                    SyncAchievementLeaderboards::class,
                    fn (SyncAchievementLeaderboards $j) => $j->year === $expectedYear,
                );
            }

            // Verify hourly sync-groups-and-integrations isolates Authentik and Outline behind Stage 1
            Bus::fake();
            $syncEvent = $events->first(fn ($event) => $event->description === 'sync-groups-and-integrations');
            $this->assertNotNull($syncEvent);
            $syncEvent->run($this->app);

            $fanOutClosure = null;
            Bus::assertChained([
                SyncMemberGroup::class,
                SyncActiveInactiveMemberGroup::class,
                SyncCoreTeamMembersGroup::class,
                SyncCGAdminMembersGroup::class,
                function (CallQueuedClosure $job) use (&$fanOutClosure): bool {
                    $fanOutClosure = $job->closure->getClosure();

                    return true;
                },
            ]);

            $this->assertIsCallable($fanOutClosure);

            Bus::fake();
            $fanOutClosure();

            Bus::assertChained([
                SyncSsoUsers::class,
                SyncCoreTeamSsoGroups::class,
                SyncCGAdminSsoGroups::class,
                SyncXCoreTeamSsoGroups::class,
                SyncXCGAdminSsoGroups::class,
                SyncMemberSsoGroup::class,
                SyncAdminSsoGroup::class,
                SyncActiveInactiveMemberSsoGroup::class,
                SyncCustomSsoGroups::class,
            ]);

            Bus::assertChained([
                SyncCoreTeamDocsGroups::class,
                SyncXCoreTeamDocsGroups::class,
                SyncCoreTeamDocsCollections::class,
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }
}
