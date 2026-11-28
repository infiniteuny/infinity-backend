<?php

namespace Tests\Unit\Http\Controllers\Api\V1;

use App\Jobs\DeleteBlob;
use App\Jobs\DeleteSsoUser;
use App\Models\Achievement;
use App\Models\Competition;
use App\Models\CompetitionInstance;
use App\Models\CompetitionOrganizerType;
use App\Models\CompetitionOutput;
use App\Models\CompetitionRank;
use App\Models\CompetitionScale;
use App\Models\CompetitionTeamType;
use App\Models\CompetitionTimeRange;
use App\Models\CoreTeam;
use App\Models\CoreTeamDivision;
use App\Models\CoreTeamMember;
use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Group;
use App\Models\Major;
use App\Models\Permission;
use App\Models\Team;
use App\Models\Token;
use App\Models\User;
use App\Services\OidcService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use RuntimeException;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    private Major $major;

    private User $alice;

    private User $malice;

    protected function setUp(): void
    {
        parent::setUp();

        $degree = Degree::create(['code' => 'S1', 'name' => 'Sarjana']);
        $faculty = Faculty::create(['code' => 'FT', 'name' => 'Teknik']);
        $this->major = Major::create([
            'code' => 'PTI',
            'name' => 'Pendidikan Teknik Informatika',
            'degree_id' => $degree->id,
            'faculty_id' => $faculty->id,
        ]);

        $this->alice = User::create([
            'name' => 'Alice Putri',
            'username' => 'aliceputri',
            'email_address' => 'Alice.Putri@student.uny.ac.id',
            'phone_number' => '+6281111111111',
            'student_id' => '24520241001',
            'major_id' => $this->major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);
        $this->alice->sso_id = '101';
        $this->alice->saveQuietly();

        $this->malice = User::create([
            'name' => 'Malice Putra',
            'username' => 'maliceputra',
            'email_address' => 'malice.Alice.Putri@student.uny.ac.id',
            'phone_number' => '+6282222222222',
            'student_id' => '9924520241001',
            'major_id' => $this->major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);
        $this->malice->sso_id = '102';
        $this->malice->saveQuietly();

        $role = Group::create([
            'name' => 'Administrator',
            'guard_name' => 'api',
            'is_managed' => true,
        ]);
        $this->alice->assignRole($role);
        foreach (['update-user', 'delete-user'] as $permName) {
            $this->alice->givePermissionTo(Permission::create([
                'name' => $permName,
                'guard_name' => 'api',
            ]));
        }
    }

    public function test_semi_public_query_gating_exact_lookup_and_guard_isolation(): void
    {
        $emailResponse = $this->getJson('/v1/users?filters[email_address]=alice.putri@student.uny.ac.id');
        $emailResponse->assertOk()
            ->assertJsonCount(1, 'data.users')
            ->assertJsonPath('data.users.0.id', $this->alice->id)
            ->assertJsonPath('data.users.0.email_address', 'REDACTED');

        $nimResponse = $this->getJson('/v1/users?filters[student_id]=24520241001');
        $nimResponse->assertOk()
            ->assertJsonCount(1, 'data.users')
            ->assertJsonPath('data.users.0.id', $this->alice->id)
            ->assertJsonPath('data.users.0.student_id', 'REDACTED');

        $this->getJson('/v1/users?filters[email_address]=@student.uny.ac.id')
            ->assertOk()
            ->assertJsonCount(0, 'data.users');
        $this->getJson('/v1/users?filters[student_id]=2452024')
            ->assertOk()
            ->assertJsonCount(0, 'data.users');
        $this->getJson('/v1/users?filters[email_address]=alice.putri@student.uny.ac.id,malice.alice.putri@student.uny.ac.id')
            ->assertOk()
            ->assertJsonCount(0, 'data.users');
        $this->getJson('/v1/users?filters[email_address][]=alice.putri@student.uny.ac.id')
            ->assertOk()
            ->assertJsonCount(0, 'data.users');

        $this->getJson('/v1/users?includes=groups')->assertStatus(400);
        $this->getJson('/v1/users?includes=permissions')->assertStatus(400);
        $this->getJson('/v1/users?filters[phone_number]=628')->assertStatus(400);
        $this->getJson('/v1/users?filters[sso_id]=101')->assertStatus(400);
        $this->getJson('/v1/users?sorts=email_address')->assertStatus(400);
        $this->getJson('/v1/users?sorts=-phone_number')->assertStatus(400);
        $this->getJson('/v1/users?sorts=student_id')->assertStatus(400);
        $this->getJson('/v1/users?filters[is_member]=1&filters[username]=alice&sorts=username')->assertOk();

        $this->getJson("/v1/users/{$this->alice->id}?includes=groups")->assertStatus(400);
        $this->getJson("/v1/users/{$this->alice->id}?includes=permissions")->assertStatus(400);
        $this->getJson("/v1/users/{$this->alice->id}?includes=major,major.degree,major.faculty,personas")->assertOk();

        // Semi-public guard isolation: valid OidcHeadersGuard headers do not bypass failed api_token JWT
        config([
            'auth.defaults.guard' => 'api_headers',
            'auth.defaults.semi_public_guard' => 'api_token',
        ]);
        Auth::shouldUse('api_headers');

        Token::create([
            'sso_id' => 'tok-1',
            'user_id' => $this->malice->id,
            'last_used_at' => now(),
            'created_at' => now()->subMinute(),
            'expires_at' => now()->addHour(),
        ]);

        $this->mock(OidcService::class, function ($mock) {
            $mock->shouldReceive('verify')
                ->once()
                ->with('invalid-jwt')
                ->andThrow(new RuntimeException('Invalid JWT signature'));
            $mock->shouldNotReceive('introspect');
        });

        $this->getJson("/v1/users/{$this->malice->id}", [
            'X-User-Id' => '102',
            'X-User-Email' => 'malice.Alice.Putri@student.uny.ac.id',
            'X-Token-Id' => 'tok-1',
            'X-Token-Created-At' => (string) time(),
            'X-Token-Expires-At' => (string) (time() + 3600),
            'Authorization' => 'Bearer invalid-jwt',
        ])
            ->assertOk()
            ->assertJsonPath('data.user.email_address', 'REDACTED')
            ->assertJsonPath('data.user.phone_number', 'REDACTED')
            ->assertJsonPath('data.user.student_id', 'REDACTED');

        // Authenticated on semi_public_guard allows partial PII filters, sorts, and RBAC includes
        $this->actingAs($this->alice, 'api_token');
        $response = $this->getJson('/v1/users?filters[email_address]=@student.uny.ac.id&sorts=email_address&includes=groups,permissions');
        $response->assertOk()->assertJsonCount(2, 'data.users');
        $this->assertNotSame('REDACTED', $response->json('data.users.0.email_address'));

        $this->getJson('/v1/users?filters[phone_number]=8111&filters[student_id]=24520&filters[sso_id]=101&sorts=-username')
            ->assertOk()
            ->assertJsonCount(1, 'data.users');
    }

    public function test_per_page_clamping_and_destroy_fk_conflict_safety(): void
    {
        Bus::fake([DeleteBlob::class, DeleteSsoUser::class]);

        $this->getJson('/v1/users?per_page=abc')
            ->assertOk()
            ->assertJsonPath('data.meta.per_page', 10);

        $this->getJson('/v1/users?per_page[]=20')
            ->assertOk()
            ->assertJsonPath('data.meta.per_page', 10);

        $this->getJson('/v1/users?per_page=0')
            ->assertOk()
            ->assertJsonPath('data.meta.per_page', 1)
            ->assertJsonCount(1, 'data.users');

        $this->getJson('/v1/users?per_page=-5')
            ->assertOk()
            ->assertJsonPath('data.meta.per_page', 1)
            ->assertJsonCount(1, 'data.users');

        $this->getJson('/v1/users?per_page=500')
            ->assertOk()
            ->assertJsonPath('data.meta.per_page', 500);

        $this->getJson('/v1/users?per_page=100000')
            ->assertOk()
            ->assertJsonPath('data.meta.per_page', 500);

        $ctGroup = Group::create(['name' => 'Core Team 2025', 'guard_name' => 'api', 'is_managed' => true]);
        $coreTeam = CoreTeam::create(['year' => 2025, 'is_active' => true, 'group_id' => $ctGroup->id]);
        $div = CoreTeamDivision::create(['name' => 'Engineering', 'priority' => 1]);
        $userCtPhoto = '{"disk":"public","path":"ct/photos","name":"m1-ct.png","visibility":"public"}';
        CoreTeamMember::create([
            'user_id' => $this->malice->id,
            'core_team_id' => $coreTeam->id,
            'core_team_division_id' => $div->id,
            'photo' => $userCtPhoto,
            'animation' => null,
        ]);

        $teamType = CompetitionTeamType::create(['name' => 'Team', 'weight' => 1]);
        $memberTeam = Team::create([
            'name' => 'Member Team',
            'leader_id' => $this->malice->id,
            'team_type_id' => $teamType->id,
        ]);
        $competition = Competition::create(['name' => 'FindIT']);
        $organizerType = CompetitionOrganizerType::create(['name' => 'University', 'weight' => 1]);
        $instance = CompetitionInstance::create([
            'competition_id' => $competition->id,
            'name' => 'FindIT 2026',
            'shortname' => 'FindIT',
            'description' => 'UGM IT Competition',
            'url' => 'https://find-it.id',
            'organizer' => 'UGM',
            'organizer_type_id' => $organizerType->id,
            'logo' => '{"disk":"local","path":"ci","name":"findit.png","visibility":"private"}',
            'start_date' => '2026-05-01',
            'end_date' => '2026-06-01',
            'location' => 'Yogyakarta',
        ]);
        $scale = CompetitionScale::create(['name' => 'National', 'weight' => 1]);
        $timeRange = CompetitionTimeRange::create(['name' => '1 Month', 'weight' => 1]);
        $output = CompetitionOutput::create(['name' => 'Software', 'weight' => 1]);
        $rank = CompetitionRank::create(['name' => '1st Place', 'weight' => 1]);

        $blockingAchievement = Achievement::create([
            'team_id' => $memberTeam->id,
            'competition_instance_id' => $instance->id,
            'competition_scale_id' => $scale->id,
            'competition_time_range_id' => $timeRange->id,
            'competition_output_id' => $output->id,
            'competition_rank_id' => $rank->id,
            'competition_branch' => 'CTF',
            'competition_start_date' => '2026-05-01',
            'competition_end_date' => '2026-05-10',
            'description' => 'Winner',
            'image' => '{"disk":"local","path":"achievements/images","name":"ctf.png","visibility":"private"}',
            'status' => 'ACCEPTED',
        ]);

        $this->actingAs($this->alice, 'api_headers')
            ->deleteJson("/v1/users/{$this->malice->id}")
            ->assertStatus(409);
        Bus::assertNothingDispatched();

        $blockingAchievement->delete();

        $this->actingAs($this->alice, 'api_headers')
            ->deleteJson("/v1/users/{$this->malice->id}")
            ->assertOk();
        Bus::assertDispatchedTimes(DeleteBlob::class, 1);
        Bus::assertDispatched(DeleteBlob::class, fn (DeleteBlob $job) => $job->manifest === $userCtPhoto);
    }
}
