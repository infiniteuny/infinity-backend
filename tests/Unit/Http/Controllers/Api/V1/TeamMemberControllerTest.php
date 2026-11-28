<?php

namespace Tests\Unit\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\TeamMemberController;
use App\Models\CompetitionTeamType;
use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\Permission;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Tests\TestCase;

class TeamMemberControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_returns_201_and_index_supports_username_filter_and_sort(): void
    {
        Bus::fake();

        $degree = Degree::create(['code' => 'S1', 'name' => 'Sarjana']);
        $faculty = Faculty::create(['code' => 'FT', 'name' => 'Teknik']);
        $major = Major::create([
            'code' => 'PTI',
            'name' => 'Pendidikan Teknik Informatika',
            'degree_id' => $degree->id,
            'faculty_id' => $faculty->id,
        ]);

        $alice = User::create([
            'name' => 'Alice Putri',
            'username' => 'aliceputri',
            'email_address' => 'alice@student.uny.ac.id',
            'phone_number' => '+6281111111101',
            'student_id' => '24520241001',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);
        $bob = User::create([
            'name' => 'Bob Putra',
            'username' => 'bobputra',
            'email_address' => 'bob@student.uny.ac.id',
            'phone_number' => '+6281111111102',
            'student_id' => '24520241002',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);

        foreach (['read-team-member', 'create-team-member'] as $permName) {
            $alice->givePermissionTo(Permission::create([
                'name' => $permName,
                'guard_name' => 'api',
            ]));
        }

        $teamType = CompetitionTeamType::create(['name' => 'Squad', 'weight' => 1]);
        $team = Team::create([
            'name' => 'Alpha Team',
            'leader_id' => $alice->id,
            'team_type_id' => $teamType->id,
        ]);
        $team->members()->attach($alice->id);

        $this->actingAs($alice, 'api_headers')
            ->postJson("/v1/teams/{$team->id}/members", [
                'user_id' => $bob->id,
            ])
            ->assertCreated();

        $this->actingAs($alice, 'api_headers')
            ->getJson("/v1/teams/{$team->id}/members?filters[username]=aliceputri&sorts=username")
            ->assertOk()
            ->assertJsonCount(1, 'data.team_members')
            ->assertJsonPath('data.team_members.0.id', $alice->id);

        $bobPivot = TeamMember::where('team_id', $team->id)
            ->where('user_id', $bob->id)
            ->firstOrFail();
        // Cache pre-lock team snapshot on pivot, then simulate concurrent leadership transfer to Bob
        $this->assertSame($alice->id, $bobPivot->team->leader_id);
        Team::whereKey($team->id)->update(['leader_id' => $bob->id]);

        $this->expectException(AccessDeniedHttpException::class);
        app(TeamMemberController::class)->destroy($bobPivot);
    }
}
