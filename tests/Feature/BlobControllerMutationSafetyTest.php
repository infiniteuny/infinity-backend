<?php

namespace Tests\Feature;

use App\Facades\Storage as BlobStorage;
use App\Jobs\DeleteBlob;
use App\Models\Achievement;
use App\Models\CommunityGroup;
use App\Models\CommunityGroupAdmin;
use App\Models\CommunityGroupAdminMember;
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
use App\Models\FundApplication;
use App\Models\Group;
use App\Models\Major;
use App\Models\Permission;
use App\Models\Persona;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage as LaravelStorage;
use RuntimeException;
use Tests\TestCase;

class BlobControllerMutationSafetyTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Major $major;

    protected function setUp(): void
    {
        parent::setUp();

        LaravelStorage::fake('local');
        LaravelStorage::fake('public');

        $degree = Degree::create(['code' => 'S1', 'name' => 'Sarjana']);
        $faculty = Faculty::create(['code' => 'FT', 'name' => 'Teknik']);
        $this->major = Major::create([
            'code' => 'PTI',
            'name' => 'Pendidikan Teknik Informatika',
            'degree_id' => $degree->id,
            'faculty_id' => $faculty->id,
        ]);

        $this->admin = User::create([
            'name' => 'Admin User',
            'username' => 'adminuser',
            'email_address' => 'admin@infiniteuny.id',
            'phone_number' => '+6281111111101',
            'student_id' => '24520241001',
            'major_id' => $this->major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);

        foreach ([
            'delete-competition',
            'update-persona',
            'delete-persona',
            'delete-community-group',
            'update-core-team-member',
            'delete-core-team-member',
            'delete-core-team-division',
            'delete-user',
            'update-fund-application',
            'read-fund-application',
        ] as $permName) {
            $this->admin->givePermissionTo(Permission::create([
                'name' => $permName,
                'guard_name' => 'api',
            ]));
        }
    }

    public function test_fk_409_conflict_on_competition_instance_destroy_preserves_live_logo_blob(): void
    {
        Bus::fake([DeleteBlob::class]);

        $competition = Competition::create(['name' => 'Gemastik']);
        $organizerType = CompetitionOrganizerType::create(['name' => 'Government', 'weight' => 1.0]);
        $logoManifest = $this->manifest('local', 'competition-instances/logos', 'gemastik.png', 'private');

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

        $teamType = CompetitionTeamType::create(['name' => 'Team', 'weight' => 1.0]);
        $team = Team::create([
            'name' => 'Team Alpha',
            'leader_id' => $this->admin->id,
            'team_type_id' => $teamType->id,
        ]);
        $scale = CompetitionScale::create(['name' => 'National', 'weight' => 1.0]);
        $timeRange = CompetitionTimeRange::create(['name' => '1 Month', 'min_days' => 1, 'max_days' => 60, 'weight' => 1.0]);
        $output = CompetitionOutput::create(['name' => 'Software', 'weight' => 1.0]);
        $rank = CompetitionRank::create(['name' => '1st Place', 'weight' => 1.0]);

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
            'image' => $this->manifest('local', 'achievements/images', 'cert.png', 'private'),
            'status' => 'ACCEPTED',
        ]);

        // 1. Delete while referenced by Achievement -> 409 Conflict & ZERO DeleteBlob dispatched!
        $conflictResponse = $this->actingAs($this->admin, 'api_headers')
            ->deleteJson("/v1/competition-instances/{$instance->id}");

        $conflictResponse->assertStatus(409);
        $this->assertDatabaseHas('competition_instances', ['id' => $instance->id]);
        Bus::assertNothingDispatched();

        // 2. Remove referencing Achievement, then delete -> 200 OK & DeleteBlob dispatched AFTER delete
        $achievement->delete();

        $okResponse = $this->actingAs($this->admin, 'api_headers')
            ->deleteJson("/v1/competition-instances/{$instance->id}");

        $okResponse->assertOk();
        $this->assertDatabaseMissing('competition_instances', ['id' => $instance->id]);
        Bus::assertDispatchedTimes(DeleteBlob::class, 1);
        Bus::assertDispatched(DeleteBlob::class, fn (DeleteBlob $job) => $job->manifest === $logoManifest);
    }

    public function test_failed_db_update_preserves_old_blob_and_cleans_up_newly_uploaded_blob(): void
    {
        Bus::fake([DeleteBlob::class]);

        $oldManifest = $this->manifest('public', 'personas/logos', 'old-logo.png', 'public');
        $persona = Persona::create([
            'name' => 'Frontend Developer',
            'priority' => 1,
            'description' => 'Builds UIs',
            'logo' => $oldManifest,
        ]);

        $eventName = 'eloquent.updating: '.Persona::class;
        Event::listen($eventName, function (): never {
            throw new RuntimeException('Simulated database failure during update');
        });

        try {
            $response = $this->actingAs($this->admin, 'api_headers')
                ->put("/v1/personas/{$persona->id}", [
                    'logo' => UploadedFile::fake()->image('new-logo.png'),
                ], ['Accept' => 'application/json']);

            $response->assertStatus(500);
        } finally {
            Event::forget($eventName);
        }

        // Old blob was NOT deleted, while newly uploaded orphan WAS queued for cleanup
        $persona->refresh();
        $this->assertSame($oldManifest, $persona->getRawOriginal('logo'));

        Bus::assertDispatchedTimes(DeleteBlob::class, 1);
        Bus::assertDispatched(DeleteBlob::class, fn (DeleteBlob $job) => $job->manifest !== $oldManifest);
    }

    public function test_multi_blob_update_failure_on_second_upload_rolls_back_first_upload_and_keeps_live_blobs(): void
    {
        Bus::fake([DeleteBlob::class]);

        $teamType = CompetitionTeamType::create(['name' => 'Team', 'weight' => 1.0]);
        $team = Team::create([
            'name' => 'Team Beta',
            'leader_id' => $this->admin->id,
            'team_type_id' => $teamType->id,
        ]);
        $competition = Competition::create(['name' => 'CompFest']);
        $organizerType = CompetitionOrganizerType::create(['name' => 'University', 'weight' => 1.0]);
        $instance = CompetitionInstance::create([
            'competition_id' => $competition->id,
            'name' => 'CompFest 18',
            'shortname' => 'CF18',
            'description' => 'IT Competition',
            'url' => 'https://compfest.id',
            'organizer' => 'UI',
            'organizer_type_id' => $organizerType->id,
            'logo' => $this->manifest('local', 'ci', 'cf.png', 'private'),
            'start_date' => '2026-07-01',
            'end_date' => '2026-08-01',
            'location' => 'Depok',
        ]);
        $scale = CompetitionScale::create(['name' => 'National', 'weight' => 1.0]);

        $oldLoa = $this->manifest('local', 'fund-applications/letter-of-acceptances', 'old-loa.pdf', 'private');
        $oldProposal = $this->manifest('local', 'fund-applications/proposals', 'old-proposal.doc', 'private');

        $fundApp = FundApplication::create([
            'team_id' => $team->id,
            'competition_instance_id' => $instance->id,
            'competition_scale_id' => $scale->id,
            'competition_branch' => 'AI Innovation',
            'competition_start_date' => '2026-07-01',
            'competition_end_date' => '2026-08-01',
            'letter_of_acceptance' => $oldLoa,
            'proposal' => $oldProposal,
            'status' => 'PENDING',
        ]);

        $newLoaManifest = $this->manifest('local', 'fund-applications/letter-of-acceptances', 'new-loa.pdf', 'private');
        BlobStorage::shouldReceive('store')
            ->once()
            ->andReturn($newLoaManifest);
        BlobStorage::shouldReceive('store')
            ->once()
            ->andThrow(new RuntimeException('S3 upload timeout on proposal'));

        $fakePdf = UploadedFile::fake()->createWithContent('new-loa.pdf', "%PDF-1.4\n1 0 obj\n<<>>\nendobj\n");
        $fakeDoc = UploadedFile::fake()->createWithContent(
            'new-proposal.doc',
            "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1".str_repeat("\x00", 512),
        );

        $response = $this->actingAs($this->admin, 'api_headers')
            ->put("/v1/fund-applications/{$fundApp->id}", [
                'letter_of_acceptance' => $fakePdf,
                'proposal' => $fakeDoc,
            ], ['Accept' => 'application/json']);

        $response->assertStatus(500);

        $fundApp->refresh();
        $this->assertSame($oldLoa, $fundApp->getRawOriginal('letter_of_acceptance'));
        $this->assertSame($oldProposal, $fundApp->getRawOriginal('proposal'));

        // Only the orphaned first upload ($newLoaManifest) is deleted; neither $oldLoa nor $oldProposal is touched!
        Bus::assertDispatchedTimes(DeleteBlob::class, 1);
        Bus::assertDispatched(DeleteBlob::class, fn (DeleteBlob $job) => $job->manifest === $newLoaManifest);
    }

    public function test_core_team_member_three_state_animation_update_deletes_old_blobs_only_after_commit(): void
    {
        Bus::fake([DeleteBlob::class]);

        $group = Group::create(['name' => 'Core Team 2025', 'guard_name' => 'api', 'is_managed' => true]);
        $coreTeam = CoreTeam::create(['year' => 2025, 'is_active' => true, 'group_id' => $group->id]);
        $division = CoreTeamDivision::create(['name' => 'Design', 'priority' => 1]);

        $oldPhoto = $this->manifest('public', 'core-teams/photos', 'old-photo.png', 'public');
        $oldAnim = $this->manifest('public', 'core-teams/animations', 'old-anim.webm', 'public');

        $member = CoreTeamMember::create([
            'user_id' => $this->admin->id,
            'core_team_id' => $coreTeam->id,
            'core_team_division_id' => $division->id,
            'photo' => $oldPhoto,
            'animation' => $oldAnim,
        ]);

        // Clearing animation via explicit null + replacing photo dispatches DeleteBlob for both old manifests after commit
        $response = $this->actingAs($this->admin, 'api_headers')
            ->put("/v1/core-team-members/{$member->id}", [
                'photo' => UploadedFile::fake()->image('new-photo.png'),
                'animation' => null,
            ], ['Accept' => 'application/json']);

        $response->assertOk();

        $member->refresh();
        $this->assertNotSame($oldPhoto, $member->getRawOriginal('photo'));
        $this->assertNull($member->getRawOriginal('animation'));

        Bus::assertDispatchedTimes(DeleteBlob::class, 2);
        Bus::assertDispatched(DeleteBlob::class, fn (DeleteBlob $job) => $job->manifest === $oldPhoto);
        Bus::assertDispatched(DeleteBlob::class, fn (DeleteBlob $job) => $job->manifest === $oldAnim);
    }

    public function test_community_group_division_and_user_destroy_clean_up_cascaded_member_blobs_only_after_commit(): void
    {
        Bus::fake([DeleteBlob::class]);

        $cgLogo = $this->manifest('public', 'cg/logos', 'uiux.png', 'public');
        $cg = CommunityGroup::create([
            'name' => 'UI/UX',
            'priority' => 1,
            'description' => 'Design group',
            'logo' => $cgLogo,
            'is_active' => true,
        ]);

        $cgaGroup = Group::create(['name' => 'CG Admin 2025', 'guard_name' => 'api', 'is_managed' => true]);
        $cgAdmin = CommunityGroupAdmin::create(['year' => 2025, 'is_active' => true, 'group_id' => $cgaGroup->id]);

        $memberUser = User::create([
            'name' => 'Member One',
            'username' => 'memberone',
            'email_address' => 'member1@infiniteuny.id',
            'phone_number' => '+6281111111199',
            'student_id' => '24520241099',
            'major_id' => $this->major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);

        $cgaPhoto = $this->manifest('public', 'cga/photos', 'm1.png', 'public');
        CommunityGroupAdminMember::create([
            'user_id' => $memberUser->id,
            'community_group_admin_id' => $cgAdmin->id,
            'community_group_id' => $cg->id,
            'photo' => $cgaPhoto,
            'animation' => null,
        ]);

        // 1. CommunityGroup destroy cleans up both cg logo and cascaded cga member photo
        $okCgDelete = $this->actingAs($this->admin, 'api_headers')
            ->deleteJson("/v1/community-groups/{$cg->id}");
        $okCgDelete->assertOk();
        Bus::assertDispatchedTimes(DeleteBlob::class, 2);
        Bus::assertDispatched(DeleteBlob::class, fn (DeleteBlob $job) => $job->manifest === $cgLogo);
        Bus::assertDispatched(DeleteBlob::class, fn (DeleteBlob $job) => $job->manifest === $cgaPhoto);

        // 2. CoreTeamDivision destroy cleans up cascaded CoreTeamMember photo
        $ctGroup = Group::create(['name' => 'Core Team 2025', 'guard_name' => 'api', 'is_managed' => true]);
        $coreTeam = CoreTeam::create(['year' => 2025, 'is_active' => true, 'group_id' => $ctGroup->id]);
        $div1 = CoreTeamDivision::create(['name' => 'Product', 'priority' => 2]);
        $div1Photo = $this->manifest('public', 'ct/photos', 'div1-ct.png', 'public');
        CoreTeamMember::create([
            'user_id' => $this->admin->id,
            'core_team_id' => $coreTeam->id,
            'core_team_division_id' => $div1->id,
            'photo' => $div1Photo,
            'animation' => null,
        ]);

        $okDivDelete = $this->actingAs($this->admin, 'api_headers')
            ->deleteJson("/v1/core-team-divisions/{$div1->id}");
        $okDivDelete->assertOk();
        Bus::assertDispatchedTimes(DeleteBlob::class, 3);
        Bus::assertDispatched(DeleteBlob::class, fn (DeleteBlob $job) => $job->manifest === $div1Photo);

        // 3. Blocking Team->Achievement causes 409 on User destroy -> zero cascaded CoreTeamMember blobs deleted
        $div2 = CoreTeamDivision::create(['name' => 'Engineering', 'priority' => 3]);
        $userCtPhoto = $this->manifest('public', 'ct/photos', 'm1-ct.png', 'public');
        CoreTeamMember::create([
            'user_id' => $memberUser->id,
            'core_team_id' => $coreTeam->id,
            'core_team_division_id' => $div2->id,
            'photo' => $userCtPhoto,
            'animation' => null,
        ]);

        $teamType = CompetitionTeamType::create(['name' => 'Team', 'weight' => 1.0]);
        $memberTeam = Team::create([
            'name' => 'Member Team',
            'leader_id' => $memberUser->id,
            'team_type_id' => $teamType->id,
        ]);
        $competition = Competition::create(['name' => 'FindIT']);
        $organizerType = CompetitionOrganizerType::create(['name' => 'University', 'weight' => 1.0]);
        $instance = CompetitionInstance::create([
            'competition_id' => $competition->id,
            'name' => 'FindIT 2026',
            'shortname' => 'FindIT',
            'description' => 'UGM IT Competition',
            'url' => 'https://find-it.id',
            'organizer' => 'UGM',
            'organizer_type_id' => $organizerType->id,
            'logo' => $this->manifest('local', 'ci', 'findit.png', 'private'),
            'start_date' => '2026-05-01',
            'end_date' => '2026-06-01',
            'location' => 'Yogyakarta',
        ]);
        $scale = CompetitionScale::create(['name' => 'National', 'weight' => 1.0]);
        $timeRange = CompetitionTimeRange::create(['name' => '1 Month', 'min_days' => 1, 'max_days' => 60, 'weight' => 1.0]);
        $output = CompetitionOutput::create(['name' => 'Software', 'weight' => 1.0]);
        $rank = CompetitionRank::create(['name' => '1st Place', 'weight' => 1.0]);

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
            'image' => $this->manifest('local', 'achievements/images', 'ctf.png', 'private'),
            'status' => 'ACCEPTED',
        ]);

        $blockedUserDelete = $this->actingAs($this->admin, 'api_headers')
            ->deleteJson("/v1/users/{$memberUser->id}");
        $blockedUserDelete->assertStatus(409);
        Bus::assertDispatchedTimes(DeleteBlob::class, 3); // still 3 from earlier!

        $blockingAchievement->delete();
        $okUserDelete = $this->actingAs($this->admin, 'api_headers')
            ->deleteJson("/v1/users/{$memberUser->id}");
        $okUserDelete->assertOk();
        Bus::assertDispatchedTimes(DeleteBlob::class, 4);
        Bus::assertDispatched(DeleteBlob::class, fn (DeleteBlob $job) => $job->manifest === $userCtPhoto);
    }

    private function manifest(string $disk, string $path, string $name, string $visibility): string
    {
        return json_encode([
            'disk' => $disk,
            'path' => $path,
            'name' => $name,
            'visibility' => $visibility,
        ], JSON_THROW_ON_ERROR);
    }
}
