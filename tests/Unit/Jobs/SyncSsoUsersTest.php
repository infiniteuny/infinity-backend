<?php

namespace Tests\Unit\Jobs;

use App\Jobs\CreateSsoUser;
use App\Jobs\SyncSsoUsers;
use App\Jobs\UpdateSsoUser;
use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class SyncSsoUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispatches_create_when_sso_id_or_watermark_missing_and_update_when_stale(): void
    {
        Bus::fake([CreateSsoUser::class, UpdateSsoUser::class]);

        $degree = Degree::create(['code' => 'S1', 'name' => 'Sarjana']);
        $faculty = Faculty::create(['code' => 'FT', 'name' => 'Teknik']);
        $major = Major::create([
            'code' => 'PTI',
            'name' => 'Pendidikan Teknik Informatika',
            'degree_id' => $degree->id,
            'faculty_id' => $faculty->id,
        ]);

        $newUser = User::create([
            'name' => 'New',
            'username' => 'newuser',
            'email_address' => 'new@infiniteuny.id',
            'phone_number' => '+6281111111101',
            'student_id' => '24520241001',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);

        $staleUser = User::create([
            'name' => 'Stale',
            'username' => 'staleuser',
            'email_address' => 'stale@infiniteuny.id',
            'phone_number' => '+6281111111102',
            'student_id' => '24520241002',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);
        User::withoutTimestamps(function () use ($staleUser) {
            $staleUser->sso_id = '42';
            $staleUser->sso_last_synced_at = now()->subHour();
            $staleUser->saveQuietly();
        });

        (new SyncSsoUsers)->handle();

        Bus::assertDispatched(CreateSsoUser::class, fn (CreateSsoUser $job) => $job->user->is($newUser));
        Bus::assertDispatched(UpdateSsoUser::class, fn (UpdateSsoUser $job) => $job->user->is($staleUser));
    }
}
