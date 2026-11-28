<?php

namespace Tests\Unit\Jobs;

use App\Jobs\SyncXCoreTeamSsoGroups;
use App\Jobs\UpdateSsoGroup;
use App\Models\CoreTeam;
use App\Models\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class SyncXCoreTeamSsoGroupsTest extends TestCase
{
    use RefreshDatabase;

    public function test_detects_mass_deactivated_core_team_updated_at_when_group_updated_at_is_unchanged(): void
    {
        config(['services.authentik.xcore_team_group_id' => 'xct-sso-uuid']);

        Bus::fake([UpdateSsoGroup::class]);

        $syncedAt = Carbon::now()->subMinute()->startOfSecond();
        $olderGroupTime = Carbon::now()->subHours(2)->startOfSecond();

        $group = Group::create(['name' => 'Core Team 2024', 'is_managed' => true]);
        Group::withoutTimestamps(function () use ($group, $syncedAt) {
            $group->sso_id = 'sso-ct-2024';
            $group->sso_last_synced_at = $syncedAt;
            $group->saveQuietly();
        });
        $coreTeam = CoreTeam::create(['year' => 2024, 'is_active' => true, 'group_id' => $group->id]);
        Group::whereKey($group->id)->update(['updated_at' => $olderGroupTime]);
        CoreTeam::whereKey($coreTeam->id)->update([
            'is_active' => false,
            'updated_at' => Carbon::now(),
        ]);

        (new SyncXCoreTeamSsoGroups)->handle();

        Bus::assertDispatched(
            UpdateSsoGroup::class,
            fn (UpdateSsoGroup $job) => $job->group->is($group) && $job->ssoParentIds === ['xct-sso-uuid'],
        );
    }
}
