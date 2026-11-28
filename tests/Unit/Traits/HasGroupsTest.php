<?php

namespace Tests\Unit\Traits;

use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Group;
use App\Models\Major;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HasGroupsTest extends TestCase
{
    use RefreshDatabase;

    public function test_assign_sync_and_remove_group_invalidate_cached_groups_relation(): void
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
            'name' => 'Test User',
            'username' => 'testuser',
            'email_address' => 'test@infiniteuny.id',
            'phone_number' => '+6281111111111',
            'student_id' => '24520241001',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);

        $groupA = Group::create(['name' => 'Group A']);
        $groupB = Group::create(['name' => 'Group B']);

        $this->assertCount(0, $user->groups);

        $user->assignGroup($groupA);
        $this->assertSame(['Group A'], $user->groups->pluck('name')->all());

        $user->syncGroups([$groupB]);
        $this->assertSame(['Group B'], $user->groups->pluck('name')->all());

        $user->removeGroup($groupB);
        $this->assertCount(0, $user->groups);
    }
}
