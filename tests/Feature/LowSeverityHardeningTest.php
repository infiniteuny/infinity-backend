<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\V1\CompetitionTimeRangeController;
use App\Http\Controllers\Api\V1\UserPermissionController;
use App\Jobs\CreateSsoGroup;
use App\Jobs\CreateSsoUser;
use App\Jobs\DeleteBlob;
use App\Jobs\UpdateSsoGroup;
use App\Jobs\UpdateSsoUser;
use App\Models\AchievementLeaderboard;
use App\Models\CommunityGroup;
use App\Models\CommunityGroupAdmin;
use App\Models\CompetitionOrganizerType;
use App\Models\CompetitionOutput;
use App\Models\CompetitionRank;
use App\Models\CompetitionScale;
use App\Models\CompetitionTeamType;
use App\Models\CompetitionTimeRange;
use App\Models\CoreTeam;
use App\Models\CoreTeamDivision;
use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Group;
use App\Models\Major;
use App\Models\Permission;
use App\Models\Persona;
use App\Models\Team;
use App\Models\User;
use App\Rules\Associative;
use App\Utils\Encoder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use ReflectionMethod;
use Tests\TestCase;

class LowSeverityHardeningTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Major $major;

    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake([
            CreateSsoUser::class,
            UpdateSsoUser::class,
            CreateSsoGroup::class,
            UpdateSsoGroup::class,
            DeleteBlob::class,
        ]);

        $degree = Degree::create(['code' => 'S1', 'name' => 'Sarjana']);
        $faculty = Faculty::create(['code' => 'FT', 'name' => 'Teknik']);
        $this->major = Major::create([
            'code' => 'PTI',
            'name' => 'Pendidikan Teknik Informatika',
            'degree_id' => $degree->id,
            'faculty_id' => $faculty->id,
        ]);

        $this->admin = $this->makeUser('Admin', 'adminuser', 'admin@infiniteuny.id');

        foreach ([
            'create-community-group-member',
            'create-community-group-admin-member',
            'create-core-team-member',
            'create-group-permission',
            'create-team-member',
            'create-user-group',
            'create-user-permission',
            'create-user-persona',
        ] as $permName) {
            $this->admin->givePermissionTo(Permission::create([
                'name' => $permName,
                'guard_name' => 'api',
            ]));
        }
    }

    public function test_all_eight_pivot_store_endpoints_return_http_201_created(): void
    {
        Storage::fake('public');

        $targetUser = $this->makeUser('Member', 'memberuser', 'member@infiniteuny.id');

        $communityGroup = CommunityGroup::create([
            'name' => 'Backend Guild',
            'priority' => 1,
            'description' => 'Backend systems',
            'logo' => '{"disk":"public","path":"cg","name":"logo.png","visibility":"public"}',
            'is_active' => true,
        ]);

        $cgaGroup = Group::create(['name' => 'CG Admin 2026', 'is_managed' => true]);
        $cgAdmin = CommunityGroupAdmin::create([
            'year' => 2026,
            'is_active' => true,
            'group_id' => $cgaGroup->id,
        ]);

        $ctGroup = Group::create(['name' => 'Core Team 2026', 'is_managed' => true]);
        $coreTeam = CoreTeam::create([
            'year' => 2026,
            'is_active' => true,
            'group_id' => $ctGroup->id,
        ]);
        $division = CoreTeamDivision::create(['name' => 'Engineering', 'priority' => 1]);

        $customGroup = Group::create(['name' => 'Reviewers', 'is_managed' => false]);
        $permission = Permission::create(['name' => 'custom-review']);

        $teamType = CompetitionTeamType::create(['name' => 'Squad', 'weight' => 1]);
        $team = Team::create([
            'name' => 'Alpha Team',
            'leader_id' => $this->admin->id,
            'team_type_id' => $teamType->id,
        ]);
        $team->members()->attach($this->admin->id);

        $persona = Persona::create([
            'name' => 'Architect',
            'priority' => 1,
            'description' => 'System architect',
            'logo' => '{"disk":"public","path":"personas","name":"arch.png","visibility":"public"}',
        ]);

        // 1. CommunityGroupMember
        $this->actingAs($this->admin, 'api_headers')
            ->postJson("/v1/community-groups/{$communityGroup->id}/members", [
                'user_id' => $targetUser->id,
            ])
            ->assertCreated();

        // 2. CommunityGroupAdminMember
        $this->actingAs($this->admin, 'api_headers')
            ->post("/v1/community-group-admins/{$cgAdmin->id}/members", [
                'user_id' => $targetUser->id,
                'community_group_id' => $communityGroup->id,
                'photo' => UploadedFile::fake()->image('cga.png'),
            ], ['Accept' => 'application/json'])
            ->assertCreated();

        // 3. CoreTeamMember
        $this->actingAs($this->admin, 'api_headers')
            ->post("/v1/core-teams/{$coreTeam->id}/members", [
                'user_id' => $targetUser->id,
                'core_team_division_id' => $division->id,
                'photo' => UploadedFile::fake()->image('ct.png'),
            ], ['Accept' => 'application/json'])
            ->assertCreated();

        // 4. GroupPermission
        $this->actingAs($this->admin, 'api_headers')
            ->postJson("/v1/groups/{$customGroup->id}/permissions", [
                'permission_id' => $permission->id,
            ])
            ->assertCreated();

        // 5. TeamMember
        $this->actingAs($this->admin, 'api_headers')
            ->postJson("/v1/teams/{$team->id}/members", [
                'user_id' => $targetUser->id,
            ])
            ->assertCreated();

        // 6. UserGroup
        $this->actingAs($this->admin, 'api_headers')
            ->postJson("/v1/users/{$targetUser->id}/groups", [
                'group_id' => $customGroup->id,
            ])
            ->assertCreated();

        // 7. UserPermission
        $this->actingAs($this->admin, 'api_headers')
            ->postJson("/v1/users/{$targetUser->id}/permissions", [
                'permission_id' => $permission->id,
            ])
            ->assertCreated();

        // 8. UserPersona
        $this->actingAs($this->admin, 'api_headers')
            ->postJson("/v1/users/{$targetUser->id}/personas", [
                'persona_id' => $persona->id,
            ])
            ->assertCreated();
    }

    public function test_per_page_clamping_handles_invalid_negative_zero_and_oversized_values_up_to_500(): void
    {
        $this->makeUser('Two', 'usertwo', 'two@infiniteuny.id');
        $this->makeUser('Three', 'userthree', 'three@infiniteuny.id');

        // Non-integer string falls back to default 10 without 500 error
        $this->getJson('/v1/users?per_page=abc')
            ->assertOk()
            ->assertJsonPath('data.meta.per_page', 10);

        // Array falls back to default 10 without 500 error
        $this->getJson('/v1/users?per_page[]=20')
            ->assertOk()
            ->assertJsonPath('data.meta.per_page', 10);

        // Zero and negative clamp to 1
        $this->getJson('/v1/users?per_page=0')
            ->assertOk()
            ->assertJsonPath('data.meta.per_page', 1)
            ->assertJsonCount(1, 'data.users');

        $this->getJson('/v1/users?per_page=-5')
            ->assertOk()
            ->assertJsonPath('data.meta.per_page', 1)
            ->assertJsonCount(1, 'data.users');

        // Landing page per_page=500 is permitted, while 100000 is clamped to 500
        $this->getJson('/v1/users?per_page=500')
            ->assertOk()
            ->assertJsonPath('data.meta.per_page', 500);

        $this->getJson('/v1/users?per_page=100000')
            ->assertOk()
            ->assertJsonPath('data.meta.per_page', 500);
    }

    public function test_scribe_docblocks_and_model_boolean_integer_casts(): void
    {
        // L4 docblocks
        $userPermIndexDoc = (string) (new ReflectionMethod(UserPermissionController::class, 'index'))->getDocComment();
        $userPermShowDoc = (string) (new ReflectionMethod(UserPermissionController::class, 'show'))->getDocComment();
        $timeRangeUpdateDoc = (string) (new ReflectionMethod(CompetitionTimeRangeController::class, 'update'))->getDocComment();

        $this->assertStringNotContainsString('@unauthenticated', $userPermIndexDoc);
        $this->assertStringNotContainsString('@unauthenticated', $userPermShowDoc);
        $this->assertStringContainsString('@apiResource App\Http\Resources\CompetitionTimeRange\CompetitionTimeRangeResource', $timeRangeUpdateDoc);
        $this->assertStringContainsString('@apiResourceModel App\Models\CompetitionTimeRange', $timeRangeUpdateDoc);

        // L5 casts after fresh DB hydration
        $cg = CommunityGroup::create([
            'name' => 'Security',
            'priority' => '3',
            'description' => 'AppSec',
            'logo' => '{"disk":"public","path":"cg","name":"sec.png","visibility":"public"}',
            'is_active' => 1,
        ])->fresh();
        $this->assertSame(3, $cg->priority);
        $this->assertSame(true, $cg->is_active);

        $groupA = Group::create(['name' => 'CG Admin 2025', 'is_managed' => true]);
        $cga = CommunityGroupAdmin::create([
            'year' => '2025',
            'is_active' => 0,
            'group_id' => $groupA->id,
        ])->fresh();
        $this->assertSame(2025, $cga->year);
        $this->assertSame(false, $cga->is_active);

        $groupB = Group::create(['name' => 'Core Team 2025', 'is_managed' => true]);
        $ct = CoreTeam::create([
            'year' => '2025',
            'is_active' => 1,
            'group_id' => $groupB->id,
        ])->fresh();
        $this->assertSame(2025, $ct->year);
        $this->assertSame(true, $ct->is_active);

        $leaderboard = AchievementLeaderboard::create([
            'user_id' => $this->admin->id,
            'year' => '2025',
            'total_points' => '150',
        ])->fresh();
        $this->assertSame(2025, $leaderboard->year);
        $this->assertSame(150, $leaderboard->total_points);

        $this->assertSame(2, CoreTeamDivision::create(['name' => 'Ops', 'priority' => '2'])->fresh()->priority);
        $this->assertSame(5, CompetitionOrganizerType::create(['name' => 'Ministry', 'weight' => '5'])->fresh()->weight);
        $this->assertSame(4, CompetitionOutput::create(['name' => 'Paper', 'weight' => '4'])->fresh()->weight);
        $this->assertSame(10, CompetitionRank::create(['name' => 'Champion', 'weight' => '10'])->fresh()->weight);
        $this->assertSame(8, CompetitionScale::create(['name' => 'International', 'weight' => '8'])->fresh()->weight);
        $this->assertSame(3, CompetitionTeamType::create(['name' => 'Duo', 'weight' => '3'])->fresh()->weight);
        $this->assertSame(6, CompetitionTimeRange::create(['name' => 'Semester', 'weight' => '6'])->fresh()->weight);
    }

    public function test_encoder_has_bulk_create_associative_rule_and_load_test_scripts(): void
    {
        // L7.1: Encoder base64url round-trip across all modulo-4 lengths & invalid rejection
        foreach (['a', 'ab', 'abc', 'abcd', '{"sub":"123","exp":1999999999}+/?='] as $payload) {
            $encoded = Encoder::base64UrlEncode($payload);
            $this->assertTrue(Encoder::isBase64Url($encoded));
            $this->assertSame($payload, Encoder::base64UrlDecode($encoded));
        }
        $this->assertFalse(Encoder::base64UrlDecode('a')); // strlen % 4 === 1 is invalid base64
        $this->assertFalse(Encoder::base64UrlDecode('invalid!chars'));

        // L7.2: HasBulkCreate respects false return from saving/creating listener
        Event::listen('eloquent.creating: '.Degree::class, static fn (): bool => false);
        try {
            $created = Degree::createMany([
                ['code' => 'D3', 'name' => 'Diploma'],
            ]);
            $this->assertCount(0, $created);
            $this->assertDatabaseMissing('degrees', ['code' => 'D3']);
        } finally {
            Event::forget('eloquent.creating: '.Degree::class);
        }

        // L7.3: Associative accepts empty JSON object ([]) & keyed map, rejects sequential lists & scalars
        $rule = ['links' => [new Associative]];
        $this->assertFalse(Validator::make(['links' => []], $rule)->fails());
        $this->assertFalse(Validator::make(['links' => ['github' => 'https://github.com/infiniteuny']], $rule)->fails());
        $this->assertTrue(Validator::make(['links' => ['https://github.com/infiniteuny']], $rule)->fails());
        $this->assertTrue(Validator::make(['links' => 'not-an-array'], $rule)->fails());

        // L6: Load test invariants
        $helpersJs = (string) file_get_contents(base_path('tests/Load/lib/helpers.js'));
        $smokeJs = (string) file_get_contents(base_path('tests/Load/smoke-test.js'));
        $breakpointJs = (string) file_get_contents(base_path('tests/Load/breakpoint-test.js'));
        $runSh = (string) file_get_contents(base_path('tests/Load/run.sh'));
        $runPs1 = (string) file_get_contents(base_path('tests/Load/run.ps1'));

        $this->assertStringContainsString('res.status === 0 || res.status >= 400', $helpersJs);
        $this->assertStringContainsString('__VU', $helpersJs);
        $this->assertStringContainsString('throw new Error(', $smokeJs);
        $this->assertStringContainsString('throw new Error(', $breakpointJs);
        $this->assertStringNotContainsString('tests/k6/logs', $breakpointJs);
        $this->assertStringContainsString('tests/Load/logs', $breakpointJs);
        $this->assertStringContainsString('test-document.docx', $runSh);
        $this->assertStringNotContainsString('$ENV_FLAGS', $runSh);
        $this->assertStringContainsString('test-document.docx', $runPs1);
    }

    private function makeUser(string $name, string $username, string $email): User
    {
        $suffix = (string) random_int(100000, 999999);

        return User::create([
            'name' => $name,
            'username' => $username,
            'email_address' => $email,
            'phone_number' => '+62813'.$suffix,
            'student_id' => '24521'.$suffix,
            'major_id' => $this->major->id,
            'links' => (object) [],
            'start_date' => '2024-01-01',
            'end_date' => '2028-12-31',
            'is_member' => true,
            'is_extraordinary' => false,
        ]);
    }
}
