<?php

namespace Tests\Unit\Http\Controllers\Api\V1;

use App\Enums\StorageVisibility;
use App\Facades\Storage;
use App\Models\CommunityGroup;
use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Group;
use App\Models\Major;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage as LaravelStorage;
use Tests\TestCase;

class CommunityGroupMemberControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $alice;

    private User $malice;

    private CommunityGroup $communityGroup;

    protected function setUp(): void
    {
        parent::setUp();

        LaravelStorage::fake('local');

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

        $role = Group::create(['name' => 'Administrator', 'guard_name' => 'api', 'is_managed' => true]);
        $this->alice->assignRole($role);
        $this->alice->givePermissionTo(Permission::create([
            'name' => 'create-community-group-member',
            'guard_name' => 'api',
        ]));

        $logo = Storage::store(
            UploadedFile::fake()->image('logo.png'),
            'community-groups/logos',
            StorageVisibility::PUBLIC,
        );

        $this->communityGroup = CommunityGroup::create([
            'name' => 'Software Engineering',
            'description' => 'SE Community Group',
            'logo' => $logo,
            'priority' => 1,
            'is_active' => true,
        ]);
        $this->communityGroup->members()->attach([$this->alice->id, $this->malice->id]);
    }

    public function test_store_returns_201_and_semi_public_queries_enforce_pii_and_rbac_gating(): void
    {
        $uri = "/v1/community-groups/{$this->communityGroup->id}/members";

        $this->getJson("{$uri}?filters[email_address]=alice.putri@student.uny.ac.id")
            ->assertOk()
            ->assertJsonCount(1, 'data.community_group_members')
            ->assertJsonPath('data.community_group_members.0.id', $this->alice->id)
            ->assertJsonPath('data.community_group_members.0.email_address', 'REDACTED');

        $this->getJson("{$uri}?filters[student_id]=24520241001")
            ->assertOk()
            ->assertJsonCount(1, 'data.community_group_members')
            ->assertJsonPath('data.community_group_members.0.id', $this->alice->id)
            ->assertJsonPath('data.community_group_members.0.student_id', 'REDACTED');

        $this->getJson("{$uri}?filters[email_address]=@student.uny.ac.id")
            ->assertOk()
            ->assertJsonCount(0, 'data.community_group_members');
        $this->getJson("{$uri}?filters[email_address]=alice.putri@student.uny.ac.id,malice.alice.putri@student.uny.ac.id")
            ->assertOk()
            ->assertJsonCount(0, 'data.community_group_members');

        $this->getJson("{$uri}?includes=groups")->assertStatus(400);
        $this->getJson("{$uri}?includes=permissions")->assertStatus(400);
        $this->getJson("{$uri}?filters[phone_number]=628")->assertStatus(400);
        $this->getJson("{$uri}?filters[sso_id]=101")->assertStatus(400);
        $this->getJson("{$uri}?sorts=email_address")->assertStatus(400);
        $this->getJson("{$uri}?filters[is_member]=1&filters[username]=alice&sorts=username")->assertOk();

        $pivotId = $this->communityGroup->members()->wherePivot('user_id', $this->alice->id)->firstOrFail()->membership->id;
        $this->getJson("/v1/community-group-members/{$pivotId}?includes=groups")->assertStatus(400);
        $this->getJson("/v1/community-group-members/{$pivotId}?includes=major,major.degree,major.faculty,personas")->assertOk();

        $semiPublicGuard = config('auth.defaults.semi_public_guard');
        $this->actingAs($this->alice, $semiPublicGuard);
        $this->getJson("{$uri}?filters[email_address]=@student.uny.ac.id&sorts=email_address&includes=groups,permissions")
            ->assertOk()
            ->assertJsonCount(2, 'data.community_group_members');

        $newUser = User::create([
            'name' => 'New Member',
            'username' => 'newmember',
            'email_address' => 'new@student.uny.ac.id',
            'phone_number' => '+6283333333333',
            'student_id' => '24520241003',
            'major_id' => $this->alice->major_id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);

        $this->actingAs($this->alice, 'api_headers')
            ->postJson($uri, ['user_id' => $newUser->id])
            ->assertCreated()
            ->assertJsonPath('data.community_group_member.email_address', 'new@student.uny.ac.id');
    }
}
