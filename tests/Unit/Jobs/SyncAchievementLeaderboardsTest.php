<?php

namespace Tests\Unit\Jobs;

use App\Jobs\SyncAchievementLeaderboards;
use App\Models\Achievement;
use App\Models\AchievementLeaderboard;
use App\Models\Competition;
use App\Models\CompetitionInstance;
use App\Models\CompetitionOrganizerType;
use App\Models\CompetitionOutput;
use App\Models\CompetitionRank;
use App\Models\CompetitionScale;
use App\Models\CompetitionTeamType;
use App\Models\CompetitionTimeRange;
use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncAchievementLeaderboardsTest extends TestCase
{
    use RefreshDatabase;

    public function test_computes_points_clamps_overflow_to_php_int_max_and_removes_stale_rows(): void
    {
        $degree = Degree::create(['code' => 'S1', 'name' => 'Sarjana']);
        $faculty = Faculty::create(['code' => 'FT', 'name' => 'Teknik']);
        $major = Major::create([
            'code' => 'PTI',
            'name' => 'Pendidikan Teknik Informatika',
            'degree_id' => $degree->id,
            'faculty_id' => $faculty->id,
        ]);

        $alice = User::create([
            'name' => 'Alice',
            'username' => 'alice_lb',
            'email_address' => 'alice_lb@infiniteuny.id',
            'phone_number' => '+6281111111301',
            'student_id' => '24520241301',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);
        $bob = User::create([
            'name' => 'Bob',
            'username' => 'bob_lb',
            'email_address' => 'bob_lb@infiniteuny.id',
            'phone_number' => '+6281111111302',
            'student_id' => '24520241302',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);

        AchievementLeaderboard::create([
            'user_id' => $bob->id,
            'year' => 2026,
            'total_points' => 999,
        ]);

        // 6 weights of 10,000 => product = 10^24 (> PHP_INT_MAX and > int4 max)
        $teamType = CompetitionTeamType::create(['name' => 'Team', 'weight' => 10000]);
        $team = Team::create([
            'name' => 'Overflow Team',
            'leader_id' => $alice->id,
            'team_type_id' => $teamType->id,
        ]);
        $team->members()->sync([$alice->id]);

        $competition = Competition::create(['name' => 'ICPC']);
        $organizerType = CompetitionOrganizerType::create(['name' => 'International', 'weight' => 10000]);
        $instance = CompetitionInstance::create([
            'competition_id' => $competition->id,
            'name' => 'ICPC 2026',
            'shortname' => 'ICPC',
            'description' => 'ICPC World Finals',
            'url' => 'https://icpc.global',
            'organizer' => 'ICPC',
            'organizer_type_id' => $organizerType->id,
            'logo' => '{"disk":"local","path":"ci","name":"icpc.png","visibility":"private"}',
            'start_date' => '2026-05-01',
            'end_date' => '2026-06-01',
            'location' => 'Tokyo',
        ]);
        $scale = CompetitionScale::create(['name' => 'World', 'weight' => 10000]);
        $timeRange = CompetitionTimeRange::create(['name' => 'Multi-Month', 'weight' => 10000]);
        $output = CompetitionOutput::create(['name' => 'Algorithm', 'weight' => 10000]);
        $rank = CompetitionRank::create(['name' => 'Champion', 'weight' => 10000]);

        Achievement::create([
            'team_id' => $team->id,
            'competition_instance_id' => $instance->id,
            'competition_scale_id' => $scale->id,
            'competition_time_range_id' => $timeRange->id,
            'competition_output_id' => $output->id,
            'competition_rank_id' => $rank->id,
            'competition_branch' => 'Algorithms',
            'competition_start_date' => '2026-05-01',
            'competition_end_date' => '2026-05-10',
            'description' => 'Champion',
            'image' => '{"disk":"local","path":"achievements","name":"win.png","visibility":"private"}',
            'status' => 'ACCEPTED',
        ]);

        $job = new SyncAchievementLeaderboards(2026);
        $this->assertSame(900, $job->uniqueFor);
        $this->assertSame(10, $job->backoff);

        $job->handle();

        $this->assertDatabaseMissing('achievement_leaderboards', ['user_id' => $bob->id, 'year' => 2026]);
        $entry = AchievementLeaderboard::where('user_id', $alice->id)->where('year', 2026)->firstOrFail();
        $this->assertSame(PHP_INT_MAX, $entry->total_points);
    }
}
