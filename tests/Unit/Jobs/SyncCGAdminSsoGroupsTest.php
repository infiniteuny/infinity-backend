<?php

namespace Tests\Unit\Jobs;

use App\Jobs\SyncCGAdminSsoGroups;
use App\Jobs\UpdateSsoGroup;
use App\Models\CommunityGroupAdmin;
use App\Models\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class SyncCGAdminSsoGroupsTest extends TestCase
{
    use RefreshDatabase;

    public function test_checks_cg_admin_updated_at_and_normalizes_unset_parent_to_null(): void
    {
        config(['services.authentik.cg_admin_group_id' => '']);

        Bus::fake([UpdateSsoGroup::class]);

        $syncedAt = Carbon::now()->subMinute()->startOfSecond();
        $olderGroupTime = Carbon::now()->subHours(2)->startOfSecond();

        $group = Group::create(['name' => 'CG Admin 2026', 'is_managed' => true]);
        Group::withoutTimestamps(function () use ($group, $syncedAt) {
            $group->sso_id = 'sso-cga-2026';
            $group->sso_last_synced_at = $syncedAt;
            $group->saveQuietly();
        });
        $cgAdmin = CommunityGroupAdmin::create(['year' => 2026, 'is_active' => true, 'group_id' => $group->id]);
        Group::whereKey($group->id)->update(['updated_at' => $olderGroupTime]);
        CommunityGroupAdmin::whereKey($cgAdmin->id)->update(['updated_at' => Carbon::now()]);

        (new SyncCGAdminSsoGroups)->handle();

        Bus::assertDispatched(
            UpdateSsoGroup::class,
            fn (UpdateSsoGroup $job) => $job->group->is($group) && $job->ssoParentIds === null,
        );
    }
}
