<?php

namespace Tests\Unit\Jobs;

use App\Jobs\SyncIndividualTeams;
use App\Models\CompetitionTeamType;
use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SyncIndividualTeamsTest extends TestCase
{
    use RefreshDatabase;

    public function test_preloads_existing_teams_and_skips_per_user_transactions_when_already_in_sync(): void
    {
        $teamType = CompetitionTeamType::create(['name' => 'Individual', 'weight' => 1]);
        config(['app.individual_team_type_id' => $teamType->id]);

        $degree = Degree::create(['code' => 'S1', 'name' => 'Sarjana']);
        $faculty = Faculty::create(['code' => 'FT', 'name' => 'Teknik']);
        $major = Major::create([
            'code' => 'PTI',
            'name' => 'Pendidikan Teknik Informatika',
            'degree_id' => $degree->id,
            'faculty_id' => $faculty->id,
        ]);

        $alice = User::create([
            'name' => 'Alice Synced',
            'username' => 'alice_it',
            'email_address' => 'alice_it@infiniteuny.id',
            'phone_number' => '+6281111111401',
            'student_id' => '24520241401',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);
        $aliceTeam = Team::create([
            'name' => 'Alice Synced',
            'leader_id' => $alice->id,
            'team_type_id' => $teamType->id,
        ]);
        $aliceTeam->members()->sync([$alice->id]);

        $bob = User::create([
            'name' => 'Bob New',
            'username' => 'bob_it',
            'email_address' => 'bob_it@infiniteuny.id',
            'phone_number' => '+6281111111402',
            'student_id' => '24520241402',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);

        // First run creates Bob's individual team
        (new SyncIndividualTeams)->handle();
        $this->assertDatabaseHas('teams', ['leader_id' => $bob->id, 'name' => 'Bob New', 'team_type_id' => $teamType->id]);

        // Steady-state run performs only chunk + batch preload queries (<= 5 queries total, not N*4)
        DB::flushQueryLog();
        DB::enableQueryLog();
        (new SyncIndividualTeams)->handle();
        $steadyQueries = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(5, $steadyQueries);
    }
}
