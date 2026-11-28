<?php

namespace Tests\Unit\Jobs;

use App\Jobs\CreateDocsCollection;
use App\Jobs\CreateDocsGroup;
use App\Models\CoreTeam;
use App\Models\CoreTeamDivision;
use App\Models\CoreTeamMember;
use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Group;
use App\Models\Major;
use App\Models\User;
use App\Services\OutlineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CreateDocsGroupTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = 'https://docs.infiniteuny.id';

    private const CORE_TEAM_GROUP_ID = '11111111-1111-1111-1111-111111111111';

    private const EX_CORE_TEAM_GROUP_ID = '22222222-2222-2222-2222-222222222222';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.outline.base_url' => self::BASE_URL,
            'services.outline.token' => 'ol_test_token',
            'services.outline.core_team_group_id' => self::CORE_TEAM_GROUP_ID,
            'services.outline.xcore_team_group_id' => self::EX_CORE_TEAM_GROUP_ID,
        ]);
    }

    public function test_paginates_group_query_invites_missing_users_reconciles_blank_or_padded_emails_and_triggers_collection_sync(): void
    {
        $adoptedGroupId = '33333333-3333-3333-3333-333333333333';
        $bobOutlineId = '66666666-6666-6666-6666-666666666666';
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
            self::BASE_URL.'/api/groups.list' => function (Request $request) use ($pageOneGroups, $adoptedGroupId) {
                $this->assertSame('Core Team 2025', $request['query']);
                $this->assertArrayNotHasKey('name', $request->data());

                if (((int) ($request['offset'] ?? 0)) === 0) {
                    return Http::response(['data' => ['groups' => $pageOneGroups]]);
                }

                return Http::response([
                    'data' => ['groups' => [['id' => $adoptedGroupId, 'name' => 'Core Team 2025']]],
                ]);
            },
            self::BASE_URL.'/api/users.list' => Http::response(['data' => []]),
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
            self::BASE_URL.'/api/groups.add_user' => Http::response(['data' => ['groups' => []]]),
            self::BASE_URL.'/api/groups.remove_user' => Http::response(['data' => ['groups' => []]]),
            self::BASE_URL.'/api/groups.memberships' => function (Request $request) use (
                $adoptedGroupId,
                $bobOutlineId,
                $blankEmailUserId,
                $paddedEmailUserId,
            ) {
                $users = match ($request['id']) {
                    $adoptedGroupId => [
                        ['id' => $bobOutlineId, 'name' => 'Bob'],
                        ['id' => $blankEmailUserId, 'name' => 'Suspended Ghost'],
                        ['id' => $paddedEmailUserId, 'name' => 'Alice Active'],
                    ],
                    self::CORE_TEAM_GROUP_ID => [
                        ['id' => $blankEmailUserId, 'name' => 'Suspended Ghost'],
                    ],
                    default => [],
                };

                return Http::response(['data' => ['users' => $users]]);
            },
            self::BASE_URL.'/api/users.info' => function (Request $request) use ($blankEmailUserId) {
                if ($request['id'] === $blankEmailUserId) {
                    return Http::response(['data' => ['id' => $blankEmailUserId, 'name' => 'Ghost', 'email' => '']]);
                }

                return Http::response([
                    'data' => ['id' => $request['id'], 'name' => 'Alice Active', 'email' => 'alice@infiniteuny.id'],
                ]);
            },
        ]);

        $degree = Degree::create(['code' => 'S1', 'name' => 'Sarjana']);
        $faculty = Faculty::create(['code' => 'FT', 'name' => 'Teknik']);
        $major = Major::create([
            'code' => 'PTI',
            'name' => 'Pendidikan Teknik Informatika',
            'degree_id' => $degree->id,
            'faculty_id' => $faculty->id,
        ]);
        $division = CoreTeamDivision::create(['name' => 'Engineering', 'priority' => 1]);

        $group2025 = Group::create(['name' => 'Core Team 2025', 'is_managed' => true]);
        $team2025 = CoreTeam::create(['year' => 2025, 'is_active' => false, 'group_id' => $group2025->id]);

        $group2026 = Group::create(['name' => 'Core Team 2026', 'is_managed' => true]);
        $team2026 = CoreTeam::create(['year' => 2026, 'is_active' => true, 'group_id' => $group2026->id]);

        $alice = User::create([
            'name' => 'Alice',
            'username' => 'alice',
            'email_address' => '  ALICE@infiniteuny.id  ',
            'phone_number' => '+6281111111101',
            'student_id' => '24520241001',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);
        $bob = User::create([
            'name' => 'Bob',
            'username' => 'bob',
            'email_address' => 'bob@infiniteuny.id',
            'phone_number' => '+6281111111102',
            'student_id' => '24520241002',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);

        CoreTeamMember::create([
            'core_team_id' => $team2026->id,
            'user_id' => $alice->id,
            'core_team_division_id' => $division->id,
            'photo' => 'photo.png',
        ]);
        CoreTeamMember::create([
            'core_team_id' => $team2025->id,
            'user_id' => $bob->id,
            'core_team_division_id' => $division->id,
            'photo' => 'photo.png',
        ]);
        $group2025->users()->sync([$bob->id]);

        Bus::fake([CreateDocsCollection::class]);

        (new CreateDocsGroup(
            $group2025,
            [self::EX_CORE_TEAM_GROUP_ID],
            [self::CORE_TEAM_GROUP_ID],
            suppressInviteEmail: true,
            syncCollectionAfter: true,
        ))->handle(app(OutlineService::class));

        $this->assertSame($adoptedGroupId, $group2025->fresh()->integration->docs_group_id);
        Http::assertNotSent(fn (Request $r) => $r->url() === self::BASE_URL.'/api/groups.create');

        Http::assertSent(fn (Request $r) => $r->url() === self::BASE_URL.'/api/groups.remove_user'
            && $r['id'] === self::CORE_TEAM_GROUP_ID
            && $r['userId'] === $blankEmailUserId);

        Http::assertSent(fn (Request $r) => $r->url() === self::BASE_URL.'/api/groups.add_user'
            && $r['id'] === self::CORE_TEAM_GROUP_ID
            && $r['userId'] === $paddedEmailUserId);

        // Diffing: Bob was already in $adoptedGroupId so add_user is NOT sent redundantly for the cohort group
        Http::assertNotSent(fn (Request $r) => $r->url() === self::BASE_URL.'/api/groups.add_user'
            && $r['id'] === $adoptedGroupId
            && $r['userId'] === $bobOutlineId);

        Bus::assertDispatched(
            CreateDocsCollection::class,
            fn (CreateDocsCollection $job) => $job->group->is($group2025)
                && $job->collectionName === 'Core Team 2025',
        );
    }

    public function test_exits_early_when_group_deleted_post_lock_or_outline_unconfigured(): void
    {
        Http::fake();

        $deletedGroup = Group::create(['name' => 'Deleted Group', 'is_managed' => true]);
        $job = new CreateDocsGroup($deletedGroup);
        $deletedGroup->delete();

        $job->handle(app(OutlineService::class));
        Http::assertNothingSent();

        $unconfiguredGroup = Group::create(['name' => 'Unconfigured Group', 'is_managed' => true]);
        config(['services.outline.token' => '   ']);
        (new CreateDocsGroup($unconfiguredGroup))->handle(app(OutlineService::class));
        Http::assertNothingSent();
        $this->assertNull($unconfiguredGroup->fresh()->integration);
    }

    public function test_ignores_404_on_remove_user_from_cohort_and_shared_groups(): void
    {
        $docsGroupId = '33333333-3333-3333-3333-333333333333';
        $bobOutlineId = '66666666-6666-6666-6666-666666666666';
        $staleOutlineId = '77777777-7777-7777-7777-777777777777';

        $degree = Degree::create(['code' => 'S1', 'name' => 'Sarjana']);
        $faculty = Faculty::create(['code' => 'FT', 'name' => 'Teknik']);
        $major = Major::create([
            'code' => 'PTI',
            'name' => 'Pendidikan Teknik Informatika',
            'degree_id' => $degree->id,
            'faculty_id' => $faculty->id,
        ]);
        $bob = User::create([
            'name' => 'Bob',
            'username' => 'bob404',
            'email_address' => 'bob404@infiniteuny.id',
            'phone_number' => '+6281111111199',
            'student_id' => '24520241099',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);
        $group = Group::create(['name' => 'Core Team 404', 'is_managed' => true]);
        $group->users()->sync([$bob->id]);

        Http::fake([
            self::BASE_URL.'/api/groups.list' => Http::response([
                'data' => ['groups' => [['id' => $docsGroupId, 'name' => 'Core Team 404']]],
            ]),
            self::BASE_URL.'/api/users.list' => Http::response([
                'data' => [['id' => $bobOutlineId, 'name' => 'Bob', 'email' => 'bob404@infiniteuny.id']],
            ]),
            self::BASE_URL.'/api/groups.add_user' => Http::response(['data' => ['groups' => []]]),
            self::BASE_URL.'/api/groups.memberships' => Http::response([
                'data' => [
                    'users' => [
                        ['id' => $bobOutlineId, 'name' => 'Bob', 'email' => 'bob404@infiniteuny.id'],
                        ['id' => $staleOutlineId, 'name' => 'Stale', 'email' => 'nobody@infiniteuny.id'],
                    ],
                ],
            ]),
            self::BASE_URL.'/api/groups.remove_user' => Http::response(['ok' => false, 'error' => 'not_found'], 404),
        ]);

        (new CreateDocsGroup($group, [self::EX_CORE_TEAM_GROUP_ID], [self::CORE_TEAM_GROUP_ID]))
            ->handle(app(OutlineService::class));

        $this->assertSame($docsGroupId, $group->fresh()->integration?->docs_group_id);
    }
}
