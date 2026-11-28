<?php

namespace Tests\Unit\Http\Controllers\Api\V1;

use App\Enums\StorageVisibility;
use App\Facades\Storage;
use App\Jobs\DeleteBlob;
use App\Models\CoreTeam;
use App\Models\CoreTeamDivision;
use App\Models\CoreTeamMember;
use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Group;
use App\Models\Major;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage as LaravelStorage;
use Tests\TestCase;

class CoreTeamMemberControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $alice;

    private User $malice;

    private CoreTeam $coreTeam;

    private CoreTeamDivision $division;

    protected function setUp(): void
    {
        parent::setUp();

        LaravelStorage::fake('local');
        LaravelStorage::fake('public');

        $degree = Degree::create(['code' => 'S1', 'name' => 'Sarjana']);
        $faculty = Faculty::create(['code' => 'FT', 'name' => 'Teknik']);
        $major = Major::create([
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
            'major_id' => $major->id,
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
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);
        $this->malice->sso_id = '102';
        $this->malice->saveQuietly();

        foreach (['create-core-team-member', 'update-core-team-member'] as $permName) {
            $this->alice->givePermissionTo(Permission::create([
                'name' => $permName,
                'guard_name' => 'api',
            ]));
        }

        $photo = Storage::store(
            UploadedFile::fake()->image('photo.png'),
            'members/photos',
            StorageVisibility::PUBLIC,
        );

        $ctGroup = Group::create(['name' => 'Core Team 2025', 'guard_name' => 'api', 'is_managed' => true]);
        $this->coreTeam = CoreTeam::create([
            'group_id' => $ctGroup->id,
            'year' => 2025,
            'is_active' => true,
        ]);
        $this->division = CoreTeamDivision::create([
            'name' => 'Engineering',
            'priority' => 1,
        ]);
        $this->coreTeam->members()->attach($this->alice->id, [
            'core_team_division_id' => $this->division->id,
            'photo' => $photo,
            'animation' => null,
        ]);
        $this->coreTeam->members()->attach($this->malice->id, [
            'core_team_division_id' => $this->division->id,
            'photo' => $photo,
            'animation' => null,
        ]);
    }

    public function test_store_returns_201_and_semi_public_queries_enforce_pii_and_rbac_gating(): void
    {
        Bus::fake();

        $uri = "/v1/core-teams/{$this->coreTeam->id}/members";

        $this->getJson("{$uri}?filters[email_address]=alice.putri@student.uny.ac.id")
            ->assertOk()
            ->assertJsonCount(1, 'data.core_team_members')
            ->assertJsonPath('data.core_team_members.0.id', $this->alice->id)
            ->assertJsonPath('data.core_team_members.0.email_address', 'REDACTED');

        $this->getJson("{$uri}?filters[email_address]=@student.uny.ac.id")
            ->assertOk()
            ->assertJsonCount(0, 'data.core_team_members');

        $this->getJson("{$uri}?includes=groups")->assertStatus(400);
        $this->getJson("{$uri}?includes=permissions")->assertStatus(400);
        $this->getJson("{$uri}?filters[phone_number]=628")->assertStatus(400);
        $this->getJson("{$uri}?sorts=email_address")->assertStatus(400);
        $this->getJson("{$uri}?filters[is_member]=1&filters[username]=alice&sorts=username")->assertOk();

        $pivotId = $this->coreTeam->members()->wherePivot('user_id', $this->alice->id)->firstOrFail()->membership->id;
        $this->getJson("/v1/core-team-members/{$pivotId}?includes=groups")->assertStatus(400);
        $this->getJson("/v1/core-team-members/{$pivotId}?includes=major,major.degree,major.faculty,personas")->assertOk();

        $semiPublicGuard = config('auth.defaults.semi_public_guard');
        $this->actingAs($this->alice, $semiPublicGuard);
        $this->getJson("{$uri}?filters[email_address]=@student.uny.ac.id&sorts=email_address&includes=groups,permissions")
            ->assertOk()
            ->assertJsonCount(2, 'data.core_team_members');

        $newUser = User::create([
            'name' => 'New CT Member',
            'username' => 'newctmember',
            'email_address' => 'newct@student.uny.ac.id',
            'phone_number' => '+6283333333333',
            'student_id' => '24520241003',
            'major_id' => $this->alice->major_id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);

        $this->actingAs($this->alice, 'api_headers')
            ->post($uri, [
                'user_id' => $newUser->id,
                'core_team_division_id' => $this->division->id,
                'photo' => UploadedFile::fake()->image('ct.png'),
            ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('data.core_team_member.email_address', 'newct@student.uny.ac.id');
    }

    public function test_three_state_animation_update_deletes_old_blobs_only_after_commit(): void
    {
        Bus::fake([DeleteBlob::class]);

        $oldPhoto = '{"disk":"public","path":"core-teams/photos","name":"old-photo.png","visibility":"public"}';
        $oldAnim = '{"disk":"public","path":"core-teams/animations","name":"old-anim.webm","visibility":"public"}';

        $member = CoreTeamMember::where('user_id', $this->alice->id)
            ->where('core_team_id', $this->coreTeam->id)
            ->firstOrFail();
        $member->update([
            'photo' => $oldPhoto,
            'animation' => $oldAnim,
        ]);

        $this->actingAs($this->alice, 'api_headers')
            ->put("/v1/core-team-members/{$member->id}", [
                'photo' => UploadedFile::fake()->image('new-photo.png'),
                'animation' => null,
            ], ['Accept' => 'application/json'])
            ->assertOk();

        $member->refresh();
        $this->assertNotSame($oldPhoto, $member->getRawOriginal('photo'));
        $this->assertNull($member->getRawOriginal('animation'));

        Bus::assertDispatchedTimes(DeleteBlob::class, 2);
        Bus::assertDispatched(DeleteBlob::class, fn (DeleteBlob $job) => $job->manifest === $oldPhoto);
        Bus::assertDispatched(DeleteBlob::class, fn (DeleteBlob $job) => $job->manifest === $oldAnim);
    }
}
