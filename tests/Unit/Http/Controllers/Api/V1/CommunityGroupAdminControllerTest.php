<?php

namespace Tests\Unit\Http\Controllers\Api\V1;

use App\Jobs\DeleteBlob;
use App\Jobs\DeleteDocsGroup;
use App\Jobs\DeleteSsoGroup;
use App\Models\CommunityGroup;
use App\Models\CommunityGroupAdmin;
use App\Models\CommunityGroupAdminMember;
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

class CommunityGroupAdminControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_destroy_dispatches_sso_docs_and_deduplicated_member_blob_cleanup(): void
    {
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
        $admin->givePermissionTo(Permission::create(['name' => 'delete-community-group-admin', 'guard_name' => 'api']));

        $alice = User::create([
            'name' => 'Alice',
            'username' => 'alicecg',
            'email_address' => 'alice@infiniteuny.id',
            'phone_number' => '+6281111111104',
            'student_id' => '24520241004',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);

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

        $this->actingAs($admin, 'api_headers')
            ->deleteJson("/v1/community-group-admins/{$cgAdmin->id}")
            ->assertOk();

        $this->assertDatabaseMissing('community_group_admins', ['id' => $cgAdmin->id]);
        $this->assertDatabaseMissing('groups', ['id' => $group->id]);
        $this->assertDatabaseMissing('group_integrations', ['group_id' => $group->id]);
        $this->assertDatabaseCount('community_group_admin_members', 0);

        Bus::assertDispatched(
            DeleteSsoGroup::class,
            fn (DeleteSsoGroup $job) => $job->groupSsoId === 'sso-cga-2024' && $job->groupId === $group->id,
        );
        Bus::assertDispatched(
            DeleteDocsGroup::class,
            fn (DeleteDocsGroup $job) => $job->docsGroupId === 'docs-cga-2024'
                && $job->groupId === $group->id
                && $job->sharedGroupIds === null
                && $job->fallbackMemberEmails === []
        );

        Bus::assertDispatchedTimes(DeleteBlob::class, 3);
        Bus::assertDispatched(DeleteBlob::class, fn (DeleteBlob $job) => $job->manifest === $photo1);
        Bus::assertDispatched(DeleteBlob::class, fn (DeleteBlob $job) => $job->manifest === $photo2);
        Bus::assertDispatched(DeleteBlob::class, fn (DeleteBlob $job) => $job->manifest === $anim2);
    }
}
