<?php

namespace Tests\Unit\Models;

use App\Models\AchievementLeaderboard;
use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AchievementLeaderboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_casts_year_and_total_points_to_integers(): void
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
            'name' => 'Alice',
            'username' => 'alice',
            'email_address' => 'alice@infiniteuny.id',
            'phone_number' => '+6281111111111',
            'student_id' => '24520241001',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);

        $leaderboard = AchievementLeaderboard::create([
            'user_id' => $user->id,
            'year' => '2026',
            'total_points' => '150',
        ])->fresh();

        $this->assertSame(2026, $leaderboard->year);
        $this->assertSame(150, $leaderboard->total_points);
    }
}
