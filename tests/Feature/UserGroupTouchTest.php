<?php

namespace Tests\Feature;

use App\Jobs\SyncCoreTeamDocsGroups;
use App\Jobs\SyncCoreTeamSsoGroups;
use App\Jobs\UpdateDocsGroup;
use App\Jobs\UpdateSsoGroup;
use App\Models\CoreTeam;
use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Group;
use App\Models\GroupIntegration;
use App\Models\Major;
use App\Models\Permission;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class UserGroupTouchTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $member;

    protected function setUp(): void
    {
        parent::setUp();

        $degree = Degree::create(['code' => 'S1', 'name' => 'Sarjana']);
        $faculty = Faculty::create(['code' => 'FT', 'name' => 'Teknik']);
        $major = Major::create([
            'code' => 'PTI',
            'name' => 'Pendidikan Teknik Informatika',
            'degree_id' => $degree->id,
            'faculty_id' => $faculty->id,
        ]);

        $this->admin = User::create([
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
            $this->admin->givePermissionTo(Permission::create([
                'name' => $permName,
                'guard_name' => 'api',
            ]));
        }

        $this->member = User::create([
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
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_store_update_and_destroy_user_group_endpoints_touch_affected_groups(): void
    {
        Carbon::setTestNow('2026-01-01 00:00:00');
        $groupA = Group::create(['name' => 'Core Team 2025', 'guard_name' => 'api']);
        $groupB = Group::create(['name' => 'Core Team 2026', 'guard_name' => 'api']);

        // 1. Attach via POST /v1/users/{user}/groups touches Group A only
        Carbon::setTestNow('2026-01-01 01:00:00');
        $storeResponse = $this->actingAs($this->admin, 'api_headers')
            ->postJson("/v1/users/{$this->member->id}/groups", [
                'group_id' => $groupA->id,
            ]);
        $storeResponse->assertOk();

        $groupA->refresh();
        $groupB->refresh();
        $this->assertSame('2026-01-01 01:00:00', $groupA->updated_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-01-01 00:00:00', $groupB->updated_at->format('Y-m-d H:i:s'));

        $userGroup = UserGroup::where('user_id', $this->member->id)
            ->where('group_id', $groupA->id)
            ->firstOrFail();

        // 2. No-op update with identical group_id touches neither group
        Carbon::setTestNow('2026-01-01 01:30:00');
        $noopResponse = $this->actingAs($this->admin, 'api_headers')
            ->putJson("/v1/user-groups/{$userGroup->id}", [
                'group_id' => $groupA->id,
            ]);
        $noopResponse->assertOk();

        $groupA->refresh();
        $groupB->refresh();
        $this->assertSame('2026-01-01 01:00:00', $groupA->updated_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-01-01 00:00:00', $groupB->updated_at->format('Y-m-d H:i:s'));

        // 3. Reassigning group_id via PUT /v1/user-groups/{user_group} touches BOTH old Group A and new Group B
        Carbon::setTestNow('2026-01-01 02:00:00');
        $updateResponse = $this->actingAs($this->admin, 'api_headers')
            ->putJson("/v1/user-groups/{$userGroup->id}", [
                'group_id' => $groupB->id,
            ]);
        $updateResponse->assertOk();

        $groupA->refresh();
        $groupB->refresh();
        $this->assertSame('2026-01-01 02:00:00', $groupA->updated_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-01-01 02:00:00', $groupB->updated_at->format('Y-m-d H:i:s'));

        // 4. Detach via DELETE /v1/user-groups/{user_group} touches Group B
        Carbon::setTestNow('2026-01-01 03:00:00');
        $destroyResponse = $this->actingAs($this->admin, 'api_headers')
            ->deleteJson("/v1/user-groups/{$userGroup->id}");
        $destroyResponse->assertOk();

        $groupA->refresh();
        $groupB->refresh();
        $this->assertSame('2026-01-01 02:00:00', $groupA->updated_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-01-01 03:00:00', $groupB->updated_at->format('Y-m-d H:i:s'));
    }

    public function test_updating_user_group_with_preloaded_group_relation_triggers_sso_and_docs_resync_for_both_groups(): void
    {
        Bus::fake();

        Carbon::setTestNow('2026-02-01 00:00:00');
        $oldGroup = Group::create([
            'name' => 'Core Team 2025',
            'guard_name' => 'api',
        ]);
        $oldGroup->forceFill([
            'sso_id' => 'sso-group-2025',
            'sso_last_synced_at' => Carbon::parse('2026-02-01 00:30:00'),
        ])->saveQuietly();
        GroupIntegration::create([
            'group_id' => $oldGroup->id,
            'docs_group_id' => 'docs-group-2025',
            'docs_group_last_synced_at' => Carbon::parse('2026-02-01 00:30:00'),
        ]);
        CoreTeam::create([
            'name' => 'Core Team 2025',
            'year' => 2025,
            'is_active' => true,
            'group_id' => $oldGroup->id,
        ]);

        $newGroup = Group::create([
            'name' => 'Core Team 2026',
            'guard_name' => 'api',
        ]);
        $newGroup->forceFill([
            'sso_id' => 'sso-group-2026',
            'sso_last_synced_at' => Carbon::parse('2026-02-01 00:30:00'),
        ])->saveQuietly();
        GroupIntegration::create([
            'group_id' => $newGroup->id,
            'docs_group_id' => 'docs-group-2026',
            'docs_group_last_synced_at' => Carbon::parse('2026-02-01 00:30:00'),
        ]);
        CoreTeam::create([
            'name' => 'Core Team 2026',
            'year' => 2026,
            'is_active' => true,
            'group_id' => $newGroup->id,
        ]);

        $this->member->groups()->attach($oldGroup->id);
        Group::whereIn('id', [$oldGroup->id, $newGroup->id])->update([
            'updated_at' => Carbon::parse('2026-02-01 00:00:00'),
        ]);
        CoreTeam::query()->update([
            'updated_at' => Carbon::parse('2026-02-01 00:00:00'),
        ]);

        $userGroup = UserGroup::with('group')
            ->where('user_id', $this->member->id)
            ->where('group_id', $oldGroup->id)
            ->firstOrFail();

        $this->assertTrue($userGroup->relationLoaded('group'));
        $this->assertSame($oldGroup->id, $userGroup->group->id);

        Carbon::setTestNow('2026-02-01 01:00:00');
        $userGroup->update(['group_id' => $newGroup->id]);

        // Cached relation was invalidated rather than pointing at the previous group
        $this->assertSame($newGroup->id, $userGroup->group->id);

        (new SyncCoreTeamSsoGroups)->handle();
        (new SyncCoreTeamDocsGroups)->handle();

        Bus::assertDispatched(UpdateSsoGroup::class, fn (UpdateSsoGroup $job) => $job->group->id === $oldGroup->id);
        Bus::assertDispatched(UpdateSsoGroup::class, fn (UpdateSsoGroup $job) => $job->group->id === $newGroup->id);
        Bus::assertDispatched(UpdateDocsGroup::class, fn (UpdateDocsGroup $job) => $job->group->id === $oldGroup->id);
        Bus::assertDispatched(UpdateDocsGroup::class, fn (UpdateDocsGroup $job) => $job->group->id === $newGroup->id);
    }
}
