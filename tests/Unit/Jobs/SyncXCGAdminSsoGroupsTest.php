<?php

namespace Tests\Unit\Jobs;

use App\Jobs\SyncXCGAdminSsoGroups;
use App\Jobs\UpdateSsoGroup;
use App\Models\CommunityGroupAdmin;
use App\Models\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class SyncXCGAdminSsoGroupsTest extends TestCase
{
    use RefreshDatabase;

    public function test_detects_mass_deactivated_cg_admin_updated_at_when_group_updated_at_is_unchanged(): void
    {
        config(['services.authentik.xcg_admin_group_id' => 'xcga-sso-uuid']);

        Bus::fake([UpdateSsoGroup::class]);

        $syncedAt = Carbon::now()->subMinute()->startOfSecond();
        $olderGroupTime = Carbon::now()->subHours(2)->startOfSecond();

        $group = Group::create(['name' => 'CG Admin 2024', 'is_managed' => true]);
        Group::withoutTimestamps(function () use ($group, $syncedAt) {
            $group->sso_id = 'sso-cga-2024';
            $group->sso_last_synced_at = $syncedAt;
            $group->saveQuietly();
        });
        $cgAdmin = CommunityGroupAdmin::create(['year' => 2024, 'is_active' => true, 'group_id' => $group->id]);
        Group::whereKey($group->id)->update(['updated_at' => $olderGroupTime]);
        CommunityGroupAdmin::whereKey($cgAdmin->id)->update([
            'is_active' => false,
            'updated_at' => Carbon::now(),
        ]);

        (new SyncXCGAdminSsoGroups)->handle();

        Bus::assertDispatched(
            UpdateSsoGroup::class,
            fn (UpdateSsoGroup $job) => $job->group->is($group) && $job->ssoParentIds === ['xcga-sso-uuid'],
        );
    }
}
