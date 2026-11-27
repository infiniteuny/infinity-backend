<?php

namespace Tests\Feature;

use App\Enums\StorageVisibility;
use App\Facades\Storage;
use App\Http\Resources\AchievementLeaderboard\AchievementLeaderboardResource;
use App\Models\Achievement;
use App\Models\AchievementLeaderboard;
use App\Models\CommunityGroup;
use App\Models\CommunityGroupAdmin;
use App\Models\Competition;
use App\Models\CompetitionInstance;
use App\Models\CompetitionOrganizerType;
use App\Models\CompetitionOutput;
use App\Models\CompetitionRank;
use App\Models\CompetitionScale;
use App\Models\CompetitionTeamType;
use App\Models\CompetitionTimeRange;
use App\Models\Config;
use App\Models\CoreTeam;
use App\Models\CoreTeamDivision;
use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Group;
use App\Models\Major;
use App\Models\Permission;
use App\Models\Team;
use App\Models\Token;
use App\Models\User;
use App\Services\OidcService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage as LaravelStorage;
use RuntimeException;
use Tests\TestCase;

class SemiPublicGuardAndRedactionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $member;

    private CommunityGroup $communityGroup;

    private CommunityGroupAdmin $communityGroupAdmin;

    private CoreTeam $coreTeam;

    private CoreTeamDivision $division;

    private Achievement $achievement;

    private AchievementLeaderboard $leaderboard;

    protected function setUp(): void
    {
        parent::setUp();

        LaravelStorage::fake('local');

        // Configure distinct default and semi-public guards matching production (.env.example)
        config([
            'auth.defaults.guard' => 'api_headers',
            'auth.defaults.semi_public_guard' => 'api_token',
        ]);
        Auth::shouldUse('api_headers');

        $degree = Degree::create([
            'code' => 'S1',
            'name' => 'Sarjana',
        ]);
        $faculty = Faculty::create([
            'code' => 'FT',
            'name' => 'Teknik',
        ]);
        $major = Major::create([
            'code' => 'PTI',
            'name' => 'Pendidikan Teknik Informatika',
            'degree_id' => $degree->id,
            'faculty_id' => $faculty->id,
        ]);

        $this->admin = User::create([
            'name' => 'Admin User',
            'username' => 'adminuser',
            'email_address' => 'admin@student.uny.ac.id',
            'phone_number' => '+6281111111111',
            'student_id' => '24520241001',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);

        $this->member = User::create([
            'name' => 'Member User',
            'username' => 'memberuser',
            'email_address' => 'member@student.uny.ac.id',
            'phone_number' => '+6282222222222',
            'student_id' => '24520241002',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);

        foreach ([
            'update-user',
            'update-achievement',
            'create-community-group-member',
            'create-community-group-admin-member',
            'create-core-team-member',
        ] as $permissionName) {
            $permission = Permission::create([
                'name' => $permissionName,
                'guard_name' => 'api',
            ]);
            $this->admin->givePermissionTo($permission);
        }

        Config::create([
            'key' => 'public_banner',
            'value' => 'Welcome',
            'type' => 'STRING',
            'is_private' => false,
        ]);
        Config::create([
            'key' => 'allow_reregistration',
            'value' => 'true',
            'type' => 'BOOLEAN',
            'is_private' => true,
        ]);

        $logo = Storage::store(
            UploadedFile::fake()->image('logo.png'),
            'community-groups/logos',
            StorageVisibility::PUBLIC,
        );

        $this->communityGroup = CommunityGroup::create([
            'name' => 'Software Engineering',
            'description' => 'SE Group',
            'logo' => $logo,
            'priority' => 1,
            'is_active' => true,
        ]);

        $cgaGroup = Group::create([
            'name' => 'CG Admin 2025',
            'guard_name' => 'api',
            'is_managed' => true,
        ]);
        $this->communityGroupAdmin = CommunityGroupAdmin::create([
            'group_id' => $cgaGroup->id,
            'year' => 2025,
            'is_active' => true,
        ]);

        $ctGroup = Group::create([
            'name' => 'Core Team 2025',
            'guard_name' => 'api',
            'is_managed' => true,
        ]);
        $this->coreTeam = CoreTeam::create([
            'group_id' => $ctGroup->id,
            'year' => 2025,
            'is_active' => true,
        ]);
        $this->division = CoreTeamDivision::create([
            'name' => 'Engineering',
            'priority' => 1,
        ]);

        $teamType = CompetitionTeamType::create(['name' => 'Team', 'weight' => 1]);
        $team = Team::create([
            'name' => 'Team Infinity',
            'leader_id' => $this->admin->id,
            'team_type_id' => $teamType->id,
        ]);
        $organizerType = CompetitionOrganizerType::create(['name' => 'National', 'weight' => 1]);
        $competition = Competition::create(['name' => 'Gemastik']);
        $competitionInstance = CompetitionInstance::create([
            'competition_id' => $competition->id,
            'name' => 'Gemastik 2025',
            'description' => 'National competition',
            'organizer' => 'Puspresnas',
            'organizer_type_id' => $organizerType->id,
            'logo' => $logo,
            'start_date' => '2025-05-01',
            'end_date' => '2025-06-01',
            'location' => 'Yogyakarta',
        ]);
        $scale = CompetitionScale::create(['name' => 'National', 'weight' => 5]);
        $timeRange = CompetitionTimeRange::create(['name' => '1-3 Months', 'weight' => 2]);
        $output = CompetitionOutput::create(['name' => 'Product', 'weight' => 3]);
        $rank = CompetitionRank::create(['name' => '1st Place', 'weight' => 5]);

        $this->achievement = Achievement::create([
            'team_id' => $team->id,
            'competition_instance_id' => $competitionInstance->id,
            'competition_scale_id' => $scale->id,
            'competition_time_range_id' => $timeRange->id,
            'competition_output_id' => $output->id,
            'competition_rank_id' => $rank->id,
            'competition_branch' => 'Software Development',
            'competition_start_date' => '2025-05-01',
            'competition_end_date' => '2025-06-01',
            'description' => 'First place winner',
            'image' => $logo,
            'status' => 'ACCEPTED',
        ]);

        $this->leaderboard = AchievementLeaderboard::create([
            'user_id' => $this->member->id,
            'year' => 2025,
            'total_points' => 150,
        ]);
        $this->leaderboard->setRelation('user', $this->member);
    }

    public function test_config_controller_hides_private_configs_when_unauthenticated_and_exposes_them_via_semi_public_guard(): void
    {
        // Unauthenticated: private configs hidden on index, 404 on show, and is_private filter/sort blocked
        $this->getJson('/v1/configs')
            ->assertOk()
            ->assertJsonCount(1, 'data.configs')
            ->assertJsonPath('data.configs.0.key', 'public_banner');

        $this->getJson('/v1/configs/public_banner')
            ->assertOk()
            ->assertJsonPath('data.config.value', 'Welcome');

        $this->getJson('/v1/configs/allow_reregistration')
            ->assertNotFound();

        $this->getJson('/v1/configs?filters[is_private]=1')->assertStatus(400);
        $this->getJson('/v1/configs?sorts=is_private')->assertStatus(400);

        // Authenticated ONLY on semi_public_guard (api_token), while default guard (api_headers) has no user:
        // simulates SystemSettingsPage and ReregistrationPage hitting semi-public /v1/configs* behind Istio
        $this->actingAs($this->admin, 'api_token');
        $this->assertNull(Auth::guard('api_headers')->user());

        $this->getJson('/v1/configs?filters[is_private]=1&sorts=is_private')
            ->assertOk()
            ->assertJsonCount(1, 'data.configs')
            ->assertJsonPath('data.configs.0.key', 'allow_reregistration');

        $this->getJson('/v1/configs/allow_reregistration')
            ->assertOk()
            ->assertJsonPath('data.config.value', 'true');
    }

    public function test_redacting_resources_unredact_when_authenticated_on_default_guard_even_when_semi_public_guard_differs(): void
    {
        Bus::fake();

        // Unauthenticated on both guards: endpoints and resources redact classified fields
        $this->getJson("/v1/achievements/{$this->achievement->id}")
            ->assertOk()
            ->assertJsonPath('data.achievement.team_id', 'REDACTED');

        $this->getJson('/v1/leaderboards/achievements/2025?includes=user')
            ->assertOk()
            ->assertJsonPath('data.achievement_leaderboards.0.user.email_address', 'REDACTED')
            ->assertJsonPath('data.achievement_leaderboards.0.user.phone_number', 'REDACTED')
            ->assertJsonPath('data.achievement_leaderboards.0.user.student_id', 'REDACTED');

        // Authenticated ONLY on default guard (api_headers), while semi_public_guard (api_token) is null:
        // simulates protected POST/PATCH endpoints behind Istio OidcHeadersGuard
        $this->actingAs($this->admin, 'api_headers');
        $this->assertNull(Auth::guard('api_token')->user());

        $this->patchJson("/v1/users/{$this->member->id}", ['name' => 'Updated Member'])
            ->assertOk()
            ->assertJsonPath('data.user.email_address', 'member@student.uny.ac.id')
            ->assertJsonPath('data.user.phone_number', '+6282222222222')
            ->assertJsonPath('data.user.student_id', '24520241002');

        $this->postJson("/v1/community-groups/{$this->communityGroup->id}/members", [
            'user_id' => $this->member->id,
        ])
            ->assertStatus(200)
            ->assertJsonPath('data.community_group_member.email_address', 'member@student.uny.ac.id');

        $this->postJson("/v1/community-group-admins/{$this->communityGroupAdmin->id}/members", [
            'user_id' => $this->member->id,
            'community_group_id' => $this->communityGroup->id,
            'photo' => UploadedFile::fake()->image('cga.png'),
        ])
            ->assertStatus(200)
            ->assertJsonPath('data.community_group_admin_member.email_address', 'member@student.uny.ac.id');

        $this->postJson("/v1/core-teams/{$this->coreTeam->id}/members", [
            'user_id' => $this->member->id,
            'core_team_division_id' => $this->division->id,
            'photo' => UploadedFile::fake()->image('ct.png'),
        ])
            ->assertStatus(200)
            ->assertJsonPath('data.core_team_member.email_address', 'member@student.uny.ac.id');

        $this->patchJson("/v1/achievements/{$this->achievement->id}", [
            'description' => 'Updated first place winner',
        ])
            ->assertOk()
            ->assertJsonPath('data.achievement.team_id', $this->achievement->team_id);

        $authLeaderboard = (new AchievementLeaderboardResource($this->leaderboard))->toBaseArray(Request::create('/v1/leaderboards/achievements/2025'));
        $this->assertSame('member@student.uny.ac.id', $authLeaderboard['user']['email_address']);
    }

    public function test_semi_public_routes_do_not_bypass_semi_public_guard_via_unvalidated_default_guard_headers(): void
    {
        // Seed valid OidcHeadersGuard state (sso_id + active Token) so calling Auth::guard('api_headers')->user()
        // WOULD succeed if invoked on a semi-public route, while OidcTokenGuard ('api_token') rejects the JWT.
        $this->member->sso_id = '999';
        $this->member->saveQuietly();
        Token::create([
            'sso_id' => 'tok-1',
            'user_id' => $this->member->id,
            'last_used_at' => now(),
            'created_at' => now()->subMinute(),
            'expires_at' => now()->addHour(),
        ]);

        $this->mock(OidcService::class, function ($mock) {
            $mock->shouldReceive('verify')
                ->twice()
                ->with('invalid-jwt')
                ->andThrow(new RuntimeException('Invalid JWT signature'));
            $mock->shouldNotReceive('introspect');
        });

        // Auth::hasUser() is false because 'auth' middleware did not run on semi-public GET /v1/users/{id},
        // and semi_public_guard ('api_token') rejects the JWT -> PII stays REDACTED.
        $this->getJson("/v1/users/{$this->member->id}", [
            'X-User-Id' => '999',
            'X-User-Email' => 'member@student.uny.ac.id',
            'X-Token-Id' => 'tok-1',
            'X-Token-Created-At' => (string) time(),
            'X-Token-Expires-At' => (string) (time() + 3600),
            'Authorization' => 'Bearer invalid-jwt',
        ])
            ->assertOk()
            ->assertJsonPath('data.user.email_address', 'REDACTED')
            ->assertJsonPath('data.user.phone_number', 'REDACTED')
            ->assertJsonPath('data.user.student_id', 'REDACTED');
    }
}
