<?php

namespace Tests\Unit\Models;

use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_casts_attributes_and_evaluates_is_active_membership_window(): void
    {
        $degree = Degree::create(['code' => 'S1', 'name' => 'Sarjana']);
        $faculty = Faculty::create(['code' => 'FT', 'name' => 'Teknik']);
        $major = Major::create([
            'code' => 'PTI',
            'name' => 'Pendidikan Teknik Informatika',
            'degree_id' => $degree->id,
            'faculty_id' => $faculty->id,
        ]);

        $today = Carbon::today();
        $user = User::create([
            'name' => 'Alice',
            'username' => 'alice',
            'email_address' => 'alice@infiniteuny.id',
            'phone_number' => '+6281111111111',
            'student_id' => '24520241001',
            'major_id' => $major->id,
            'links' => ['github' => 'https://github.com/infiniteuny'],
            'start_date' => $today->copy()->subDay()->toDateString(),
            'end_date' => $today->copy()->addDay()->toDateString(),
            'is_member' => 1,
            'is_extraordinary' => 0,
        ]);

        User::withoutTimestamps(function () use ($user) {
            $user->sso_last_synced_at = '2026-06-01 12:00:00';
            $user->saveQuietly();
        });

        $user->refresh();

        $this->assertIsObject($user->links);
        $this->assertTrue($user->is_member);
        $this->assertFalse($user->is_extraordinary);
        $this->assertTrue($user->is_active);
        $this->assertInstanceOf(CarbonInterface::class, $user->sso_last_synced_at);
    }
}
