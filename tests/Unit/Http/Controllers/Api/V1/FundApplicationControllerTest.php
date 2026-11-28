<?php

namespace Tests\Unit\Http\Controllers\Api\V1;

use App\Facades\Storage as BlobStorage;
use App\Jobs\DeleteBlob;
use App\Models\Competition;
use App\Models\CompetitionInstance;
use App\Models\CompetitionOrganizerType;
use App\Models\CompetitionScale;
use App\Models\CompetitionTeamType;
use App\Models\Degree;
use App\Models\Faculty;
use App\Models\FundApplication;
use App\Models\Major;
use App\Models\Permission;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage as LaravelStorage;
use RuntimeException;
use Tests\TestCase;

class FundApplicationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_multi_blob_update_failure_on_second_upload_rolls_back_first_upload_and_keeps_live_blobs(): void
    {
        LaravelStorage::fake('local');
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
        $admin->givePermissionTo(Permission::create(['name' => 'update-fund-application', 'guard_name' => 'api']));

        $teamType = CompetitionTeamType::create(['name' => 'Team', 'weight' => 1]);
        $team = Team::create([
            'name' => 'Team Beta',
            'leader_id' => $admin->id,
            'team_type_id' => $teamType->id,
        ]);
        $competition = Competition::create(['name' => 'CompFest']);
        $organizerType = CompetitionOrganizerType::create(['name' => 'University', 'weight' => 1]);
        $instance = CompetitionInstance::create([
            'competition_id' => $competition->id,
            'name' => 'CompFest 18',
            'shortname' => 'CF18',
            'description' => 'IT Competition',
            'url' => 'https://compfest.id',
            'organizer' => 'UI',
            'organizer_type_id' => $organizerType->id,
            'logo' => '{"disk":"local","path":"ci","name":"cf.png","visibility":"private"}',
            'start_date' => '2026-07-01',
            'end_date' => '2026-08-01',
            'location' => 'Depok',
        ]);
        $scale = CompetitionScale::create(['name' => 'National', 'weight' => 1]);

        $oldLoa = '{"disk":"local","path":"fund-applications/letter-of-acceptances","name":"old-loa.pdf","visibility":"private"}';
        $oldProposal = '{"disk":"local","path":"fund-applications/proposals","name":"old-proposal.doc","visibility":"private"}';

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

        $newLoaManifest = '{"disk":"local","path":"fund-applications/letter-of-acceptances","name":"new-loa.pdf","visibility":"private"}';
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

        $this->actingAs($admin, 'api_headers')
            ->put("/v1/fund-applications/{$fundApp->id}", [
                'letter_of_acceptance' => $fakePdf,
                'proposal' => $fakeDoc,
            ], ['Accept' => 'application/json'])
            ->assertStatus(500);

        $fundApp->refresh();
        $this->assertSame($oldLoa, $fundApp->getRawOriginal('letter_of_acceptance'));
        $this->assertSame($oldProposal, $fundApp->getRawOriginal('proposal'));

        Bus::assertDispatchedTimes(DeleteBlob::class, 1);
        Bus::assertDispatched(DeleteBlob::class, fn (DeleteBlob $job) => $job->manifest === $newLoaManifest);
    }
}
