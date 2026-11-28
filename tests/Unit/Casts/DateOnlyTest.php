<?php

namespace Tests\Unit\Casts;

use App\Casts\DateOnly;
use App\Models\Achievement;
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
use App\Models\FundApplication;
use App\Models\Major;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DateOnlyTest extends TestCase
{
    use RefreshDatabase;

    public function test_cast_handles_null_empty_and_date_inputs(): void
    {
        $cast = new DateOnly;
        $model = new CompetitionInstance;

        $this->assertNull($cast->get($model, 'start_date', null, []));
        $this->assertNull($cast->get($model, 'start_date', '', []));
        $this->assertNull($cast->set($model, 'start_date', null, []));
        $this->assertNull($cast->set($model, 'start_date', '', []));
        $this->assertNull($cast->serialize($model, 'start_date', null, []));
        $this->assertNull($cast->serialize($model, 'start_date', '', []));

        $this->assertSame('2026-05-10', $cast->set($model, 'start_date', '2026-05-10T18:45:00+00:00', []));

        $date = $cast->get($model, 'start_date', '2026-05-10 15:20:30', []);
        $this->assertInstanceOf(Carbon::class, $date);
        $this->assertSame('2026-05-10 00:00:00', $date->format('Y-m-d H:i:s'));

        $expectedAtom = Carbon::parse('2026-05-10')->startOfDay()->format(DATE_ATOM);
        $this->assertSame($expectedAtom, $cast->serialize($model, 'start_date', $date, []));
        $this->assertSame($expectedAtom, $cast->serialize($model, 'start_date', '2026-05-10', []));
    }

    public function test_models_store_y_m_d_in_database_and_serialize_as_date_atom(): void
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

        $organizerType = CompetitionOrganizerType::create(['name' => 'Gov', 'weight' => 2]);
        $teamType = CompetitionTeamType::create(['name' => 'Team', 'weight' => 3]);
        $scale = CompetitionScale::create(['name' => 'National', 'weight' => 5]);
        $timeRange = CompetitionTimeRange::create(['name' => 'Multi-day', 'weight' => 2]);
        $output = CompetitionOutput::create(['name' => 'Product', 'weight' => 3]);
        $rank = CompetitionRank::create(['name' => 'First', 'weight' => 5]);

        $team = Team::create([
            'leader_id' => $user->id,
            'name' => 'Team Alpha',
            'team_type_id' => $teamType->id,
        ]);

        $competition = Competition::create([
            'name' => 'Gemastik',
            'shortname' => 'GEM',
        ]);

        $instance = CompetitionInstance::create([
            'competition_id' => $competition->id,
            'name' => 'Gemastik 2026',
            'shortname' => 'GEM26',
            'description' => 'National IT Competition',
            'url' => 'https://example.com',
            'organizer' => 'Kemdikbud',
            'organizer_type_id' => $organizerType->id,
            'logo' => '',
            'start_date' => '2026-05-10 14:30:00',
            'end_date' => Carbon::parse('2026-05-12 22:15:00'),
            'location' => 'Yogyakarta',
        ]);

        $fundApplication = FundApplication::create([
            'team_id' => $team->id,
            'competition_instance_id' => $instance->id,
            'competition_scale_id' => $scale->id,
            'competition_branch' => 'Software',
            'competition_start_date' => '2026-05-10 09:00:00',
            'competition_end_date' => '2026-05-12 17:00:00',
            'letter_of_acceptance' => '',
            'proposal' => '',
            'status' => 'PENDING',
        ]);

        $achievement = Achievement::create([
            'team_id' => $team->id,
            'competition_instance_id' => $instance->id,
            'competition_scale_id' => $scale->id,
            'competition_time_range_id' => $timeRange->id,
            'competition_output_id' => $output->id,
            'competition_rank_id' => $rank->id,
            'competition_branch' => 'Software',
            'competition_start_date' => '2026-05-10 09:00:00',
            'competition_end_date' => '2026-05-12 17:00:00',
            'description' => 'Won first place',
            'image' => '',
            'status' => 'APPROVED',
        ]);

        $this->assertSame('2026-05-10', DB::table('competition_instances')->where('id', $instance->id)->value('start_date'));
        $this->assertSame('2026-05-12', DB::table('competition_instances')->where('id', $instance->id)->value('end_date'));
        $this->assertSame('2026-05-10', DB::table('fund_applications')->where('id', $fundApplication->id)->value('competition_start_date'));
        $this->assertSame('2026-05-12', DB::table('fund_applications')->where('id', $fundApplication->id)->value('competition_end_date'));
        $this->assertSame('2026-05-10', DB::table('achievements')->where('id', $achievement->id)->value('competition_start_date'));
        $this->assertSame('2026-05-12', DB::table('achievements')->where('id', $achievement->id)->value('competition_end_date'));

        $this->assertTrue(CompetitionInstance::where('start_date', '<=', '2026-05-10')->whereKey($instance->id)->exists());

        $expectedStart = Carbon::parse('2026-05-10')->startOfDay()->format(DATE_ATOM);
        $expectedEnd = Carbon::parse('2026-05-12')->startOfDay()->format(DATE_ATOM);

        $instanceArray = $instance->fresh()->toArray();
        $this->assertSame($expectedStart, $instanceArray['start_date']);
        $this->assertSame($expectedEnd, $instanceArray['end_date']);

        $fundArray = $fundApplication->fresh()->toArray();
        $this->assertSame($expectedStart, $fundArray['competition_start_date']);
        $this->assertSame($expectedEnd, $fundArray['competition_end_date']);

        $achievementArray = $achievement->fresh()->toArray();
        $this->assertSame($expectedStart, $achievementArray['competition_start_date']);
        $this->assertSame($expectedEnd, $achievementArray['competition_end_date']);
    }
}
