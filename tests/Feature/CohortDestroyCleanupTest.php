<?php

namespace Tests\Feature;

use App\Jobs\DeleteBlob;
use App\Jobs\DeleteDocsGroup;
use App\Jobs\DeleteSsoGroup;
use App\Models\CommunityGroup;
use App\Models\CommunityGroupAdmin;
use App\Models\CommunityGroupAdminMember;
use App\Models\CoreTeam;
use App\Models\CoreTeamDivision;
use App\Models\CoreTeamMember;
use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Group;
use App\Models\GroupIntegration;
use App\Models\Major;
use App\Models\Permission;
use App\Models\User;
use App\Services\OutlineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CohortDestroyCleanupTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = 'https://docs.infiniteuny.id';

    private const TOKEN = 'ol_api_test_token';

    private const CORE_TEAM_GROUP_ID = '11111111-1111-1111-1111-111111111111';

    private const EX_CORE_TEAM_GROUP_ID = '22222222-2222-2222-2222-222222222222';

    private const CG_ADMIN_GROUP_ID = '33333333-3333-3333-3333-333333333333';

    private const EX_CG_ADMIN_GROUP_ID = '44444444-4444-4444-4444-444444444444';

    private User $admin;

    private Major $major;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.outline.base_url' => self::BASE_URL,
            'services.outline.token' => self::TOKEN,
            'services.outline.core_team_group_id' => self::CORE_TEAM_GROUP_ID,
            'services.outline.xcore_team_group_id' => self::EX_CORE_TEAM_GROUP_ID,
            'services.outline.cg_admin_group_id' => self::CG_ADMIN_GROUP_ID,
            'services.outline.xcg_admin_group_id' => self::EX_CG_ADMIN_GROUP_ID,
        ]);

        $degree = Degree::create(['code' => 'S1', 'name' => 'Sarjana']);
        $faculty = Faculty::create(['code' => 'FT', 'name' => 'Teknik']);
        $this->major = Major::create([
            'code' => 'PTI',
            'name' => 'Pendidikan Teknik Informatika',
            'degree_id' => $degree->id,
            'faculty_id' => $faculty->id,
        ]);

        $this->admin = $this->createUser('Admin', 'adminuser', 'admin@infiniteuny.id', '24520241001', '+6281111111101');
        foreach (['delete-core-team', 'delete-community-group-admin', 'delete-group'] as $permName) {
            $this->admin->givePermissionTo(Permission::create([
                'name' => $permName,
                'guard_name' => 'api',
            ]));
        }
    }

    public function test_core_team_destroy_dispatches_sso_docs_and_member_blob_cleanup_without_touching_deleted_group(): void
    {
        Bus::fake([
            DeleteSsoGroup::class,
            DeleteDocsGroup::class,
            DeleteBlob::class,
        ]);

        $alice = $this->createUser('Alice', 'aliceuser', 'Alice@infiniteuny.id', '24520241002', '+6281111111102');
        $bob = $this->createUser('Bob', 'bobuser', 'bob@infiniteuny.id', '24520241003', '+6281111111103');

        $group = Group::create([
            'name' => 'Core Team 2024',
            'guard_name' => 'api',
            'is_managed' => true,
        ]);
        $group->forceFill(['sso_id' => 'sso-ct-2024'])->saveQuietly();
        $group->users()->attach([$alice->id, $bob->id]);

        GroupIntegration::create([
            'group_id' => $group->id,
            'docs_group_id' => 'docs-ct-2024',
            'docs_collection_id' => 'docs-col-ct-2024',
        ]);

        $coreTeam = CoreTeam::create([
            'year' => 2024,
            'is_active' => false,
            'group_id' => $group->id,
        ]);

        $division = CoreTeamDivision::create([
            'name' => 'Engineering',
            'priority' => 1,
        ]);

        $alicePhoto = '{"disk":"local","path":"core-teams/photos","name":"alice.png"}';
        $aliceAnim = '{"disk":"local","path":"core-teams/animations","name":"alice.webm"}';
        $bobPhoto = '{"disk":"local","path":"core-teams/photos","name":"bob.png"}';

        CoreTeamMember::create([
            'user_id' => $alice->id,
            'core_team_id' => $coreTeam->id,
            'core_team_division_id' => $division->id,
            'photo' => $alicePhoto,
            'animation' => $aliceAnim,
        ]);
        CoreTeamMember::create([
            'user_id' => $bob->id,
            'core_team_id' => $coreTeam->id,
            'core_team_division_id' => $division->id,
            'photo' => $bobPhoto,
            'animation' => null,
        ]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->actingAs($this->admin, 'api_headers')
            ->deleteJson("/v1/core-teams/{$coreTeam->id}");

        $queries = array_column(DB::getQueryLog(), 'query');
        DB::disableQueryLog();

        $response->assertOk();

        // Ensure $coreTeam->delete() did not issue a wasted UPDATE on groups right before deleting it
        foreach ($queries as $sql) {
            $this->assertStringNotContainsString('update "groups"', strtolower($sql));
        }

        $this->assertDatabaseMissing('core_teams', ['id' => $coreTeam->id]);
        $this->assertDatabaseMissing('groups', ['id' => $group->id]);
        $this->assertDatabaseMissing('group_integrations', ['group_id' => $group->id]);
        $this->assertDatabaseCount('core_team_members', 0);

        Bus::assertDispatched(DeleteSsoGroup::class, fn (DeleteSsoGroup $job) => $job->groupSsoId === 'sso-ct-2024');
        Bus::assertDispatched(
            DeleteDocsGroup::class,
            function (DeleteDocsGroup $job) use ($group) {
                $emails = $job->fallbackMemberEmails;
                sort($emails);

                return $job->docsGroupId === 'docs-ct-2024'
                    && $job->groupId === $group->id
                    && $job->sharedGroupIds === [self::CORE_TEAM_GROUP_ID, self::EX_CORE_TEAM_GROUP_ID]
                    && $emails === ['alice@infiniteuny.id', 'bob@infiniteuny.id'];
            }
        );

        Bus::assertDispatchedTimes(DeleteBlob::class, 3);
        Bus::assertDispatched(DeleteBlob::class, fn (DeleteBlob $job) => $job->manifest === $alicePhoto);
        Bus::assertDispatched(DeleteBlob::class, fn (DeleteBlob $job) => $job->manifest === $aliceAnim);
        Bus::assertDispatched(DeleteBlob::class, fn (DeleteBlob $job) => $job->manifest === $bobPhoto);
    }

    public function test_community_group_admin_destroy_dispatches_sso_docs_and_deduplicated_member_blob_cleanup(): void
    {
        Bus::fake([
            DeleteSsoGroup::class,
            DeleteDocsGroup::class,
            DeleteBlob::class,
        ]);

        $alice = $this->createUser('Alice', 'alicecg', 'alice@infiniteuny.id', '24520241004', '+6281111111104');

        $group = Group::create([
            'name' => 'CG Admin 2024',
            'guard_name' => 'api',
            'is_managed' => true,
        ]);
        $group->forceFill(['sso_id' => 'sso-cga-2024'])->saveQuietly();
        $group->users()->attach([$alice->id]);

        GroupIntegration::create([
            'group_id' => $group->id,
            'docs_group_id' => 'docs-cga-2024',
        ]);

        $cgAdmin = CommunityGroupAdmin::create([
            'year' => 2024,
            'is_active' => false,
            'group_id' => $group->id,
        ]);

        $cg1 = CommunityGroup::create([
            'name' => 'Web Dev',
            'priority' => 1,
            'description' => 'Web development',
            'logo' => '{"disk":"local","path":"cg/logos","name":"web.png"}',
            'is_active' => true,
        ]);
        $cg2 = CommunityGroup::create([
            'name' => 'Mobile Dev',
            'priority' => 2,
            'description' => 'Mobile development',
            'logo' => '{"disk":"local","path":"cg/logos","name":"mobile.png"}',
            'is_active' => true,
        ]);

        $photo1 = '{"disk":"local","path":"cga/photos","name":"alice-web.png"}';
        $photo2 = '{"disk":"local","path":"cga/photos","name":"alice-mobile.png"}';
        $anim2 = '{"disk":"local","path":"cga/animations","name":"alice-mobile.webm"}';

        CommunityGroupAdminMember::create([
            'user_id' => $alice->id,
            'community_group_admin_id' => $cgAdmin->id,
            'community_group_id' => $cg1->id,
            'photo' => $photo1,
            'animation' => null,
        ]);
        CommunityGroupAdminMember::create([
            'user_id' => $alice->id,
            'community_group_admin_id' => $cgAdmin->id,
            'community_group_id' => $cg2->id,
            'photo' => $photo2,
            'animation' => $anim2,
        ]);

        $response = $this->actingAs($this->admin, 'api_headers')
            ->deleteJson("/v1/community-group-admins/{$cgAdmin->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('community_group_admins', ['id' => $cgAdmin->id]);
        $this->assertDatabaseMissing('groups', ['id' => $group->id]);
        $this->assertDatabaseMissing('group_integrations', ['group_id' => $group->id]);
        $this->assertDatabaseCount('community_group_admin_members', 0);

        Bus::assertDispatched(DeleteSsoGroup::class, fn (DeleteSsoGroup $job) => $job->groupSsoId === 'sso-cga-2024');
        Bus::assertDispatched(
            DeleteDocsGroup::class,
            fn (DeleteDocsGroup $job) => $job->docsGroupId === 'docs-cga-2024'
                && $job->groupId === $group->id
                && $job->sharedGroupIds === [self::CG_ADMIN_GROUP_ID, self::EX_CG_ADMIN_GROUP_ID]
                && $job->fallbackMemberEmails === ['alice@infiniteuny.id']
        );

        Bus::assertDispatchedTimes(DeleteBlob::class, 3);
        Bus::assertDispatched(DeleteBlob::class, fn (DeleteBlob $job) => $job->manifest === $photo1);
        Bus::assertDispatched(DeleteBlob::class, fn (DeleteBlob $job) => $job->manifest === $photo2);
        Bus::assertDispatched(DeleteBlob::class, fn (DeleteBlob $job) => $job->manifest === $anim2);
    }

    public function test_delete_docs_group_batches_db_queries_diffs_shared_group_writes_and_never_deletes_collection(): void
    {
        $docsGroupId = '77777777-7777-7777-7777-777777777777';
        $soleOutlineId = 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa';
        $activeMultiOutlineId = 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb';
        $inactiveMultiOutlineId = 'cccccccc-cccc-cccc-cccc-cccccccccccc';
        $blankEmailOutlineId = 'dddddddd-dddd-dddd-dddd-dddddddddddd';

        // Sole member had only the deleted 2023 cohort (now zero cohorts in DB)
        $this->createUser('Sole', 'soleuser', 'sole@infiniteuny.id', '24520241010', '+6281111111110');

        // Active multi-cohort member still belongs to active Core Team 2025
        $activeMulti = $this->createUser('ActiveMulti', 'activemulti', 'active@infiniteuny.id', '24520241011', '+6281111111111');
        $group2025 = Group::create(['name' => 'Core Team 2025', 'guard_name' => 'api', 'is_managed' => true]);
        $ct2025 = CoreTeam::create(['year' => 2025, 'is_active' => true, 'group_id' => $group2025->id]);
        $division = CoreTeamDivision::create(['name' => 'Tech', 'priority' => 1]);
        CoreTeamMember::create([
            'user_id' => $activeMulti->id,
            'core_team_id' => $ct2025->id,
            'core_team_division_id' => $division->id,
            'photo' => '{"disk":"local","path":"ct","name":"active.png"}',
        ]);

        // Inactive multi-cohort member still belongs to inactive Core Team 2024
        $inactiveMulti = $this->createUser('InactiveMulti', 'inactivemulti', 'inactive@infiniteuny.id', '24520241012', '+6281111111112');
        $group2024 = Group::create(['name' => 'Core Team 2024', 'guard_name' => 'api', 'is_managed' => true]);
        $ct2024 = CoreTeam::create(['year' => 2024, 'is_active' => false, 'group_id' => $group2024->id]);
        CoreTeamMember::create([
            'user_id' => $inactiveMulti->id,
            'core_team_id' => $ct2024->id,
            'core_team_division_id' => $division->id,
            'photo' => '{"disk":"local","path":"ct","name":"inactive.png"}',
        ]);

        $addedToShared = [];
        $removedFromShared = [];
        $deletedGroupIds = [];
        $deletedCollectionCalled = false;

        Http::fake([
            self::BASE_URL.'/api/groups.memberships' => function (Request $request) use (
                $docsGroupId,
                $soleOutlineId,
                $activeMultiOutlineId,
                $inactiveMultiOutlineId,
                $blankEmailOutlineId,
            ) {
                $users = match ($request['id']) {
                    $docsGroupId => [
                        ['id' => $soleOutlineId, 'name' => 'Sole', 'email' => 'sole@infiniteuny.id'],
                        ['id' => $activeMultiOutlineId, 'name' => 'ActiveMulti', 'email' => 'active@infiniteuny.id'],
                        ['id' => $inactiveMultiOutlineId, 'name' => 'InactiveMulti', 'email' => 'inactive@infiniteuny.id'],
                        ['id' => $blankEmailOutlineId, 'name' => 'NoEmail', 'email' => ''],
                    ],
                    // activeMulti is ALREADY in Core Team; inactiveMulti was wrongly still in Core Team
                    self::CORE_TEAM_GROUP_ID => [
                        ['id' => $activeMultiOutlineId, 'name' => 'ActiveMulti', 'email' => 'active@infiniteuny.id'],
                        ['id' => $inactiveMultiOutlineId, 'name' => 'InactiveMulti', 'email' => 'inactive@infiniteuny.id'],
                    ],
                    // sole & blankEmail are in Ex Core Team; inactiveMulti is NOT yet in Ex Core Team
                    self::EX_CORE_TEAM_GROUP_ID => [
                        ['id' => $soleOutlineId, 'name' => 'Sole', 'email' => 'sole@infiniteuny.id'],
                        ['id' => $blankEmailOutlineId, 'name' => 'NoEmail', 'email' => ''],
                    ],
                    default => [],
                };

                return Http::response(['data' => ['users' => $users]]);
            },
            self::BASE_URL.'/api/users.info' => Http::response([
                'data' => ['id' => $blankEmailOutlineId, 'name' => 'NoEmail', 'email' => ''],
            ]),
            self::BASE_URL.'/api/groups.add_user' => function (Request $request) use (&$addedToShared) {
                $addedToShared[] = [$request['id'], $request['userId']];

                return Http::response(['data' => []]);
            },
            self::BASE_URL.'/api/groups.remove_user' => function (Request $request) use (&$removedFromShared) {
                $removedFromShared[] = [$request['id'], $request['userId']];

                return Http::response(['data' => []]);
            },
            self::BASE_URL.'/api/groups.delete' => function (Request $request) use (&$deletedGroupIds) {
                $deletedGroupIds[] = $request['id'];

                return Http::response(['success' => true]);
            },
            self::BASE_URL.'/api/collections.delete' => function () use (&$deletedCollectionCalled) {
                $deletedCollectionCalled = true;

                return Http::response(['success' => true]);
            },
        ]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $job = new DeleteDocsGroup(
            docsGroupId: $docsGroupId,
            groupId: 'local-deleted-group-id',
            sharedGroupIds: [self::CORE_TEAM_GROUP_ID, self::EX_CORE_TEAM_GROUP_ID],
            fallbackMemberEmails: ['sole@infiniteuny.id', 'active@infiniteuny.id', 'inactive@infiniteuny.id'],
        );
        $job->handle(app(OutlineService::class));

        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        // Batch-loaded in exactly 3 SQL queries (users + coreTeams + communityGroupAdmins), not 3*N
        $this->assertSame(3, $queryCount);

        $this->assertFalse($deletedCollectionCalled, 'ADR-0007 forbids deleting Outline collections on cohort deletion.');
        $this->assertSame([$docsGroupId], $deletedGroupIds);

        // Delta-only writes: activeMulti was ALREADY in Core Team and NOT in Ex Core Team -> 0 write calls!
        // Only inactiveMulti needed adding to Ex Core Team.
        $this->assertSame([[self::EX_CORE_TEAM_GROUP_ID, $inactiveMultiOutlineId]], $addedToShared);

        // Only actual memberships removed (sole & blankEmail from Ex Core Team; inactiveMulti from Core Team)
        $this->assertCount(3, $removedFromShared);
        $this->assertContains([self::EX_CORE_TEAM_GROUP_ID, $soleOutlineId], $removedFromShared);
        $this->assertContains([self::EX_CORE_TEAM_GROUP_ID, $blankEmailOutlineId], $removedFromShared);
        $this->assertContains([self::CORE_TEAM_GROUP_ID, $inactiveMultiOutlineId], $removedFromShared);
    }

    public function test_delete_docs_group_reconciles_fallback_emails_and_is_idempotent_when_group_already_404(): void
    {
        $docsGroupId = '88888888-8888-8888-8888-888888888888';
        $soleOutlineId = 'eeeeeeee-eeee-eeee-eeee-eeeeeeeeeeee';

        $this->createUser('Sole', 'solefallback', 'sole@infiniteuny.id', '24520241020', '+6281111111120');

        $removedFromShared = [];
        $deleteGroupCalled = false;

        Http::fake([
            self::BASE_URL.'/api/groups.memberships' => function (Request $request) use ($docsGroupId, $soleOutlineId) {
                if ($request['id'] === $docsGroupId) {
                    return Http::response(['error' => 'not_found'], 404);
                }

                if ($request['id'] === self::EX_CORE_TEAM_GROUP_ID) {
                    return Http::response([
                        'data' => [
                            'users' => [
                                ['id' => $soleOutlineId, 'name' => 'Sole', 'email' => 'sole@infiniteuny.id'],
                            ],
                        ],
                    ]);
                }

                return Http::response(['data' => ['users' => []]]);
            },
            self::BASE_URL.'/api/users.list' => Http::response([
                'data' => [
                    ['id' => $soleOutlineId, 'name' => 'Sole', 'email' => 'sole@infiniteuny.id'],
                ],
            ]),
            self::BASE_URL.'/api/groups.remove_user' => function (Request $request) use (&$removedFromShared) {
                $removedFromShared[] = [$request['id'], $request['userId']];

                return Http::response(['data' => []]);
            },
            self::BASE_URL.'/api/groups.delete' => function () use (&$deleteGroupCalled) {
                $deleteGroupCalled = true;

                return Http::response(['success' => true]);
            },
        ]);

        $job = new DeleteDocsGroup(
            docsGroupId: $docsGroupId,
            groupId: 'local-deleted-group-id',
            sharedGroupIds: [self::CORE_TEAM_GROUP_ID, self::EX_CORE_TEAM_GROUP_ID],
            fallbackMemberEmails: ['sole@infiniteuny.id'],
        );
        $job->handle(app(OutlineService::class));

        $this->assertFalse($deleteGroupCalled);
        $this->assertSame([[self::EX_CORE_TEAM_GROUP_ID, $soleOutlineId]], $removedFromShared);
    }

    public function test_group_destroy_skips_member_lookup_and_deletes_docs_group_with_single_rpc_call(): void
    {
        $group = Group::create([
            'name' => 'Custom Working Group',
            'guard_name' => 'api',
            'is_managed' => false,
        ]);
        $group->forceFill(['sso_id' => 'sso-custom-wg'])->saveQuietly();
        GroupIntegration::create([
            'group_id' => $group->id,
            'docs_group_id' => 'docs-custom-wg',
        ]);

        Bus::fake([DeleteSsoGroup::class, DeleteDocsGroup::class]);

        $response = $this->actingAs($this->admin, 'api_headers')
            ->deleteJson("/v1/groups/{$group->id}");

        $response->assertOk();

        Bus::assertDispatched(DeleteSsoGroup::class, fn (DeleteSsoGroup $job) => $job->groupSsoId === 'sso-custom-wg');

        $dispatchedDocsJob = null;
        Bus::assertDispatched(DeleteDocsGroup::class, function (DeleteDocsGroup $job) use ($group, &$dispatchedDocsJob) {
            $dispatchedDocsJob = $job;

            return $job->docsGroupId === 'docs-custom-wg'
                && $job->groupId === $group->id
                && $job->sharedGroupIds === null;
        });

        $membershipsCalled = false;
        $deletedIds = [];

        Http::fake([
            self::BASE_URL.'/api/groups.memberships' => function () use (&$membershipsCalled) {
                $membershipsCalled = true;

                return Http::response(['data' => ['users' => []]]);
            },
            self::BASE_URL.'/api/groups.delete' => function (Request $request) use (&$deletedIds) {
                $deletedIds[] = $request['id'];

                return Http::response(['success' => true]);
            },
        ]);

        $dispatchedDocsJob->handle(app(OutlineService::class));

        $this->assertFalse($membershipsCalled);
        $this->assertSame(['docs-custom-wg'], $deletedIds);
    }

    private function createUser(
        string $name,
        string $username,
        string $email,
        string $studentId,
        string $phone,
    ): User {
        return User::create([
            'name' => $name,
            'username' => $username,
            'email_address' => $email,
            'phone_number' => $phone,
            'student_id' => $studentId,
            'major_id' => $this->major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);
    }
}
