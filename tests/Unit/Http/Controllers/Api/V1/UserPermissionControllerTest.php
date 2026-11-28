<?php

namespace Tests\Unit\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\UserPermissionController;
use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Group;
use App\Models\Major;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use ReflectionMethod;
use Tests\TestCase;

class UserPermissionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_returns_201_and_index_show_docblocks_do_not_declare_unauthenticated(): void
    {
        Bus::fake();

        $indexDoc = (string) (new ReflectionMethod(UserPermissionController::class, 'index'))->getDocComment();
        $showDoc = (string) (new ReflectionMethod(UserPermissionController::class, 'show'))->getDocComment();

        $this->assertStringNotContainsString('@unauthenticated', $indexDoc);
        $this->assertStringNotContainsString('@unauthenticated', $showDoc);

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
            'name' => 'create-user-permission',
            'guard_name' => 'api',
        ]));

        $targetUser = User::create([
            'name' => 'Member',
            'username' => 'memberuser',
            'email_address' => 'member@infiniteuny.id',
            'phone_number' => '+6281111111102',
            'student_id' => '24520241002',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);
        $permission = Permission::create(['name' => 'custom-review']);

        $this->actingAs($admin, 'api_headers')
            ->postJson("/v1/users/{$targetUser->id}/permissions", [
                'permission_id' => $permission->id,
            ])
            ->assertCreated();

        $admin->givePermissionTo(Permission::create([
            'name' => 'read-user-permission',
            'guard_name' => 'api',
        ]));

        $inheritedPerm = Permission::create(['name' => 'alpha-inherited', 'guard_name' => 'api']);
        $group = Group::create(['name' => 'Reviewers', 'guard_name' => 'api']);
        $secondGroup = Group::create(['name' => 'Senior Reviewers', 'guard_name' => 'api']);
        $group->givePermissionTo([$inheritedPerm, $permission]);
        $secondGroup->givePermissionTo($inheritedPerm);
        $targetUser->assignRole([$group, $secondGroup]);

        // Warm route/auth permission cache once before measuring index() query count
        $this->actingAs($admin, 'api_headers')
            ->getJson("/v1/users/{$targetUser->id}/permissions")
            ->assertOk();

        DB::flushQueryLog();
        DB::enableQueryLog();

        $nestedResponse = $this->actingAs($admin, 'api_headers')
            ->getJson("/v1/users/{$targetUser->id}/permissions?includes[]=nested&sorts=name")
            ->assertOk();

        // Route model binding (User) + 1 single SQL query for nested permissions & entitlements
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();
        $this->assertLessThanOrEqual(2, $queryCount);

        $this->assertSame(
            ['alpha-inherited', 'custom-review'],
            array_column($nestedResponse->json('data.user_permissions'), 'name'),
        );
        $this->assertContains(
            $nestedResponse->json('data.user_permissions.0.entitlement.group_id'),
            [$group->id, $secondGroup->id],
        );
        $this->assertSame($inheritedPerm->id, $nestedResponse->json('data.user_permissions.0.entitlement.permission_id'));
        $this->assertNotEmpty($nestedResponse->json('data.user_permissions.0.entitlement.id'));
        $this->assertNull($nestedResponse->json('data.user_permissions.0.entitlement.user_id'));

        // Overlapping direct + group permission prefers direct UserPermission entitlement without duplicating row
        $this->assertSame($targetUser->id, $nestedResponse->json('data.user_permissions.1.entitlement.user_id'));
        $this->assertNull($nestedResponse->json('data.user_permissions.1.entitlement.group_id'));
        $this->assertSame($permission->id, $nestedResponse->json('data.user_permissions.1.entitlement.permission_id'));
        $this->assertNotEmpty($nestedResponse->json('data.user_permissions.1.entitlement.id'));

        $this->actingAs($admin, 'api_headers')
            ->getJson("/v1/users/{$targetUser->id}/permissions?includes=nested&filters[name]=alpha-inherited&fields=name")
            ->assertOk()
            ->assertJsonCount(1, 'data.user_permissions')
            ->assertJsonPath('data.user_permissions.0.name', 'alpha-inherited')
            ->assertJsonPath('data.user_permissions.0.entitlement.permission_id', $inheritedPerm->id)
            ->assertJsonMissingPath('data.user_permissions.0.id')
            ->assertJsonMissingPath('data.user_permissions.0._pivot_permission_id');

        $this->actingAs($admin, 'api_headers')
            ->getJson("/v1/users/{$targetUser->id}/permissions?includes=nested,invalid")
            ->assertStatus(400);

        $this->actingAs($admin, 'api_headers')
            ->getJson("/v1/users/{$targetUser->id}/permissions?includes=nested&fields=invalid")
            ->assertStatus(400);

        $this->actingAs($admin, 'api_headers')
            ->getJson("/v1/users/{$targetUser->id}/permissions?includes=nested&filters[invalid]=1")
            ->assertStatus(400);

        $this->actingAs($admin, 'api_headers')
            ->getJson("/v1/users/{$targetUser->id}/permissions?includes=nested&sorts=invalid")
            ->assertStatus(400);
    }
}
