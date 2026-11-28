<?php

namespace Tests\Unit\Http\Controllers\Api\V1;

use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Group;
use App\Models\Major;
use App\Models\Permission;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class UserGroupControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_store_returns_201_and_store_update_destroy_touch_affected_groups(): void
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
            'name' => 'Admin Infinity',
            'username' => 'admininfinity',
            'email_address' => 'admin@student.uny.ac.id',
            'phone_number' => '+6281111111111',
            'student_id' => '24520241001',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);
        foreach (['read-user-group', 'create-user-group', 'update-user-group', 'delete-user-group'] as $permName) {
            $admin->givePermissionTo(Permission::create([
                'name' => $permName,
                'guard_name' => 'api',
            ]));
        }

        $member = User::create([
            'name' => 'Member Infinity',
            'username' => 'memberinfinity',
            'email_address' => 'member@student.uny.ac.id',
            'phone_number' => '+6282222222222',
            'student_id' => '24520241002',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);

        Carbon::setTestNow('2026-01-01 00:00:00');
        $groupA = Group::create(['name' => 'Core Team 2025', 'guard_name' => 'api']);
        $groupB = Group::create(['name' => 'Core Team 2026', 'guard_name' => 'api']);

        Carbon::setTestNow('2026-01-01 01:00:00');
        $this->actingAs($admin, 'api_headers')
            ->postJson("/v1/users/{$member->id}/groups", [
                'group_id' => $groupA->id,
            ])
            ->assertCreated();

        $groupA->refresh();
        $groupB->refresh();
        $this->assertSame('2026-01-01 01:00:00', $groupA->updated_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-01-01 00:00:00', $groupB->updated_at->format('Y-m-d H:i:s'));

        $userGroup = UserGroup::where('user_id', $member->id)
            ->where('group_id', $groupA->id)
            ->firstOrFail();

        Carbon::setTestNow('2026-01-01 01:30:00');
        $this->actingAs($admin, 'api_headers')
            ->putJson("/v1/user-groups/{$userGroup->id}", [
                'group_id' => $groupA->id,
            ])
            ->assertOk();

        $groupA->refresh();
        $groupB->refresh();
        $this->assertSame('2026-01-01 01:00:00', $groupA->updated_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-01-01 00:00:00', $groupB->updated_at->format('Y-m-d H:i:s'));

        Carbon::setTestNow('2026-01-01 02:00:00');
        $this->actingAs($admin, 'api_headers')
            ->putJson("/v1/user-groups/{$userGroup->id}", [
                'group_id' => $groupB->id,
            ])
            ->assertOk();

        $groupA->refresh();
        $groupB->refresh();
        $this->assertSame('2026-01-01 02:00:00', $groupA->updated_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-01-01 02:00:00', $groupB->updated_at->format('Y-m-d H:i:s'));

        Carbon::setTestNow('2026-01-01 03:00:00');
        $this->actingAs($admin, 'api_headers')
            ->deleteJson("/v1/user-groups/{$userGroup->id}")
            ->assertOk();

        $groupA->refresh();
        $groupB->refresh();
        $this->assertSame('2026-01-01 02:00:00', $groupA->updated_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-01-01 03:00:00', $groupB->updated_at->format('Y-m-d H:i:s'));
    }
}
