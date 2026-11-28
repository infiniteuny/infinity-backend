<?php

namespace Tests\Unit\Http\Controllers\Api\V1;

use App\Jobs\DeleteBlob;
use App\Models\CommunityGroup;
use App\Models\CommunityGroupAdmin;
use App\Models\CommunityGroupAdminMember;
use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Group;
use App\Models\Major;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CommunityGroupControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_and_update_coerce_valid_boolean_strings_and_reject_null_is_active(): void
    {
        Storage::fake('public');
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
        foreach (['create-community-group', 'update-community-group'] as $permName) {
            $admin->givePermissionTo(Permission::create(['name' => $permName, 'guard_name' => 'api']));
        }

        $groupResponse = $this->actingAs($admin, 'api_headers')
            ->post('/v1/community-groups', [
                'name' => 'Web Engineering',
                'priority' => 1,
                'description' => 'Frontend and backend',
                'logo' => UploadedFile::fake()->image('logo.png'),
                'is_active' => 'true',
            ], ['Accept' => 'application/json'])
            ->assertCreated();

        $communityGroupId = $groupResponse->json('data.community_group.id');
        $this->assertTrue((bool) CommunityGroup::findOrFail($communityGroupId)->is_active);

        $this->actingAs($admin, 'api_headers')
            ->patchJson("/v1/community-groups/{$communityGroupId}", [
                'is_active' => null,
            ])
            ->assertStatus(422);

        $this->assertTrue((bool) CommunityGroup::findOrFail($communityGroupId)->is_active);
    }

    public function test_destroy_cleans_up_group_logo_and_cascaded_admin_member_blobs_after_commit(): void
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
        $admin->givePermissionTo(Permission::create(['name' => 'delete-community-group', 'guard_name' => 'api']));

        $cgLogo = '{"disk":"public","path":"cg/logos","name":"uiux.png","visibility":"public"}';
        $cg = CommunityGroup::create([
            'name' => 'UI/UX',
            'priority' => 1,
            'description' => 'Design group',
            'logo' => $cgLogo,
            'is_active' => true,
        ]);

        $cgaGroup = Group::create(['name' => 'CG Admin 2025', 'guard_name' => 'api', 'is_managed' => true]);
        $cgAdmin = CommunityGroupAdmin::create(['year' => 2025, 'is_active' => true, 'group_id' => $cgaGroup->id]);

        $cgaPhoto = '{"disk":"public","path":"cga/photos","name":"m1.png","visibility":"public"}';
        CommunityGroupAdminMember::create([
            'user_id' => $admin->id,
            'community_group_admin_id' => $cgAdmin->id,
            'community_group_id' => $cg->id,
            'photo' => $cgaPhoto,
            'animation' => null,
        ]);

        $this->actingAs($admin, 'api_headers')
            ->deleteJson("/v1/community-groups/{$cg->id}")
            ->assertOk();

        Bus::assertDispatchedTimes(DeleteBlob::class, 2);
        Bus::assertDispatched(DeleteBlob::class, fn (DeleteBlob $job) => $job->manifest === $cgLogo);
        Bus::assertDispatched(DeleteBlob::class, fn (DeleteBlob $job) => $job->manifest === $cgaPhoto);
    }
}
