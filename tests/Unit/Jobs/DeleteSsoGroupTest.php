<?php

namespace Tests\Unit\Jobs;

use App\Jobs\CreateSsoGroup;
use App\Jobs\DeleteSsoGroup;
use App\Jobs\UpdateSsoGroup;
use App\Models\Group;
use App\Services\AuthentikService;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DeleteSsoGroupTest extends TestCase
{
    private const AUTHENTIK_URL = 'https://auth.infiniteuny.id';

    public function test_shares_per_group_lock_key_with_create_and_update_and_handles_404_and_unconfigured(): void
    {
        $group = new Group(['name' => 'Custom Group']);
        $group->id = '01950000-0000-7000-8000-000000000002';

        $createJob = new CreateSsoGroup($group);
        $updateJob = new UpdateSsoGroup($group);
        $deleteWithGroupJob = new DeleteSsoGroup('sso-grp-1', $group->id);
        $deleteFallbackJob = new DeleteSsoGroup('sso-grp-1');

        /** @var WithoutOverlapping $createLock */
        $createLock = $createJob->middleware()[0];
        /** @var WithoutOverlapping $updateLock */
        $updateLock = $updateJob->middleware()[0];
        /** @var WithoutOverlapping $deleteWithGroupLock */
        $deleteWithGroupLock = $deleteWithGroupJob->middleware()[0];
        /** @var WithoutOverlapping $deleteFallbackLock */
        $deleteFallbackLock = $deleteFallbackJob->middleware()[0];

        $this->assertSame($createLock->getLockKey($createJob), $deleteWithGroupLock->getLockKey($deleteWithGroupJob));
        $this->assertSame($updateLock->getLockKey($updateJob), $deleteWithGroupLock->getLockKey($deleteWithGroupJob));
        $this->assertSame('laravel-queue-overlap:sso-group:'.$group->id, $deleteWithGroupLock->getLockKey($deleteWithGroupJob));
        $this->assertSame('laravel-queue-overlap:sso-group-delete:sso-grp-1', $deleteFallbackLock->getLockKey($deleteFallbackJob));
        $this->assertSame(240, $deleteWithGroupLock->expiresAfter);

        $this->assertSame(900, $deleteWithGroupJob->uniqueFor);
        $this->assertSame('sso-grp-1', $deleteWithGroupJob->ssoId);
        $this->assertSame('sso-grp-1', $deleteWithGroupJob->groupSsoId);
        $this->assertSame("group:{$group->id}", $deleteWithGroupJob->uniqueId());

        Http::fake([
            self::AUTHENTIK_URL.'/api/v3/core/groups/sso-grp-1/' => Http::response(['detail' => 'Not found.'], 404),
        ]);

        config([
            'services.authentik.base_url' => self::AUTHENTIK_URL,
            'services.authentik.token' => '',
        ]);
        $deleteWithGroupJob->handle(app(AuthentikService::class));
        Http::assertNothingSent();

        config(['services.authentik.token' => 'ak_test_token']);
        $deleteWithGroupJob->handle(app(AuthentikService::class));
        Http::assertSentCount(1);
    }
}
