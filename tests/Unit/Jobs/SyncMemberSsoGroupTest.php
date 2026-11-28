<?php

namespace Tests\Unit\Jobs;

use App\Jobs\CreateSsoGroup;
use App\Jobs\SyncMemberSsoGroup;
use App\Models\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class SyncMemberSsoGroupTest extends TestCase
{
    use RefreshDatabase;

    public function test_rebinds_changed_member_sso_id_quietly_and_passes_internal_parent(): void
    {
        config([
            'services.authentik.member_group_id' => 'new-member-sso-uuid',
            'services.authentik.internal_group_id' => 'internal-parent-uuid',
        ]);

        $pastTime = Carbon::now()->subHours(2)->startOfSecond();
        $memberGroup = Group::create(['name' => 'Member', 'is_managed' => true]);
        Group::withoutTimestamps(function () use ($memberGroup) {
            $memberGroup->sso_id = 'old-member-sso-uuid';
            $memberGroup->sso_last_synced_at = now()->subMinute();
            $memberGroup->saveQuietly();
        });
        Group::whereKey($memberGroup->id)->update(['updated_at' => $pastTime]);

        Bus::fake([CreateSsoGroup::class]);

        (new SyncMemberSsoGroup)->handle();

        $memberGroup->refresh();
        $this->assertSame('new-member-sso-uuid', $memberGroup->sso_id);
        $this->assertNull($memberGroup->sso_last_synced_at);
        $this->assertTrue($memberGroup->updated_at->equalTo($pastTime));
        Bus::assertDispatched(
            CreateSsoGroup::class,
            fn (CreateSsoGroup $job) => $job->group->is($memberGroup) && $job->ssoParentIds === ['internal-parent-uuid'],
        );
    }
}
