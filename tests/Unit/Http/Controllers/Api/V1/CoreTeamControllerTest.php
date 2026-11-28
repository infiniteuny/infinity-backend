<?php

namespace Tests\Unit\Http\Controllers\Api\V1;

use App\Jobs\DeleteBlob;
use App\Jobs\DeleteDocsGroup;
use App\Jobs\DeleteSsoGroup;
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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CoreTeamControllerTest extends TestCase
{
    use RefreshDatabase;

    private const CORE_TEAM_GROUP_ID = '11111111-1111-1111-1111-111111111111';

    private const EX_CORE_TEAM_GROUP_ID = '22222222-2222-2222-2222-222222222222';

    public function test_destroy_dispatches_sso_docs_and_member_blob_cleanup_without_touching_deleted_group(): void
    {
        config([
            'services.outline.core_team_group_id' => self::CORE_TEAM_GROUP_ID,
            'services.outline.xcore_team_group_id' => self::EX_CORE_TEAM_GROUP_ID,
        ]);

        Bus::fake([
            DeleteSsoGroup::class,
            DeleteDocsGroup::class,
            DeleteBlob::class,
        ]);

        $degree = Degree::create(['code' => 'S1', 'name' => 'Sarjana']);
        $faculty = Faculty::create(['code' => 'FT', 'name' => 'Teknik']);
        $major = Major::create([
            'code' => 'PTI',
            'name' => 'Pendidikan Teknik Informatika',
            'degree_id' => $degree->id,
            'faculty_id' => $faculty->id,
        ]);

        $admin = $this->createUser($major, 'Admin', 'adminuser', 'admin@infiniteuny.id', '24520241001', '+6281111111101');
        $admin->givePermissionTo(Permission::create(['name' => 'delete-core-team', 'guard_name' => 'api']));

        $alice = $this->createUser($major, 'Alice', 'aliceuser', 'Alice@infiniteuny.id', '24520241002', '+6281111111102');
        $bob = $this->createUser($major, 'Bob', 'bobuser', 'bob@infiniteuny.id', '24520241003', '+6281111111103');

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

        $response = $this->actingAs($admin, 'api_headers')
            ->deleteJson("/v1/core-teams/{$coreTeam->id}");

        $queries = array_column(DB::getQueryLog(), 'query');
        DB::disableQueryLog();

        $response->assertOk();

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

    private function createUser(
        Major $major,
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
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);
    }
}
