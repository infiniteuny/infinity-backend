<?php

namespace Tests\Unit\Jobs;

use App\Jobs\CreateSsoUser;
use App\Jobs\UpdateSsoUser;
use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Group;
use App\Models\Major;
use App\Models\User;
use App\Services\AuthentikService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UpdateSsoUserTest extends TestCase
{
    use RefreshDatabase;

    private const AUTHENTIK_URL = 'https://auth.infiniteuny.id';

    public function test_delegates_null_sso_id_recovers_on_404_and_saves_quietly_without_touching_groups_when_unchanged(): void
    {
        config([
            'services.authentik.base_url' => self::AUTHENTIK_URL,
            'services.authentik.token' => 'ak_test_token',
        ]);

        $degree = Degree::create(['code' => 'S1', 'name' => 'Sarjana']);
        $faculty = Faculty::create(['code' => 'FT', 'name' => 'Teknik']);
        $major = Major::create([
            'code' => 'PTI',
            'name' => 'Pendidikan Teknik Informatika',
            'degree_id' => $degree->id,
            'faculty_id' => $faculty->id,
        ]);

        $unsynced = User::create([
            'name' => 'Unsynced',
            'username' => 'unsynced',
            'email_address' => 'unsynced@infiniteuny.id',
            'phone_number' => '+6281111111101',
            'student_id' => '24520241001',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);

        Bus::fake([CreateSsoUser::class]);
        (new UpdateSsoUser($unsynced))->handle(app(AuthentikService::class));
        Bus::assertDispatched(CreateSsoUser::class, fn (CreateSsoUser $job) => $job->user->is($unsynced));

        $synced = User::create([
            'name' => 'Synced',
            'username' => 'synced',
            'email_address' => 'synced@infiniteuny.id',
            'phone_number' => '+6281111111102',
            'student_id' => '24520241002',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);
        $group = Group::create(['name' => 'Member']);
        $group->users()->attach($synced->id);

        $pastTime = Carbon::now()->subHour()->startOfSecond();
        User::withoutTimestamps(function () use ($synced) {
            $synced->sso_id = '42';
            $synced->saveQuietly();
        });
        User::whereKey($synced->id)->update(['updated_at' => $pastTime]);
        Group::whereKey($group->id)->update(['updated_at' => $pastTime]);
        $synced->refresh();

        Http::fake([
            self::AUTHENTIK_URL.'/api/v3/core/users/42/' => Http::response([
                'pk' => 42,
                'username' => 'synced',
                'name' => 'Synced',
                'email' => 'synced@infiniteuny.id',
                'is_active' => true,
            ]),
        ]);

        (new UpdateSsoUser($synced))->handle(app(AuthentikService::class));

        $this->assertTrue($synced->fresh()->updated_at->equalTo($pastTime));
        $this->assertTrue($group->fresh()->updated_at->equalTo($pastTime));

        // Post-lock deleted user (e.g. queued behind DeleteSsoUser) exits instead of 404-resurrecting in Authentik
        $queuedUpdate = new UpdateSsoUser($synced);
        $group->users()->detach();
        $synced->delete();

        Http::fake();
        $queuedUpdate->handle(app(AuthentikService::class));
        Http::assertNothingSent();
    }
}
