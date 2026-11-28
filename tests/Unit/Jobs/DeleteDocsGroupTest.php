<?php

namespace Tests\Unit\Jobs;

use App\Jobs\DeleteDocsGroup;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DeleteDocsGroupTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = 'https://docs.infiniteuny.id';

    private const CORE_TEAM_GROUP_ID = '11111111-1111-1111-1111-111111111111';

    private const EX_CORE_TEAM_GROUP_ID = '22222222-2222-2222-2222-222222222222';

    private Major $major;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.outline.base_url' => self::BASE_URL,
            'services.outline.token' => 'ol_test_token',
            'services.outline.core_team_group_id' => self::CORE_TEAM_GROUP_ID,
            'services.outline.xcore_team_group_id' => self::EX_CORE_TEAM_GROUP_ID,
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

    public function test_batches_db_queries_diffs_shared_group_writes_handles_404_and_never_deletes_collection(): void
    {
        $docsGroupId = '77777777-7777-7777-7777-777777777777';
        $soleOutlineId = 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa';
        $activeMultiOutlineId = 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb';
        $inactiveMultiOutlineId = 'cccccccc-cccc-cccc-cccc-cccccccccccc';

        $this->makeUser('Sole', 'sole@infiniteuny.id');
        $activeMulti = $this->makeUser('Active', 'active@infiniteuny.id');
        $inactiveMulti = $this->makeUser('Inactive', 'inactive@infiniteuny.id');

        $division = CoreTeamDivision::create(['name' => 'Tech', 'priority' => 1]);
        $group2025 = Group::create(['name' => 'Core Team 2025', 'is_managed' => true]);
        $ct2025 = CoreTeam::create(['year' => 2025, 'is_active' => true, 'group_id' => $group2025->id]);
        CoreTeamMember::create([
            'user_id' => $activeMulti->id,
            'core_team_id' => $ct2025->id,
            'core_team_division_id' => $division->id,
            'photo' => 'active.png',
        ]);

        $group2024 = Group::create(['name' => 'Core Team 2024', 'is_managed' => true]);
        $ct2024 = CoreTeam::create(['year' => 2024, 'is_active' => false, 'group_id' => $group2024->id]);
        CoreTeamMember::create([
            'user_id' => $inactiveMulti->id,
            'core_team_id' => $ct2024->id,
            'core_team_division_id' => $division->id,
            'photo' => 'inactive.png',
        ]);

        $addedToShared = [];
        $removedFromShared = [];
        $deletedCollectionCalled = false;

        Http::fake([
            self::BASE_URL.'/api/groups.memberships' => function (Request $request) use (
                $docsGroupId,
                $soleOutlineId,
                $activeMultiOutlineId,
                $inactiveMultiOutlineId,
            ) {
                $users = match ($request['id']) {
                    $docsGroupId => [
                        ['id' => $soleOutlineId, 'name' => 'Sole', 'email' => 'sole@infiniteuny.id'],
                        ['id' => $activeMultiOutlineId, 'name' => 'Active', 'email' => 'active@infiniteuny.id'],
                        ['id' => $inactiveMultiOutlineId, 'name' => 'Inactive', 'email' => 'inactive@infiniteuny.id'],
                    ],
                    self::CORE_TEAM_GROUP_ID => [
                        ['id' => $activeMultiOutlineId, 'name' => 'Active', 'email' => 'active@infiniteuny.id'],
                        ['id' => $inactiveMultiOutlineId, 'name' => 'Inactive', 'email' => 'inactive@infiniteuny.id'],
                    ],
                    self::EX_CORE_TEAM_GROUP_ID => [
                        ['id' => $soleOutlineId, 'name' => 'Sole', 'email' => 'sole@infiniteuny.id'],
                    ],
                    default => [],
                };

                return Http::response(['data' => ['users' => $users]]);
            },
            self::BASE_URL.'/api/groups.add_user' => function (Request $request) use (&$addedToShared) {
                $addedToShared[] = [$request['id'], $request['userId']];

                return Http::response(['data' => []]);
            },
            self::BASE_URL.'/api/groups.remove_user' => function (Request $request) use (&$removedFromShared) {
                $removedFromShared[] = [$request['id'], $request['userId']];

                return Http::response(['data' => []]);
            },
            self::BASE_URL.'/api/groups.delete' => Http::response(['success' => true]),
            self::BASE_URL.'/api/collections.delete' => function () use (&$deletedCollectionCalled) {
                $deletedCollectionCalled = true;

                return Http::response(['success' => true]);
            },
        ]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        (new DeleteDocsGroup(
            docsGroupId: $docsGroupId,
            groupId: 'deleted-group',
            sharedGroupIds: [self::CORE_TEAM_GROUP_ID, self::EX_CORE_TEAM_GROUP_ID],
            fallbackMemberEmails: ['sole@infiniteuny.id', 'active@infiniteuny.id', 'inactive@infiniteuny.id'],
        ))->handle(app(OutlineService::class));

        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertSame(2, $queryCount);
        $this->assertFalse($deletedCollectionCalled);
        $this->assertSame([[self::EX_CORE_TEAM_GROUP_ID, $inactiveMultiOutlineId]], $addedToShared);
        $this->assertCount(2, $removedFromShared);
    }

    private function makeUser(string $name, string $email): User
    {
        $suffix = (string) random_int(100000, 999999);

        return User::create([
            'name' => $name,
            'username' => 'u_'.$suffix,
            'email_address' => $email,
            'phone_number' => '+62811'.$suffix,
            'student_id' => '24520'.$suffix,
            'major_id' => $this->major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);
    }
}
