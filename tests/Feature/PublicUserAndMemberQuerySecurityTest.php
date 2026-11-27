<?php

namespace Tests\Feature;

use App\Enums\StorageVisibility;
use App\Facades\Storage;
use App\Models\CommunityGroup;
use App\Models\CommunityGroupAdmin;
use App\Models\CompetitionTeamType;
use App\Models\CoreTeam;
use App\Models\CoreTeamDivision;
use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Group;
use App\Models\Major;
use App\Models\Permission;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage as LaravelStorage;
use Tests\TestCase;

class PublicUserAndMemberQuerySecurityTest extends TestCase
{
    use RefreshDatabase;

    private Major $major;

    private User $alice;

    private User $malice;

    private CommunityGroup $communityGroup;

    private CommunityGroupAdmin $communityGroupAdmin;

    private CoreTeam $coreTeam;

    private Team $team;

    protected function setUp(): void
    {
        parent::setUp();

        LaravelStorage::fake('local');

        $degree = Degree::create([
            'code' => 'S1',
            'name' => 'Sarjana',
        ]);
        $faculty = Faculty::create([
            'code' => 'FT',
            'name' => 'Teknik',
        ]);
        $this->major = Major::create([
            'code' => 'PTI',
            'name' => 'Pendidikan Teknik Informatika',
            'degree_id' => $degree->id,
            'faculty_id' => $faculty->id,
        ]);

        $this->alice = User::create([
            'name' => 'Alice Putri',
            'username' => 'aliceputri',
            'email_address' => 'Alice.Putri@student.uny.ac.id',
            'phone_number' => '+6281111111111',
            'student_id' => '24520241001',
            'major_id' => $this->major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);
        $this->alice->sso_id = '101';
        $this->alice->saveQuietly();

        $this->malice = User::create([
            'name' => 'Malice Putra',
            'username' => 'maliceputra',
            'email_address' => 'malice.Alice.Putri@student.uny.ac.id',
            'phone_number' => '+6282222222222',
            'student_id' => '9924520241001',
            'major_id' => $this->major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);
        $this->malice->sso_id = '102';
        $this->malice->saveQuietly();

        $role = Group::create([
            'name' => 'Administrator',
            'guard_name' => 'api',
            'is_managed' => true,
        ]);
        $permission = Permission::create([
            'name' => 'read-team-member',
            'guard_name' => 'api',
        ]);
        $this->alice->assignRole($role);
        $this->alice->givePermissionTo($permission);

        $logo = Storage::store(
            UploadedFile::fake()->image('logo.png'),
            'community-groups/logos',
            StorageVisibility::PUBLIC,
        );
        $photo = Storage::store(
            UploadedFile::fake()->image('photo.png'),
            'members/photos',
            StorageVisibility::PUBLIC,
        );

        $this->communityGroup = CommunityGroup::create([
            'name' => 'Software Engineering',
            'description' => 'SE Community Group',
            'logo' => $logo,
            'priority' => 1,
            'is_active' => true,
        ]);
        $this->communityGroup->members()->attach([$this->alice->id, $this->malice->id]);

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
        $this->communityGroupAdmin->members()->attach($this->alice->id, [
            'community_group_id' => $this->communityGroup->id,
            'photo' => $photo,
            'animation' => null,
        ]);
        $this->communityGroupAdmin->members()->attach($this->malice->id, [
            'community_group_id' => $this->communityGroup->id,
            'photo' => $photo,
            'animation' => null,
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
        $division = CoreTeamDivision::create([
            'name' => 'Engineering',
            'priority' => 1,
        ]);
        $this->coreTeam->members()->attach($this->alice->id, [
            'core_team_division_id' => $division->id,
            'photo' => $photo,
            'animation' => null,
        ]);
        $this->coreTeam->members()->attach($this->malice->id, [
            'core_team_division_id' => $division->id,
            'photo' => $photo,
            'animation' => null,
        ]);

        $teamType = CompetitionTeamType::create([
            'name' => 'Team',
            'weight' => 1,
        ]);
        $this->team = Team::create([
            'name' => 'Team Infinity',
            'leader_id' => $this->alice->id,
            'team_type_id' => $teamType->id,
        ]);
        $this->team->members()->sync([$this->alice->id, $this->malice->id]);
    }

    public function test_unauthenticated_requests_allow_single_exact_case_insensitive_email_and_student_id_without_leaking_partial_or_batch_matches(): void
    {
        $endpoints = [
            ['/v1/users', 'users'],
            ["/v1/community-groups/{$this->communityGroup->id}/members", 'community_group_members'],
            ["/v1/community-group-admins/{$this->communityGroupAdmin->id}/members", 'community_group_admin_members'],
            ["/v1/core-teams/{$this->coreTeam->id}/members", 'core_team_members'],
        ];

        foreach ($endpoints as [$uri, $collectionKey]) {
            // Exact case-insensitive email match returns ONLY Alice (not Malice whose email contains Alice's email)
            $emailResponse = $this->getJson("{$uri}?filters[email_address]=alice.putri@student.uny.ac.id");
            $emailResponse->assertOk();
            $items = $emailResponse->json("data.{$collectionKey}");
            $this->assertCount(1, $items, "Expected exact email lookup on {$uri} to return 1 item");
            $this->assertSame($this->alice->id, $items[0]['id']);
            $this->assertSame('REDACTED', $items[0]['email_address']);

            // Exact student_id match returns ONLY Alice (not Malice whose student_id ends with Alice's student_id)
            $nimResponse = $this->getJson("{$uri}?filters[student_id]=24520241001");
            $nimResponse->assertOk();
            $nimItems = $nimResponse->json("data.{$collectionKey}");
            $this->assertCount(1, $nimItems, "Expected exact student_id lookup on {$uri} to return 1 item");
            $this->assertSame($this->alice->id, $nimItems[0]['id']);
            $this->assertSame('REDACTED', $nimItems[0]['student_id']);

            // Partial email & partial student_id return 0 items unauthenticated
            $this->getJson("{$uri}?filters[email_address]=@student.uny.ac.id")
                ->assertOk()
                ->assertJsonCount(0, "data.{$collectionKey}");
            $this->getJson("{$uri}?filters[student_id]=2452024")
                ->assertOk()
                ->assertJsonCount(0, "data.{$collectionKey}");

            // Comma-delimited and array batch probes return 0 items unauthenticated
            $this->getJson("{$uri}?filters[email_address]=alice.putri@student.uny.ac.id,malice.alice.putri@student.uny.ac.id")
                ->assertOk()
                ->assertJsonCount(0, "data.{$collectionKey}");
            $this->getJson("{$uri}?filters[email_address][]=alice.putri@student.uny.ac.id")
                ->assertOk()
                ->assertJsonCount(0, "data.{$collectionKey}");
        }
    }

    public function test_unauthenticated_requests_block_rbac_includes_restricted_pii_filters_and_pii_sorts(): void
    {
        $indexEndpoints = [
            '/v1/users',
            "/v1/community-groups/{$this->communityGroup->id}/members",
            "/v1/community-group-admins/{$this->communityGroupAdmin->id}/members",
            "/v1/core-teams/{$this->coreTeam->id}/members",
        ];

        foreach ($indexEndpoints as $uri) {
            $this->getJson("{$uri}?includes=groups")->assertStatus(400);
            $this->getJson("{$uri}?includes=permissions")->assertStatus(400);
            $this->getJson("{$uri}?filters[phone_number]=628")->assertStatus(400);
            $this->getJson("{$uri}?filters[sso_id]=101")->assertStatus(400);
            $this->getJson("{$uri}?sorts=email_address")->assertStatus(400);
            $this->getJson("{$uri}?sorts=-phone_number")->assertStatus(400);
            $this->getJson("{$uri}?sorts=student_id")->assertStatus(400);

            // Publicly allowed filters & sorts (used by landing & public views) remain 200 OK
            $this->getJson("{$uri}?filters[is_member]=1&filters[username]=alice&sorts=username")->assertOk();
        }

        $cgMemberPivotId = $this->communityGroup->members()->wherePivot('user_id', $this->alice->id)->firstOrFail()->membership->id;
        $cgaMemberPivotId = $this->communityGroupAdmin->members()->wherePivot('user_id', $this->alice->id)->firstOrFail()->membership->id;
        $ctMemberPivotId = $this->coreTeam->members()->wherePivot('user_id', $this->alice->id)->firstOrFail()->membership->id;

        foreach ([
            "/v1/users/{$this->alice->id}",
            "/v1/community-group-members/{$cgMemberPivotId}",
            "/v1/community-group-admin-members/{$cgaMemberPivotId}",
            "/v1/core-team-members/{$ctMemberPivotId}",
        ] as $showUri) {
            $this->getJson("{$showUri}?includes=groups")->assertStatus(400);
            $this->getJson("{$showUri}?includes=permissions")->assertStatus(400);
            $this->getJson("{$showUri}?includes=major,major.degree,major.faculty,personas")->assertOk();
        }
    }

    public function test_authenticated_requests_allow_partial_pii_filters_sorts_rbac_includes_and_member_username_sort(): void
    {
        $semiPublicGuard = config('auth.defaults.semi_public_guard');
        $defaultGuard = config('auth.defaults.guard');

        $this->actingAs($this->alice, $semiPublicGuard);

        $endpoints = [
            ['/v1/users', 'users'],
            ["/v1/community-groups/{$this->communityGroup->id}/members", 'community_group_members'],
            ["/v1/community-group-admins/{$this->communityGroupAdmin->id}/members", 'community_group_admin_members'],
            ["/v1/core-teams/{$this->coreTeam->id}/members", 'core_team_members'],
        ];

        foreach ($endpoints as [$uri, $collectionKey]) {
            // Partial email_address matches both Alice and Malice, and PII fields are unredacted when authenticated on semi_public_guard
            $response = $this->getJson("{$uri}?filters[email_address]=@student.uny.ac.id&sorts=email_address&includes=groups,permissions");
            $response->assertOk()
                ->assertJsonCount(2, "data.{$collectionKey}");
            $this->assertNotSame('REDACTED', $response->json("data.{$collectionKey}.0.email_address"));

            // Partial phone_number, partial student_id, exact sso_id, and username sort all succeed
            $this->getJson("{$uri}?filters[phone_number]=8111&filters[student_id]=24520&filters[sso_id]=101&sorts=-username")
                ->assertOk()
                ->assertJsonCount(1, "data.{$collectionKey}");
        }

        // TeamMemberController also supports username filter & sort for dashboard TeamMembersList
        $this->actingAs($this->alice, $defaultGuard);
        $this->getJson("/v1/teams/{$this->team->id}/members?filters[username]=aliceputri&sorts=username")
            ->assertOk()
            ->assertJsonCount(1, 'data.team_members');
    }
}
