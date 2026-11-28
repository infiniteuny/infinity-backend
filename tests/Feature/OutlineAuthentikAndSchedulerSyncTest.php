<?php

namespace Tests\Feature;

use App\Data\Authentik\User as AuthentikUserData;
use App\Jobs\CreateDocsCollection;
use App\Jobs\CreateDocsGroup;
use App\Jobs\CreateSsoGroup;
use App\Jobs\CreateSsoUser;
use App\Jobs\SyncActiveInactiveMemberGroup;
use App\Jobs\SyncActiveInactiveMemberSsoGroup;
use App\Jobs\SyncAdminSsoGroup;
use App\Jobs\SyncCGAdminMembersGroup;
use App\Jobs\SyncCGAdminSsoGroups;
use App\Jobs\SyncCoreTeamDocsCollections;
use App\Jobs\SyncCoreTeamDocsGroups;
use App\Jobs\SyncCoreTeamMembersGroup;
use App\Jobs\SyncCoreTeamSsoGroups;
use App\Jobs\SyncMemberGroup;
use App\Jobs\SyncMemberSsoGroup;
use App\Jobs\SyncSsoUsers;
use App\Jobs\SyncXCGAdminSsoGroups;
use App\Jobs\SyncXCoreTeamDocsGroups;
use App\Jobs\SyncXCoreTeamSsoGroups;
use App\Jobs\UpdateDocsCollection;
use App\Jobs\UpdateDocsGroup;
use App\Jobs\UpdateSsoGroup;
use App\Jobs\UpdateSsoUser;
use App\Models\CommunityGroupAdmin;
use App\Models\CoreTeam;
use App\Models\CoreTeamDivision;
use App\Models\CoreTeamMember;
use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Group;
use App\Models\GroupIntegration;
use App\Models\Major;
use App\Models\User;
use App\Services\AuthentikService;
use App\Services\OutlineService;
use Carbon\CarbonInterface;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OutlineAuthentikAndSchedulerSyncTest extends TestCase
{
    use RefreshDatabase;

    private const OUTLINE_URL = 'https://docs.infiniteuny.id';

    private const AUTHENTIK_URL = 'https://auth.infiniteuny.id';

    private const CORE_TEAM_DOCS_GROUP_ID = '11111111-1111-1111-1111-111111111111';

    private const EX_CORE_TEAM_DOCS_GROUP_ID = '22222222-2222-2222-2222-222222222222';

    private Major $major;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.outline.base_url' => self::OUTLINE_URL,
            'services.outline.token' => 'ol_test_token',
            'services.outline.core_team_group_id' => self::CORE_TEAM_DOCS_GROUP_ID,
            'services.outline.xcore_team_group_id' => self::EX_CORE_TEAM_DOCS_GROUP_ID,
            'services.authentik.base_url' => self::AUTHENTIK_URL,
            'services.authentik.token' => 'ak_test_token',
        ]);

        $degree = Degree::create(['code' => 'S1', 'name' => 'Sarjana']);
        $faculty = Faculty::create(['code' => 'FT', 'name' => 'Teknik']);
        $this->major = Major::create([
            'code' => 'PTI',
            'name' => 'Pendidikan Teknik Informatika',
            'degree_id' => $degree->id,
            'faculty_id' => $faculty->id,
        ]);
    }

    public function test_outline_service_invite_users_short_circuits_empty_array(): void
    {
        Http::fake();

        $result = app(OutlineService::class)->inviteUsers([]);

        $this->assertCount(0, $result);
        Http::assertNothingSent();
    }

    public function test_outline_docs_group_paginates_query_lookup_removes_blank_email_stale_users_and_trims_local_emails(): void
    {
        $adoptedGroupId = '33333333-3333-3333-3333-333333333333';
        $blankEmailUserId = 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa';
        $paddedEmailUserId = 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb';

        $pageOneGroups = [];
        for ($i = 1; $i <= 100; $i++) {
            $pageOneGroups[] = [
                'id' => sprintf('00000000-0000-0000-0000-%012d', $i),
                'name' => "Core Team 2025 Extra {$i}",
            ];
        }

        Http::fake([
            self::OUTLINE_URL.'/api/groups.list' => function (Request $request) use ($pageOneGroups, $adoptedGroupId) {
                $this->assertSame('Core Team 2025', $request['query']);
                $this->assertArrayNotHasKey('name', $request->data());

                $offset = (int) ($request['offset'] ?? 0);
                if ($offset === 0) {
                    return Http::response(['data' => ['groups' => $pageOneGroups]]);
                }

                return Http::response([
                    'data' => [
                        'groups' => [[
                            'id' => $adoptedGroupId,
                            'name' => 'Core Team 2025',
                        ]],
                    ],
                ]);
            },
            self::OUTLINE_URL.'/api/groups.memberships' => Http::response([
                'data' => [
                    'users' => [
                        ['id' => $blankEmailUserId, 'name' => 'Suspended Ghost'],
                        ['id' => $paddedEmailUserId, 'name' => 'Active Multi Cohort'],
                    ],
                ],
            ]),
            self::OUTLINE_URL.'/api/groups.remove_user' => Http::response(['data' => ['groups' => []]]),
            self::OUTLINE_URL.'/api/groups.add_user' => Http::response(['data' => ['groups' => []]]),
            self::OUTLINE_URL.'/api/users.info' => function (Request $request) use ($blankEmailUserId) {
                if ($request['id'] === $blankEmailUserId) {
                    return Http::response([
                        'data' => [
                            'id' => $blankEmailUserId,
                            'name' => 'Suspended Ghost',
                            'email' => '',
                        ],
                    ]);
                }

                return Http::response([
                    'data' => [
                        'id' => $request['id'],
                        'name' => 'Active Multi Cohort',
                        'email' => 'alice@infiniteuny.id',
                    ],
                ]);
            },
        ]);

        // Group 2025 (inactive, empty local members) + Group 2026 (active, contains Alice whose DB email has whitespace)
        $group2025 = Group::create(['name' => 'Core Team 2025', 'is_managed' => true]);
        CoreTeam::create(['year' => 2025, 'is_active' => false, 'group_id' => $group2025->id]);

        $group2026 = Group::create(['name' => 'Core Team 2026', 'is_managed' => true]);
        $activeTeam2026 = CoreTeam::create(['year' => 2026, 'is_active' => true, 'group_id' => $group2026->id]);
        $division = CoreTeamDivision::create(['name' => 'Tech', 'priority' => 1]);

        $alice = $this->createUser('Alice', '  ALICE@infiniteuny.id  ');
        CoreTeamMember::create([
            'core_team_id' => $activeTeam2026->id,
            'user_id' => $alice->id,
            'core_team_division_id' => $division->id,
            'photo' => 'photo.png',
        ]);

        Bus::fake([CreateDocsCollection::class, UpdateDocsCollection::class]);

        (new CreateDocsGroup(
            $group2025,
            [self::EX_CORE_TEAM_DOCS_GROUP_ID],
            [self::CORE_TEAM_DOCS_GROUP_ID],
            syncCollectionAfter: true,
        ))->handle(app(OutlineService::class));

        // Adopted group on page 2 without calling groups.create
        Http::assertNotSent(fn (Request $r) => $r->url() === self::OUTLINE_URL.'/api/groups.create');
        $this->assertSame($adoptedGroupId, $group2025->fresh()->integration->docs_group_id);

        // Blank-email stale user was removed from BOTH shared groups instead of returning early
        Http::assertSent(fn (Request $r) => $r->url() === self::OUTLINE_URL.'/api/groups.remove_user'
            && $r['id'] === self::CORE_TEAM_DOCS_GROUP_ID
            && $r['userId'] === $blankEmailUserId);
        Http::assertSent(fn (Request $r) => $r->url() === self::OUTLINE_URL.'/api/groups.remove_user'
            && $r['id'] === self::EX_CORE_TEAM_DOCS_GROUP_ID
            && $r['userId'] === $blankEmailUserId);

        // Whitespace-padded local user matched via LOWER(TRIM(email_address)) and kept in Core Team (not Ex Core Team)
        Http::assertSent(fn (Request $r) => $r->url() === self::OUTLINE_URL.'/api/groups.add_user'
            && $r['id'] === self::CORE_TEAM_DOCS_GROUP_ID
            && $r['userId'] === $paddedEmailUserId);
        Http::assertSent(fn (Request $r) => $r->url() === self::OUTLINE_URL.'/api/groups.remove_user'
            && $r['id'] === self::EX_CORE_TEAM_DOCS_GROUP_ID
            && $r['userId'] === $paddedEmailUserId);

        // Initial collection sync triggered automatically once CreateDocsGroup committed docs_group_id
        Bus::assertDispatched(
            CreateDocsCollection::class,
            fn (CreateDocsCollection $job) => $job->group->is($group2025)
                && $job->collectionName === 'Core Team 2025',
        );
    }

    public function test_sync_core_team_docs_collections_dispatches_update_when_collection_id_exists_and_watermark_was_reset(): void
    {
        Bus::fake([CreateDocsCollection::class, UpdateDocsCollection::class]);

        $group = Group::create(['name' => 'Core Team 2025', 'is_managed' => true]);
        CoreTeam::create(['year' => 2025, 'is_active' => true, 'group_id' => $group->id]);

        // Simulate state after UpdateDocsGroup 404 group recreation: docs_collection_id exists, watermark is null
        GroupIntegration::create([
            'group_id' => $group->id,
            'docs_group_id' => 'recreated-docs-group-id',
            'docs_collection_id' => 'existing-docs-collection-id',
            'docs_group_last_synced_at' => now(),
            'docs_collection_last_synced_at' => null,
        ]);

        (new SyncCoreTeamDocsCollections)->handle();

        Bus::assertNotDispatched(CreateDocsCollection::class);
        Bus::assertDispatched(
            UpdateDocsCollection::class,
            fn (UpdateDocsCollection $job) => $job->group->is($group)
                && $job->collectionName === 'Core Team 2025',
        );
    }

    public function test_authentik_user_dto_and_create_update_sso_user_jobs_handle_prelinked_id_404_quiet_saves_and_group_touch(): void
    {
        // DTO deserializes integer pk + null email/name and omits pk from fromModel()->toArray()
        $parsedDto = AuthentikUserData::from([
            'pk' => 42,
            'username' => 'service_bot',
            'name' => null,
            'email' => null,
            'is_active' => true,
        ]);
        $this->assertSame(42, $parsedDto->sso_id);
        $this->assertNull($parsedDto->email_address);

        $alice = $this->createUser('Alice Updated', 'alice@infiniteuny.id');
        $fromModelArray = AuthentikUserData::fromModel($alice)->toArray();
        $this->assertArrayNotHasKey('pk', $fromModelArray);
        $this->assertSame('Alice Updated', $fromModelArray['name']);

        // Attach Alice to a group with an older updated_at so we verify group touch on initial sso_id assignment
        $pastTime = Carbon::now()->subHours(2)->startOfSecond();
        $memberGroup = Group::create(['name' => 'Member', 'is_managed' => true]);
        $memberGroup->users()->attach($alice->id);
        Group::whereKey($memberGroup->id)->update(['updated_at' => $pastTime]);
        User::whereKey($alice->id)->update(['updated_at' => $pastTime]);
        $alice->refresh();

        // Pre-link a stale sso_id ('999') that 404s, while listUsers returns a non-matching email + exact match ('42')
        User::withoutTimestamps(function () use ($alice) {
            $alice->sso_id = '999';
            $alice->saveQuietly();
        });

        Http::fake([
            self::AUTHENTIK_URL.'/api/v3/core/users/999/' => Http::response(['detail' => 'Not found.'], 404),
            self::AUTHENTIK_URL.'/api/v3/core/users/*' => function (Request $request) use ($alice) {
                if ($request->method() === 'GET') {
                    return Http::response([
                        'results' => [
                            [
                                'pk' => 10,
                                'username' => 'other_alice',
                                'name' => 'Other Alice',
                                'email' => 'prefix_alice@infiniteuny.id',
                                'is_active' => false,
                            ],
                            [
                                'pk' => 42,
                                'username' => 'stale_handle',
                                'name' => 'Stale Name',
                                'email' => 'ALICE@infiniteuny.id',
                                'is_active' => false,
                            ],
                        ],
                    ]);
                }

                if ($request->method() === 'PATCH' && str_ends_with($request->url(), '/users/42/')) {
                    $this->assertArrayNotHasKey('pk', $request->data());
                    $this->assertSame('Alice Updated', $request['name']);
                    $this->assertSame($alice->username, $request['username']);
                    $this->assertTrue($request['is_active']);
                    $this->assertSame('infiniteuny.id', $request['path']);
                    $this->assertSame('internal', $request['type']);

                    return Http::response([
                        'pk' => 42,
                        'username' => $alice->username,
                        'name' => 'Alice Updated',
                        'email' => 'alice@infiniteuny.id',
                        'is_active' => true,
                    ]);
                }

                return Http::response([], 500);
            },
        ]);

        (new CreateSsoUser($alice))->handle(app(AuthentikService::class));

        $alice->refresh();
        $memberGroup->refresh();

        $this->assertSame('42', $alice->sso_id);
        $this->assertInstanceOf(CarbonInterface::class, $alice->sso_last_synced_at);
        // Quiet watermark save did NOT bump users.updated_at!
        $this->assertTrue($alice->updated_at->equalTo($pastTime));
        // Because sso_id changed ('999' -> '42'), Alice's group WAS touched so Authentik group sync includes pk 42!
        $this->assertTrue($memberGroup->updated_at->gt($pastTime));

        // Subsequent UpdateSsoUser when sso_id stays '42' neither bumps users.updated_at nor touches groups.updated_at
        Group::whereKey($memberGroup->id)->update(['updated_at' => $pastTime]);
        (new UpdateSsoUser($alice))->handle(app(AuthentikService::class));
        $this->assertTrue($alice->fresh()->updated_at->equalTo($pastTime));
        $this->assertTrue($memberGroup->fresh()->updated_at->equalTo($pastTime));

        // UpdateSsoUser with null sso_id delegates cleanly to CreateSsoUser
        Bus::fake([CreateSsoUser::class]);
        $unsyncedUser = $this->createUser('Unsynced', 'unsynced@infiniteuny.id');
        (new UpdateSsoUser($unsyncedUser))->handle(app(AuthentikService::class));
        Bus::assertDispatched(CreateSsoUser::class, fn (CreateSsoUser $job) => $job->user->is($unsyncedUser));
    }

    public function test_authentik_group_sync_handles_mass_cohort_deactivation_unset_parents_404_recovery_and_rebound_sso_ids(): void
    {
        // 1. Unset parent env vars pass null (never [null] / "parents": []) and mass cohort deactivation triggers SyncX*
        config([
            'services.authentik.core_team_group_id' => null,
            'services.authentik.xcore_team_group_id' => '',
            'services.authentik.cg_admin_group_id' => null,
            'services.authentik.xcg_admin_group_id' => 'xcg-parent-uuid',
            'services.authentik.member_group_id' => null,
            'services.authentik.admin_group_id' => 'new-admin-sso-uuid',
        ]);

        $syncedAt = Carbon::now()->subMinute()->startOfSecond();
        $olderGroupTime = Carbon::now()->subHours(2)->startOfSecond();

        $ctGroup2024 = Group::create(['name' => 'Core Team 2024', 'is_managed' => true]);
        Group::withoutTimestamps(function () use ($ctGroup2024, $syncedAt) {
            $ctGroup2024->sso_id = 'sso-ct-2024';
            $ctGroup2024->sso_last_synced_at = $syncedAt;
            $ctGroup2024->saveQuietly();
        });
        $coreTeam2024 = CoreTeam::create(['year' => 2024, 'is_active' => true, 'group_id' => $ctGroup2024->id]);
        Group::whereKey($ctGroup2024->id)->update(['updated_at' => $olderGroupTime]);
        // Simulate query-builder mass deactivation (updates core_teams.updated_at without touching groups.updated_at)
        CoreTeam::whereKey($coreTeam2024->id)->update([
            'is_active' => false,
            'updated_at' => Carbon::now(),
        ]);

        $cgaGroup2024 = Group::create(['name' => 'CG Admin 2024', 'is_managed' => true]);
        Group::withoutTimestamps(function () use ($cgaGroup2024, $syncedAt) {
            $cgaGroup2024->sso_id = 'sso-cga-2024';
            $cgaGroup2024->sso_last_synced_at = $syncedAt;
            $cgaGroup2024->saveQuietly();
        });
        $cgAdmin2024 = CommunityGroupAdmin::create(['year' => 2024, 'is_active' => true, 'group_id' => $cgaGroup2024->id]);
        Group::whereKey($cgaGroup2024->id)->update(['updated_at' => $olderGroupTime]);
        CommunityGroupAdmin::whereKey($cgAdmin2024->id)->update([
            'is_active' => false,
            'updated_at' => Carbon::now(),
        ]);

        $activeMemberGroup = Group::create(['name' => 'Active Member', 'is_managed' => true]);

        $adminGroup = Group::create(['name' => 'Administrator', 'is_managed' => true]);
        Group::withoutTimestamps(function () use ($adminGroup, $syncedAt) {
            $adminGroup->sso_id = 'old-admin-sso-uuid';
            $adminGroup->sso_last_synced_at = $syncedAt;
            $adminGroup->saveQuietly();
        });
        Group::whereKey($adminGroup->id)->update(['updated_at' => $olderGroupTime]);

        Bus::fake([CreateSsoGroup::class, UpdateSsoGroup::class]);

        (new SyncXCoreTeamSsoGroups)->handle();
        (new SyncXCGAdminSsoGroups)->handle();
        (new SyncActiveInactiveMemberSsoGroup)->handle();
        (new SyncAdminSsoGroup)->handle();

        Bus::assertDispatched(
            UpdateSsoGroup::class,
            fn (UpdateSsoGroup $job) => $job->group->is($ctGroup2024) && $job->ssoParentIds === null,
        );
        Bus::assertDispatched(
            UpdateSsoGroup::class,
            fn (UpdateSsoGroup $job) => $job->group->is($cgaGroup2024) && $job->ssoParentIds === ['xcg-parent-uuid'],
        );
        Bus::assertDispatched(
            CreateSsoGroup::class,
            fn (CreateSsoGroup $job) => $job->group->is($activeMemberGroup) && $job->ssoParentIds === null,
        );

        // SyncAdminSsoGroup rebound sso_id, reset sso_last_synced_at = null without bumping updated_at, and dispatched CreateSsoGroup
        $adminGroup->refresh();
        $this->assertSame('new-admin-sso-uuid', $adminGroup->sso_id);
        $this->assertNull($adminGroup->sso_last_synced_at);
        $this->assertTrue($adminGroup->updated_at->equalTo($olderGroupTime));
        Bus::assertDispatched(
            CreateSsoGroup::class,
            fn (CreateSsoGroup $job) => $job->group->is($adminGroup) && $job->isSuperuser === true,
        );

        // 2. UpdateSsoGroup self-heals on 404 without adopting substring matches, omits empty parents, and saves quietly
        Http::fake([
            self::AUTHENTIK_URL.'/api/v3/core/groups/sso-ct-2024/' => Http::response(['detail' => 'Not found.'], 404),
            self::AUTHENTIK_URL.'/api/v3/core/groups/*' => function (Request $request) {
                if ($request->method() === 'GET') {
                    // Substring match only -> must NOT be adopted!
                    return Http::response([
                        'results' => [[
                            'pk' => 'wrong-substring-group',
                            'name' => 'Core Team 2024 Backup',
                            'users' => [],
                        ]],
                    ]);
                }

                if ($request->method() === 'POST') {
                    $this->assertArrayNotHasKey('parents', $request->data());
                    $this->assertSame('Core Team 2024', $request['name']);

                    return Http::response([
                        'pk' => 'recreated-ct-2024-uuid',
                        'name' => 'Core Team 2024',
                        'users' => [],
                    ]);
                }

                return Http::response([], 500);
            },
        ]);

        $ctGroup2024->refresh();
        (new UpdateSsoGroup($ctGroup2024, [null]))->handle(app(AuthentikService::class));

        $ctGroup2024->refresh();
        $this->assertSame('recreated-ct-2024-uuid', $ctGroup2024->sso_id);
        $this->assertInstanceOf(CarbonInterface::class, $ctGroup2024->sso_last_synced_at);
        $this->assertTrue($ctGroup2024->updated_at->equalTo($olderGroupTime));
    }

    public function test_hourly_scheduler_chains_group_sso_and_docs_sync_jobs_in_dependency_order(): void
    {
        Bus::fake();

        $this->artisan('schedule:list')->assertSuccessful();

        $schedule = app(Schedule::class);
        $chainEvent = collect($schedule->events())->first(
            fn ($event) => $event->description === 'sync-groups-and-integrations'
        );

        $this->assertNotNull($chainEvent);
        $this->assertSame('0 * * * *', $chainEvent->expression);

        $chainEvent->run($this->app);

        Bus::assertChained([
            SyncMemberGroup::class,
            SyncActiveInactiveMemberGroup::class,
            SyncCoreTeamMembersGroup::class,
            SyncCGAdminMembersGroup::class,
            SyncSsoUsers::class,
            SyncCoreTeamSsoGroups::class,
            SyncCGAdminSsoGroups::class,
            SyncXCoreTeamSsoGroups::class,
            SyncXCGAdminSsoGroups::class,
            SyncMemberSsoGroup::class,
            SyncAdminSsoGroup::class,
            SyncActiveInactiveMemberSsoGroup::class,
            SyncCoreTeamDocsGroups::class,
            SyncXCoreTeamDocsGroups::class,
            SyncCoreTeamDocsCollections::class,
        ]);
    }

    private function createUser(string $name, string $email): User
    {
        $suffix = (string) random_int(100000, 999999);

        return User::create([
            'name' => $name,
            'username' => 'user_'.$suffix,
            'email_address' => $email,
            'phone_number' => '+62812'.$suffix,
            'student_id' => '24520'.$suffix,
            'major_id' => $this->major->id,
            'links' => (object) [],
            'start_date' => '2024-01-01',
            'end_date' => '2028-12-31',
            'is_member' => true,
            'is_extraordinary' => false,
        ]);
    }
}
