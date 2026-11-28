<?php

namespace Tests\Unit\Http\Controllers\Api\V1;

use App\Jobs\DeleteBlob;
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
use App\Models\Major;
use App\Models\Permission;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class CompetitionInstanceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_fk_409_conflict_on_destroy_preserves_live_logo_blob(): void
    {
        Bus::fake([DeleteBlob::class]);

        $degree = Degree::create(['code' => 'S1', 'name' => 'Sarjana']);
        $faculty = Faculty::create(['code' => 'FT', 'name' => 'Teknik']);
        $major = Major::create([
            'code' => 'PTI',
            'name' => 'Pendidikan Teknik Informatika',
            'degree_id' => $degree->id,
            'faculty_id' => $faculty->id,
        ]);

        $admin = User::create([
            'name' => 'Admin User',
            'username' => 'adminuser',
            'email_address' => 'admin@infiniteuny.id',
            'phone_number' => '+6281111111101',
            'student_id' => '24520241001',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);
        $admin->givePermissionTo(Permission::create(['name' => 'delete-competition', 'guard_name' => 'api']));

        $competition = Competition::create(['name' => 'Gemastik']);
        $organizerType = CompetitionOrganizerType::create(['name' => 'Government', 'weight' => 1]);
        $logoManifest = '{"disk":"local","path":"competition-instances/logos","name":"gemastik.png","visibility":"private"}';

        $instance = CompetitionInstance::create([
            'competition_id' => $competition->id,
            'name' => 'Gemastik XVII',
            'shortname' => 'Gemastik 17',
            'description' => 'National ICT competition',
            'url' => 'https://gemastik.id',
            'organizer' => 'Puspresnas',
            'organizer_type_id' => $organizerType->id,
            'logo' => $logoManifest,
            'start_date' => '2026-08-01',
            'end_date' => '2026-09-01',
            'location' => 'Semarang',
        ]);

        $teamType = CompetitionTeamType::create(['name' => 'Team', 'weight' => 1]);
        $team = Team::create([
            'name' => 'Team Alpha',
            'leader_id' => $admin->id,
            'team_type_id' => $teamType->id,
        ]);
        $scale = CompetitionScale::create(['name' => 'National', 'weight' => 1]);
        $timeRange = CompetitionTimeRange::create(['name' => '1 Month', 'weight' => 1]);
        $output = CompetitionOutput::create(['name' => 'Software', 'weight' => 1]);
        $rank = CompetitionRank::create(['name' => '1st Place', 'weight' => 1]);

        $achievement = Achievement::create([
            'team_id' => $team->id,
            'competition_instance_id' => $instance->id,
            'competition_scale_id' => $scale->id,
            'competition_time_range_id' => $timeRange->id,
            'competition_output_id' => $output->id,
            'competition_rank_id' => $rank->id,
            'competition_branch' => 'Software Development',
            'competition_start_date' => '2026-08-01',
            'competition_end_date' => '2026-08-15',
            'description' => 'First place winner',
            'image' => '{"disk":"local","path":"achievements/images","name":"cert.png","visibility":"private"}',
            'status' => 'ACCEPTED',
        ]);

        $this->actingAs($admin, 'api_headers')
            ->deleteJson("/v1/competition-instances/{$instance->id}")
            ->assertStatus(409);

        $this->assertDatabaseHas('competition_instances', ['id' => $instance->id]);
        Bus::assertNothingDispatched();

        $achievement->delete();

        $this->actingAs($admin, 'api_headers')
            ->deleteJson("/v1/competition-instances/{$instance->id}")
            ->assertOk();

        $this->assertDatabaseMissing('competition_instances', ['id' => $instance->id]);
        Bus::assertDispatchedTimes(DeleteBlob::class, 1);
        Bus::assertDispatched(DeleteBlob::class, fn (DeleteBlob $job) => $job->manifest === $logoManifest);
    }
}
