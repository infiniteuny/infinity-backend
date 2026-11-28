<?php

namespace Tests\Unit\Models;

use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Group;
use App\Models\Major;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class UserGroupTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_updating_group_id_touches_both_previous_and_new_groups_and_invalidates_cached_relation(): void
    {
        $degree = Degree::create(['code' => 'S1', 'name' => 'Sarjana']);
        $faculty = Faculty::create(['code' => 'FT', 'name' => 'Teknik']);
        $major = Major::create([
            'code' => 'PTI',
            'name' => 'Pendidikan Teknik Informatika',
            'degree_id' => $degree->id,
            'faculty_id' => $faculty->id,
        ]);
        $user = User::create([
            'name' => 'Member',
            'username' => 'member',
            'email_address' => 'member@infiniteuny.id',
            'phone_number' => '+6281111111111',
            'student_id' => '24520241001',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);

        Carbon::setTestNow('2026-02-01 00:00:00');
        $oldGroup = Group::create(['name' => 'Core Team 2025']);
        $newGroup = Group::create(['name' => 'Core Team 2026']);

        $user->groups()->attach($oldGroup->id);
        Group::whereIn('id', [$oldGroup->id, $newGroup->id])->update([
            'updated_at' => Carbon::parse('2026-02-01 00:00:00'),
        ]);

        $userGroup = UserGroup::with('group')
            ->where('user_id', $user->id)
            ->where('group_id', $oldGroup->id)
            ->firstOrFail();

        $this->assertTrue($userGroup->relationLoaded('group'));
        $this->assertSame($oldGroup->id, $userGroup->group->id);

        // No-op update touches neither group
        Carbon::setTestNow('2026-02-01 00:30:00');
        $userGroup->update(['group_id' => $oldGroup->id]);
        $this->assertSame('2026-02-01 00:00:00', $oldGroup->fresh()->updated_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-02-01 00:00:00', $newGroup->fresh()->updated_at->format('Y-m-d H:i:s'));

        // Reassigning group_id touches both groups and invalidates cached relation
        Carbon::setTestNow('2026-02-01 01:00:00');
        $userGroup->update(['group_id' => $newGroup->id]);

        $this->assertSame($newGroup->id, $userGroup->group->id);
        $this->assertSame('2026-02-01 01:00:00', $oldGroup->fresh()->updated_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-02-01 01:00:00', $newGroup->fresh()->updated_at->format('Y-m-d H:i:s'));
    }
}
