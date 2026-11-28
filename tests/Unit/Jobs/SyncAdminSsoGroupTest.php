<?php

namespace Tests\Unit\Jobs;

use App\Jobs\CreateSsoGroup;
use App\Jobs\SyncAdminSsoGroup;
use App\Models\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class SyncAdminSsoGroupTest extends TestCase
{
    use RefreshDatabase;

    public function test_rebinds_changed_configured_sso_id_quietly_resets_watermark_and_dispatches_create(): void
    {
        config(['services.authentik.admin_group_id' => 'new-admin-sso-uuid']);

        $pastTime = Carbon::now()->subHours(2)->startOfSecond();
        $adminGroup = Group::create(['name' => 'Administrator', 'is_managed' => true]);
        Group::withoutTimestamps(function () use ($adminGroup) {
            $adminGroup->sso_id = 'old-admin-sso-uuid';
            $adminGroup->sso_last_synced_at = now()->subMinute();
            $adminGroup->saveQuietly();
        });
        Group::whereKey($adminGroup->id)->update(['updated_at' => $pastTime]);

        Bus::fake([CreateSsoGroup::class]);

        (new SyncAdminSsoGroup)->handle();

        $adminGroup->refresh();
        $this->assertSame('new-admin-sso-uuid', $adminGroup->sso_id);
        $this->assertNull($adminGroup->sso_last_synced_at);
        $this->assertTrue($adminGroup->updated_at->equalTo($pastTime));
        Bus::assertDispatched(
            CreateSsoGroup::class,
            fn (CreateSsoGroup $job) => $job->group->is($adminGroup) && $job->isSuperuser === true,
        );
    }
}
