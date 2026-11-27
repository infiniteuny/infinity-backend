<?php

namespace Tests\Feature;

use App\Enums\OutlinePermission;
use App\Jobs\CreateDocsCollection;
use App\Jobs\CreateDocsGroup;
use App\Jobs\SyncCoreTeamDocsCollections;
use App\Jobs\SyncCoreTeamDocsGroups;
use App\Jobs\SyncXCoreTeamDocsGroups;
use App\Jobs\UpdateDocsCollection;
use App\Jobs\UpdateDocsGroup;
use App\Models\CoreTeam;
use App\Models\CoreTeamDivision;
use App\Models\CoreTeamMember;
use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Group;
use App\Models\GroupIntegration;
use App\Models\Major;
use App\Models\User;
use App\Services\OutlineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SyncDocsJobsTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = 'https://docs.infiniteuny.id';

    private const TOKEN = 'ol_api_test_token';

    private const CORE_TEAM_GROUP_ID = '11111111-1111-1111-1111-111111111111';

    private const EX_CORE_TEAM_GROUP_ID = '22222222-2222-2222-2222-222222222222';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.outline.base_url' => self::BASE_URL,
            'services.outline.token' => self::TOKEN,
            'services.outline.core_team_group_id' => self::CORE_TEAM_GROUP_ID,
            'services.outline.xcore_team_group_id' => self::EX_CORE_TEAM_GROUP_ID,
        ]);
    }

    public function test_sync_orchestrators_dispatch_group_based_create_and_update_jobs(): void
    {
        Bus::fake([
            CreateDocsGroup::class,
            UpdateDocsGroup::class,
            CreateDocsCollection::class,
            UpdateDocsCollection::class,
        ]);

        $now = now();

        $activeGroup = Group::create([
            'name' => 'Core Team 2025',
            'guard_name' => 'api',
        ]);
        CoreTeam::create([
            'year' => 2025,
            'is_active' => true,
            'group_id' => $activeGroup->id,
        ]);

        $inactiveGroup = Group::create([
            'name' => 'Core Team 2024',
            'guard_name' => 'api',
        ]);
        CoreTeam::create([
            'year' => 2024,
            'is_active' => false,
            'group_id' => $inactiveGroup->id,
        ]);
        GroupIntegration::create([
            'group_id' => $inactiveGroup->id,
            'docs_group_id' => '77777777-7777-7777-7777-777777777777',
            'docs_collection_id' => null,
            'docs_group_last_synced_at' => $now->copy()->subHour(),
            'docs_collection_last_synced_at' => null,
        ]);

        (new SyncCoreTeamDocsGroups)->handle();
        (new SyncXCoreTeamDocsGroups)->handle();
        (new SyncCoreTeamDocsCollections)->handle();

        Bus::assertDispatched(
            CreateDocsGroup::class,
            fn (CreateDocsGroup $job) => $job->group->is($activeGroup)
                && $job->docsParentIds === [self::CORE_TEAM_GROUP_ID]
                && $job->docsRemoveParentIds === [self::EX_CORE_TEAM_GROUP_ID]
        );

        Bus::assertDispatched(
            UpdateDocsGroup::class,
            fn (UpdateDocsGroup $job) => $job->group->is($inactiveGroup)
                && $job->docsParentIds === [self::EX_CORE_TEAM_GROUP_ID]
                && $job->docsRemoveParentIds === [self::CORE_TEAM_GROUP_ID]
        );

        Bus::assertDispatched(
            CreateDocsCollection::class,
            fn (CreateDocsCollection $job) => $job->group->is($inactiveGroup)
                && $job->collectionName === 'Core 2024'
                && $job->readOnlyGroupIds === [self::CORE_TEAM_GROUP_ID, self::EX_CORE_TEAM_GROUP_ID]
        );
    }

    public function test_create_docs_group_batches_user_lookup_invites_missing_and_reconciles_members(): void
    {
        $docsGroupId = '33333333-3333-3333-3333-333333333333';
        $aliceOutlineId = '55555555-5555-5555-5555-555555555555';
        $bobOutlineId = '66666666-6666-6666-6666-666666666666';
        $staleOutlineId = '99999999-9999-9999-9999-999999999999';

        Http::fake([
            self::BASE_URL.'/api/groups.list' => Http::response([
                'data' => ['groups' => [], 'groupMemberships' => []],
            ]),
            self::BASE_URL.'/api/groups.create' => Http::response([
                'data' => ['id' => $docsGroupId, 'name' => 'Core Team 2025'],
            ]),
            self::BASE_URL.'/api/users.list' => function (Request $request) use ($aliceOutlineId) {
                $this->assertSame('all', $request['filter']);
                $this->assertContains('alice@infiniteuny.id', $request['emails']);
                $this->assertContains('bob@infiniteuny.id', $request['emails']);

                return Http::response([
                    'data' => [[
                        'id' => $aliceOutlineId,
                        'name' => 'Alice',
                        'email' => 'alice@infiniteuny.id',
                        'role' => 'member',
                    ]],
                ]);
            },
            self::BASE_URL.'/api/users.invite' => Http::response([
                'data' => [
                    'users' => [[
                        'id' => $bobOutlineId,
                        'name' => 'Bob',
                        'email' => 'bob@infiniteuny.id',
                        'role' => 'member',
                    ]],
                ],
            ]),
            self::BASE_URL.'/api/groups.add_user' => Http::response([
                'data' => ['users' => [], 'groups' => [], 'groupMemberships' => []],
            ]),
            self::BASE_URL.'/api/groups.memberships' => Http::response([
                'data' => [
                    'users' => [
                        ['id' => $aliceOutlineId, 'name' => 'Alice'],
                        ['id' => $bobOutlineId, 'name' => 'Bob'],
                        ['id' => $staleOutlineId, 'name' => 'Stale'],
                    ],
                ],
            ]),
            self::BASE_URL.'/api/groups.remove_user' => Http::response([
                'data' => ['groups' => []],
            ]),
            self::BASE_URL.'/api/users.info' => Http::response([
                'data' => [
                    'id' => $staleOutlineId,
                    'name' => 'Stale',
                    'email' => 'stale@infiniteuny.id',
                ],
            ]),
        ]);

        $group = Group::create([
            'name' => 'Core Team 2025',
            'guard_name' => 'api',
        ]);
        $coreTeam = CoreTeam::create([
            'year' => 2025,
            'is_active' => true,
            'group_id' => $group->id,
        ]);

        $major = $this->createMajor();
        $division = CoreTeamDivision::create([
            'name' => 'Engineering',
            'priority' => 1,
        ]);

        $alice = $this->createUser($major, 'Alice', 'alice@infiniteuny.id');
        $bob = $this->createUser($major, 'Bob', 'bob@infiniteuny.id');

        CoreTeamMember::create([
            'core_team_id' => $coreTeam->id,
            'user_id' => $alice->id,
            'core_team_division_id' => $division->id,
            'photo' => 'photo.png',
        ]);
        CoreTeamMember::create([
            'core_team_id' => $coreTeam->id,
            'user_id' => $bob->id,
            'core_team_division_id' => $division->id,
            'photo' => 'photo.png',
        ]);

        $group->users()->sync([$alice->id, $bob->id]);

        (new CreateDocsGroup($group, [self::CORE_TEAM_GROUP_ID], [self::EX_CORE_TEAM_GROUP_ID], suppressInviteEmail: true))
            ->handle(app(OutlineService::class));

        $integration = $group->fresh()->integration;
        $this->assertNotNull($integration);
        $this->assertSame($docsGroupId, $integration->docs_group_id);
        $this->assertNotNull($integration->docs_group_last_synced_at);

        Http::assertSent(fn (Request $r) => $r->url() === self::BASE_URL.'/api/users.invite'
            && count($r['invites']) === 1
            && $r['invites'][0]['email'] === 'bob@infiniteuny.id');

        Http::assertSent(fn (Request $r) => $r->url() === self::BASE_URL.'/api/groups.remove_user'
            && $r['id'] === $docsGroupId
            && $r['userId'] === $staleOutlineId);

        Http::assertSent(fn (Request $r) => $r->url() === self::BASE_URL.'/api/groups.remove_user'
            && $r['id'] === self::CORE_TEAM_GROUP_ID
            && $r['userId'] === $staleOutlineId);
    }

    public function test_update_docs_group_handles_active_to_inactive_transition_and_preserves_multi_year_active_members(): void
    {
        $missingGroupId = 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa';
        $recreatedGroupId = 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb';
        $existingCollectionId = 'cccccccc-cccc-cccc-cccc-cccccccccccc';
        $charlieOutlineId = 'dddddddd-dddd-dddd-dddd-dddddddddddd';
        $dianaOutlineId = 'eeeeeeee-eeee-eeee-eeee-eeeeeeeeeeee';

        Http::fake([
            self::BASE_URL.'/api/groups.update' => Http::response(['ok' => false, 'error' => 'not_found'], 404),
            self::BASE_URL.'/api/groups.list' => Http::response([
                'data' => ['groups' => [], 'groupMemberships' => []],
            ]),
            self::BASE_URL.'/api/groups.create' => Http::response([
                'data' => ['id' => $recreatedGroupId, 'name' => 'Core Team 2024'],
            ]),
            self::BASE_URL.'/api/users.list' => Http::response([
                'data' => [
                    [
                        'id' => $charlieOutlineId,
                        'name' => 'Charlie',
                        'email' => 'charlie@infiniteuny.id',
                        'role' => 'member',
                    ],
                    [
                        'id' => $dianaOutlineId,
                        'name' => 'Diana',
                        'email' => 'diana@infiniteuny.id',
                        'role' => 'member',
                    ],
                ],
            ]),
            self::BASE_URL.'/api/groups.add_user' => Http::response([
                'data' => ['users' => [], 'groups' => [], 'groupMemberships' => []],
            ]),
            self::BASE_URL.'/api/groups.remove_user' => Http::response([
                'data' => ['groups' => []],
            ]),
            self::BASE_URL.'/api/groups.memberships' => Http::response([
                'data' => [
                    'users' => [
                        ['id' => $charlieOutlineId, 'name' => 'Charlie'],
                        ['id' => $dianaOutlineId, 'name' => 'Diana'],
                    ],
                ],
            ]),
        ]);

        $group2024 = Group::create(['name' => 'Core Team 2024', 'guard_name' => 'api']);
        $coreTeam2024 = CoreTeam::create([
            'year' => 2024,
            'is_active' => false,
            'group_id' => $group2024->id,
        ]);

        $group2025 = Group::create(['name' => 'Core Team 2025', 'guard_name' => 'api']);
        $coreTeam2025 = CoreTeam::create([
            'year' => 2025,
            'is_active' => true,
            'group_id' => $group2025->id,
        ]);

        GroupIntegration::create([
            'group_id' => $group2024->id,
            'docs_group_id' => $missingGroupId,
            'docs_collection_id' => $existingCollectionId,
            'docs_group_last_synced_at' => now()->subHour(),
            'docs_collection_last_synced_at' => now()->subHour(),
        ]);

        $major = $this->createMajor();
        $division = CoreTeamDivision::create(['name' => 'Design', 'priority' => 2]);

        // Charlie is ONLY in inactive 2024 -> should move to Ex Core Team and be removed from Core Team
        $charlie = $this->createUser($major, 'Charlie', 'charlie@infiniteuny.id');
        // Diana is in BOTH inactive 2024 and active 2025 -> should stay in Core Team, NOT added to Ex Core Team
        $diana = $this->createUser($major, 'Diana', 'diana@infiniteuny.id');

        CoreTeamMember::create([
            'core_team_id' => $coreTeam2024->id,
            'user_id' => $charlie->id,
            'core_team_division_id' => $division->id,
            'photo' => 'photo.png',
        ]);
        CoreTeamMember::create([
            'core_team_id' => $coreTeam2024->id,
            'user_id' => $diana->id,
            'core_team_division_id' => $division->id,
            'photo' => 'photo.png',
        ]);
        CoreTeamMember::create([
            'core_team_id' => $coreTeam2025->id,
            'user_id' => $diana->id,
            'core_team_division_id' => $division->id,
            'photo' => 'photo.png',
        ]);

        $group2024->users()->sync([$charlie->id, $diana->id]);

        (new UpdateDocsGroup($group2024, [self::EX_CORE_TEAM_GROUP_ID], [self::CORE_TEAM_GROUP_ID]))
            ->handle(app(OutlineService::class));

        $integration = $group2024->fresh()->integration;
        $this->assertSame($recreatedGroupId, $integration->docs_group_id);
        $this->assertNull($integration->docs_collection_last_synced_at);

        // Charlie moved to Ex Core Team and removed from Core Team
        Http::assertSent(fn (Request $r) => $r->url() === self::BASE_URL.'/api/groups.add_user'
            && $r['id'] === self::EX_CORE_TEAM_GROUP_ID
            && $r['userId'] === $charlieOutlineId);

        Http::assertSent(fn (Request $r) => $r->url() === self::BASE_URL.'/api/groups.remove_user'
            && $r['id'] === self::CORE_TEAM_GROUP_ID
            && $r['userId'] === $charlieOutlineId);

        // Diana remains active: added to Core Team 2024, NOT added to Ex Core Team, NOT removed from Core Team
        Http::assertSent(fn (Request $r) => $r->url() === self::BASE_URL.'/api/groups.add_user'
            && $r['id'] === $recreatedGroupId
            && $r['userId'] === $dianaOutlineId);

        Http::assertNotSent(fn (Request $r) => $r->url() === self::BASE_URL.'/api/groups.add_user'
            && $r['id'] === self::EX_CORE_TEAM_GROUP_ID
            && $r['userId'] === $dianaOutlineId);

        Http::assertNotSent(fn (Request $r) => $r->url() === self::BASE_URL.'/api/groups.remove_user'
            && $r['id'] === self::CORE_TEAM_GROUP_ID
            && $r['userId'] === $dianaOutlineId);
    }

    public function test_create_docs_collection_creates_private_collection_and_assigns_permissions(): void
    {
        $docsGroupId = '33333333-3333-3333-3333-333333333333';
        $docsCollectionId = '44444444-4444-4444-4444-444444444444';

        Http::fake([
            self::BASE_URL.'/api/collections.list' => Http::response(['data' => []]),
            self::BASE_URL.'/api/collections.create' => Http::response([
                'data' => ['id' => $docsCollectionId, 'name' => 'Core 2025'],
            ]),
            self::BASE_URL.'/api/collections.add_group' => Http::response([
                'data' => ['groupMemberships' => []],
            ]),
        ]);

        $group = Group::create([
            'name' => 'Core Team 2025',
            'guard_name' => 'api',
        ]);
        GroupIntegration::create([
            'group_id' => $group->id,
            'docs_group_id' => $docsGroupId,
        ]);

        (new CreateDocsCollection($group, 'Core 2025', [self::CORE_TEAM_GROUP_ID, self::EX_CORE_TEAM_GROUP_ID]))
            ->handle(app(OutlineService::class));

        $integration = $group->fresh()->integration;
        $this->assertSame($docsCollectionId, $integration->docs_collection_id);
        $this->assertNotNull($integration->docs_collection_last_synced_at);

        Http::assertSent(fn (Request $r) => $r->url() === self::BASE_URL.'/api/collections.create'
            && $r['name'] === 'Core 2025'
            && array_key_exists('permission', $r->data())
            && $r['permission'] === null);

        Http::assertSent(fn (Request $r) => $r->url() === self::BASE_URL.'/api/collections.add_group'
            && $r['id'] === $docsCollectionId
            && $r['groupId'] === $docsGroupId
            && $r['permission'] === OutlinePermission::READ_WRITE->value);

        Http::assertSent(fn (Request $r) => $r->url() === self::BASE_URL.'/api/collections.add_group'
            && $r['id'] === $docsCollectionId
            && $r['groupId'] === self::CORE_TEAM_GROUP_ID
            && $r['permission'] === OutlinePermission::READ->value);

        Http::assertSent(fn (Request $r) => $r->url() === self::BASE_URL.'/api/collections.add_group'
            && $r['id'] === $docsCollectionId
            && $r['groupId'] === self::EX_CORE_TEAM_GROUP_ID
            && $r['permission'] === OutlinePermission::READ->value);
    }

    public function test_create_docs_collection_locks_down_preexisting_public_collection(): void
    {
        $docsGroupId = '33333333-3333-3333-3333-333333333333';
        $docsCollectionId = '44444444-4444-4444-4444-444444444444';

        Http::fake([
            self::BASE_URL.'/api/collections.list' => Http::response([
                'data' => [[
                    'id' => $docsCollectionId,
                    'name' => 'Core 2025',
                    'permission' => 'read_write',
                ]],
            ]),
            self::BASE_URL.'/api/collections.update' => Http::response([
                'data' => [
                    'id' => $docsCollectionId,
                    'name' => 'Core 2025',
                    'permission' => null,
                ],
            ]),
            self::BASE_URL.'/api/collections.add_group' => Http::response([
                'data' => ['groupMemberships' => []],
            ]),
        ]);

        $group = Group::create([
            'name' => 'Core Team 2025',
            'guard_name' => 'api',
        ]);
        GroupIntegration::create([
            'group_id' => $group->id,
            'docs_group_id' => $docsGroupId,
        ]);

        (new CreateDocsCollection($group, 'Core 2025', [self::CORE_TEAM_GROUP_ID, self::EX_CORE_TEAM_GROUP_ID]))
            ->handle(app(OutlineService::class));

        Http::assertNotSent(fn (Request $r) => $r->url() === self::BASE_URL.'/api/collections.create');

        Http::assertSent(fn (Request $r) => $r->url() === self::BASE_URL.'/api/collections.update'
            && $r['id'] === $docsCollectionId
            && array_key_exists('permission', $r->data())
            && $r['permission'] === null);
    }

    public function test_update_docs_collection_preserves_existing_groups_and_owning_group_read_write(): void
    {
        $docsGroupId = '33333333-3333-3333-3333-333333333333';
        $docsCollectionId = '44444444-4444-4444-4444-444444444444';

        Http::fake([
            self::BASE_URL.'/api/collections.update' => Http::response([
                'data' => [
                    'id' => $docsCollectionId,
                    'name' => 'Core Team 2025',
                    'permission' => null,
                ],
            ]),
            self::BASE_URL.'/api/collections.add_group' => Http::response([
                'data' => ['groupMemberships' => []],
            ]),
        ]);

        $group = Group::create([
            'name' => 'Core Team 2025',
            'guard_name' => 'api',
        ]);
        GroupIntegration::create([
            'group_id' => $group->id,
            'docs_group_id' => $docsGroupId,
            'docs_collection_id' => $docsCollectionId,
            'docs_group_last_synced_at' => now()->subHour(),
            'docs_collection_last_synced_at' => now()->subHour(),
        ]);

        (new UpdateDocsCollection(
            $group,
            'Core Team 2025',
            [$docsGroupId, self::CORE_TEAM_GROUP_ID, self::EX_CORE_TEAM_GROUP_ID],
        ))->handle(app(OutlineService::class));

        Http::assertSent(fn (Request $r) => $r->url() === self::BASE_URL.'/api/collections.add_group'
            && $r['id'] === $docsCollectionId
            && $r['groupId'] === $docsGroupId
            && $r['permission'] === OutlinePermission::READ_WRITE->value);

        Http::assertNotSent(fn (Request $r) => $r->url() === self::BASE_URL.'/api/collections.add_group'
            && $r['groupId'] === $docsGroupId
            && $r['permission'] === OutlinePermission::READ->value);

        Http::assertNotSent(fn (Request $r) => $r->url() === self::BASE_URL.'/api/collections.remove_group');
    }

    private function createMajor(): Major
    {
        $degree = Degree::firstOrCreate(['name' => 'S1']);
        $faculty = Faculty::firstOrCreate(['name' => 'FT']);

        return Major::firstOrCreate(
            ['name' => 'Teknologi Informasi'],
            ['degree_id' => $degree->id, 'faculty_id' => $faculty->id],
        );
    }

    private function createUser(Major $major, string $name, string $email): User
    {
        return User::create([
            'name' => $name,
            'username' => strtolower($name).random_int(100, 999),
            'email_address' => $email,
            'phone_number' => '+628'.random_int(100000000, 999999999),
            'student_id' => (string) random_int(10000000000, 99999999999),
            'major_id' => $major->id,
            'links' => [],
            'start_date' => '2024-01-01',
            'end_date' => '2028-01-01',
            'is_member' => true,
            'is_extraordinary' => false,
        ]);
    }
}
