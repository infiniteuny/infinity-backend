<?php

namespace Tests\Unit\Http\Controllers\Api\V1;

use App\Jobs\DeleteBlob;
use App\Models\CoreTeam;
use App\Models\CoreTeamDivision;
use App\Models\CoreTeamMember;
use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Group;
use App\Models\Major;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class CoreTeamDivisionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_destroy_cleans_up_cascaded_core_team_member_blobs_after_commit(): void
    {
        Bus::fake([DeleteBlob::class]);

        $degree = Degree::create(['code' => 'S1', 'name' => 'Sarjana']);
        $faculty = Faculty::create(['code' => 'FT', 'name' => 'Teknik']);
        $major = Major::create([
            'code' => 'PTI',
            'name' => 'Pendidikan Teknik Informatika',
            'degree_id' => $degree->id,
            'faculty_id' => $faculty->id,
        ]);

        $admin = User::create([
            'name' => 'Admin User',
            'username' => 'adminuser',
            'email_address' => 'admin@infiniteuny.id',
            'phone_number' => '+6281111111101',
            'student_id' => '24520241001',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);
        $admin->givePermissionTo(Permission::create(['name' => 'delete-core-team-division', 'guard_name' => 'api']));

        $ctGroup = Group::create(['name' => 'Core Team 2025', 'guard_name' => 'api', 'is_managed' => true]);
        $coreTeam = CoreTeam::create(['year' => 2025, 'is_active' => true, 'group_id' => $ctGroup->id]);
        $division = CoreTeamDivision::create(['name' => 'Product', 'priority' => 2]);
        $divPhoto = '{"disk":"public","path":"ct/photos","name":"div1-ct.png","visibility":"public"}';

        CoreTeamMember::create([
            'user_id' => $admin->id,
            'core_team_id' => $coreTeam->id,
            'core_team_division_id' => $division->id,
            'photo' => $divPhoto,
            'animation' => null,
        ]);

        $this->actingAs($admin, 'api_headers')
            ->deleteJson("/v1/core-team-divisions/{$division->id}")
            ->assertOk();

        Bus::assertDispatchedTimes(DeleteBlob::class, 1);
        Bus::assertDispatched(DeleteBlob::class, fn (DeleteBlob $job) => $job->manifest === $divPhoto);
    }
}
