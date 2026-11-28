<?php

namespace Tests\Unit\Jobs;

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
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UpdateDocsGroupTest extends TestCase
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

    public function test_recovers_from_404_preserves_multi_year_active_members_and_removes_paginated_stale_members(): void
    {
        $missingGroupId = 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa';
        $recreatedGroupId = 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb';
        $charlieOutlineId = 'dddddddd-dddd-dddd-dddd-dddddddddddd';
        $dianaOutlineId = 'eeeeeeee-eeee-eeee-eeee-eeeeeeeeeeee';

        $statefulMembers = [
            ['id' => $charlieOutlineId, 'name' => 'Charlie'],
            ['id' => $dianaOutlineId, 'name' => 'Diana'],
        ];
        for ($i = 1; $i <= 102; $i++) {
            $statefulMembers[] = [
                'id' => sprintf('00000000-0000-0000-0000-%012d', $i),
                'name' => "Stale {$i}",
            ];
        }

        Http::fake([
            self::BASE_URL.'/api/groups.update' => Http::response(['ok' => false, 'error' => 'not_found'], 404),
            self::BASE_URL.'/api/groups.list' => Http::response(['data' => ['groups' => []]]),
            self::BASE_URL.'/api/groups.create' => Http::response([
                'data' => ['id' => $recreatedGroupId, 'name' => 'Core Team 2024'],
            ]),
            self::BASE_URL.'/api/users.list' => Http::response([
                'data' => [
                    ['id' => $charlieOutlineId, 'name' => 'Charlie', 'email' => 'charlie@infiniteuny.id'],
                    ['id' => $dianaOutlineId, 'name' => 'Diana', 'email' => 'diana@infiniteuny.id'],
                ],
            ]),
            self::BASE_URL.'/api/groups.add_user' => Http::response(['data' => ['groups' => []]]),
            self::BASE_URL.'/api/groups.memberships' => function (Request $request) use (&$statefulMembers) {
                $offset = (int) ($request['offset'] ?? 0);
                $limit = (int) ($request['limit'] ?? 100);

                return Http::response([
                    'data' => ['users' => array_slice(array_values($statefulMembers), $offset, $limit)],
                ]);
            },
            self::BASE_URL.'/api/groups.remove_user' => function (Request $request) use (&$statefulMembers, $recreatedGroupId) {
                if ($request['id'] === $recreatedGroupId) {
                    $statefulMembers = array_values(array_filter(
                        $statefulMembers,
                        fn (array $member) => $member['id'] !== $request['userId'],
                    ));
                }

                return Http::response(['data' => ['groups' => []]]);
            },
            self::BASE_URL.'/api/users.info' => function (Request $request) {
                return Http::response([
                    'data' => ['id' => $request['id'], 'name' => 'Stale', 'email' => 'stale@infiniteuny.id'],
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
        $division = CoreTeamDivision::create(['name' => 'Design', 'priority' => 1]);

        $group2024 = Group::create(['name' => 'Core Team 2024']);
        $coreTeam2024 = CoreTeam::create(['year' => 2024, 'is_active' => false, 'group_id' => $group2024->id]);

        $group2025 = Group::create(['name' => 'Core Team 2025']);
        $coreTeam2025 = CoreTeam::create(['year' => 2025, 'is_active' => true, 'group_id' => $group2025->id]);

        GroupIntegration::create([
            'group_id' => $group2024->id,
            'docs_group_id' => $missingGroupId,
            'docs_collection_id' => 'cccccccc-cccc-cccc-cccc-cccccccccccc',
            'docs_group_last_synced_at' => now()->subHour(),
            'docs_collection_last_synced_at' => now()->subHour(),
        ]);

        $charlie = User::create([
            'name' => 'Charlie',
            'username' => 'charlie',
            'email_address' => 'charlie@infiniteuny.id',
            'phone_number' => '+6281111111101',
            'student_id' => '24520241001',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);
        $diana = User::create([
            'name' => 'Diana',
            'username' => 'diana',
            'email_address' => 'diana@infiniteuny.id',
            'phone_number' => '+6281111111102',
            'student_id' => '24520241002',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);

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
        $this->assertCount(2, $statefulMembers);

        Http::assertNotSent(fn (Request $r) => $r->url() === self::BASE_URL.'/api/groups.remove_user'
            && $r['id'] === self::CORE_TEAM_GROUP_ID
            && $r['userId'] === $dianaOutlineId);
    }

    public function test_steady_state_diffing_skips_redundant_writes_and_post_lock_deleted_group_exits_early(): void
    {
        $docsGroupId = 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa';
        $aliceOutlineId = '12121212-1212-1212-1212-121212121212';

        $degree = Degree::create(['code' => 'S1', 'name' => 'Sarjana']);
        $faculty = Faculty::create(['code' => 'FT', 'name' => 'Teknik']);
        $major = Major::create([
            'code' => 'PTI',
            'name' => 'Pendidikan Teknik Informatika',
            'degree_id' => $degree->id,
            'faculty_id' => $faculty->id,
        ]);
        $division = CoreTeamDivision::create(['name' => 'Engineering', 'priority' => 1]);

        $group2026 = Group::create(['name' => 'Core Team 2026']);
        $coreTeam2026 = CoreTeam::create(['year' => 2026, 'is_active' => true, 'group_id' => $group2026->id]);
        GroupIntegration::create([
            'group_id' => $group2026->id,
            'docs_group_id' => $docsGroupId,
            'docs_collection_id' => 'cccccccc-cccc-cccc-cccc-cccccccccccc',
            'docs_group_last_synced_at' => now()->subHour(),
            'docs_collection_last_synced_at' => now()->subHour(),
        ]);

        $alice = User::create([
            'name' => 'Alice',
            'username' => 'alice2026',
            'email_address' => 'alice2026@infiniteuny.id',
            'phone_number' => '+6281111111226',
            'student_id' => '24520241226',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);
        CoreTeamMember::create([
            'core_team_id' => $coreTeam2026->id,
            'user_id' => $alice->id,
            'core_team_division_id' => $division->id,
            'photo' => 'photo.png',
        ]);
        $group2026->users()->sync([$alice->id]);

        Http::fake([
            self::BASE_URL.'/api/groups.update' => Http::response([
                'data' => ['id' => $docsGroupId, 'name' => 'Core Team 2026'],
            ]),
            self::BASE_URL.'/api/users.list' => Http::response([
                'data' => [['id' => $aliceOutlineId, 'name' => 'Alice', 'email' => 'alice2026@infiniteuny.id']],
            ]),
            self::BASE_URL.'/api/groups.memberships' => function (Request $request) use ($docsGroupId, $aliceOutlineId) {
                $users = match ($request['id']) {
                    $docsGroupId, self::CORE_TEAM_GROUP_ID => [
                        ['id' => $aliceOutlineId, 'name' => 'Alice', 'email' => 'alice2026@infiniteuny.id'],
                    ],
                    default => [],
                };

                return Http::response(['data' => ['users' => $users]]);
            },
        ]);

        (new UpdateDocsGroup($group2026, [self::CORE_TEAM_GROUP_ID], [self::EX_CORE_TEAM_GROUP_ID]))
            ->handle(app(OutlineService::class));

        Http::assertNotSent(fn (Request $r) => $r->url() === self::BASE_URL.'/api/groups.add_user');
        Http::assertNotSent(fn (Request $r) => $r->url() === self::BASE_URL.'/api/groups.remove_user');

        // Post-lock deleted group exits cleanly without HTTP writes
        $queuedJob = new UpdateDocsGroup($group2026);
        CoreTeamMember::where('core_team_id', $coreTeam2026->id)->delete();
        $coreTeam2026->delete();
        GroupIntegration::where('group_id', $group2026->id)->delete();
        $group2026->users()->detach();
        $group2026->delete();

        Http::fake();
        $queuedJob->handle(app(OutlineService::class));
        Http::assertNothingSent();
    }
}
