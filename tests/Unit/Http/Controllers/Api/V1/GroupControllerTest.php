<?php

namespace Tests\Unit\Http\Controllers\Api\V1;

use App\Jobs\DeleteDocsGroup;
use App\Jobs\DeleteSsoGroup;
use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Group;
use App\Models\GroupIntegration;
use App\Models\Major;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class GroupControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_destroy_dispatches_sso_and_docs_group_cleanup_without_shared_groups(): void
    {
        $degree = Degree::create(['code' => 'S1', 'name' => 'Sarjana']);
        $faculty = Faculty::create(['code' => 'FT', 'name' => 'Teknik']);
        $major = Major::create([
            'code' => 'PTI',
            'name' => 'Pendidikan Teknik Informatika',
            'degree_id' => $degree->id,
            'faculty_id' => $faculty->id,
        ]);

        $admin = User::create([
            'name' => 'Admin',
            'username' => 'adminuser',
            'email_address' => 'admin@infiniteuny.id',
            'phone_number' => '+6281111111101',
            'student_id' => '24520241001',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);
        $admin->givePermissionTo(Permission::create(['name' => 'delete-group', 'guard_name' => 'api']));

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

        $this->actingAs($admin, 'api_headers')
            ->deleteJson("/v1/groups/{$group->id}")
            ->assertOk();

        Bus::assertDispatched(
            DeleteSsoGroup::class,
            fn (DeleteSsoGroup $job) => $job->groupSsoId === 'sso-custom-wg' && $job->groupId === $group->id,
        );
        Bus::assertDispatched(
            DeleteDocsGroup::class,
            fn (DeleteDocsGroup $job) => $job->docsGroupId === 'docs-custom-wg'
                && $job->groupId === $group->id
                && $job->sharedGroupIds === null,
        );
    }
}
