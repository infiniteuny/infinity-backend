<?php

namespace Tests\Unit\Jobs;

use App\Jobs\CreateSsoUser;
use App\Jobs\DeleteSsoUser;
use App\Jobs\UpdateSsoUser;
use App\Models\User;
use App\Services\AuthentikService;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DeleteSsoUserTest extends TestCase
{
    private const AUTHENTIK_URL = 'https://auth.infiniteuny.id';

    public function test_shares_per_user_lock_key_with_create_and_update_and_handles_404_and_unconfigured(): void
    {
        $user = new User;
        $user->id = '01950000-0000-7000-8000-000000000001';

        $createJob = new CreateSsoUser($user);
        $updateJob = new UpdateSsoUser($user);
        $deleteWithUserJob = new DeleteSsoUser('42', $user->id);
        $deleteFallbackJob = new DeleteSsoUser('42');

        /** @var WithoutOverlapping $createLock */
        $createLock = $createJob->middleware()[0];
        /** @var WithoutOverlapping $updateLock */
        $updateLock = $updateJob->middleware()[0];
        /** @var WithoutOverlapping $deleteWithUserLock */
        $deleteWithUserLock = $deleteWithUserJob->middleware()[0];
        /** @var WithoutOverlapping $deleteFallbackLock */
        $deleteFallbackLock = $deleteFallbackJob->middleware()[0];

        $this->assertInstanceOf(WithoutOverlapping::class, $deleteWithUserLock);
        $this->assertSame($createLock->getLockKey($createJob), $deleteWithUserLock->getLockKey($deleteWithUserJob));
        $this->assertSame($updateLock->getLockKey($updateJob), $deleteWithUserLock->getLockKey($deleteWithUserJob));
        $this->assertSame('laravel-queue-overlap:sso-user:'.$user->id, $deleteWithUserLock->getLockKey($deleteWithUserJob));
        $this->assertSame('laravel-queue-overlap:sso-user-delete:42', $deleteFallbackLock->getLockKey($deleteFallbackJob));
        $this->assertSame(240, $deleteWithUserLock->expiresAfter);

        $job = new DeleteSsoUser('42', $user->id);
        $this->assertSame(900, $job->uniqueFor);
        $this->assertSame('42', $job->ssoId);
        $this->assertSame('42', $job->userSsoId);
        $this->assertSame("user:{$user->id}", $job->uniqueId());

        // Unconfigured token skips HTTP request; configured token tolerates 404 on delete
        Http::fake([
            self::AUTHENTIK_URL.'/api/v3/core/users/42/' => Http::response(['detail' => 'Not found.'], 404),
        ]);

        config([
            'services.authentik.base_url' => self::AUTHENTIK_URL,
            'services.authentik.token' => '',
        ]);
        $job->handle(app(AuthentikService::class));
        Http::assertNothingSent();

        config(['services.authentik.token' => 'ak_test_token']);
        $job->handle(app(AuthentikService::class));
        Http::assertSentCount(1);
    }
}
