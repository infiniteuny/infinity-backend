<?php

namespace Tests\Unit\Http\Controllers\Api\V1;

use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Group;
use App\Models\Major;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class GroupPermissionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_returns_http_201_created(): void
    {
        Bus::fake();

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
        $admin->givePermissionTo(Permission::create([
            'name' => 'create-group-permission',
            'guard_name' => 'api',
        ]));

        $group = Group::create(['name' => 'Reviewers', 'is_managed' => false]);
        $permission = Permission::create(['name' => 'custom-review']);

        $this->actingAs($admin, 'api_headers')
            ->postJson("/v1/groups/{$group->id}/permissions", [
                'permission_id' => $permission->id,
            ])
            ->assertCreated();
    }
}
