<?php

namespace Tests\Unit\Jobs;

use App\Jobs\SyncCoreTeamSsoGroups;
use App\Jobs\UpdateSsoGroup;
use App\Models\CoreTeam;
use App\Models\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class SyncCoreTeamSsoGroupsTest extends TestCase
{
    use RefreshDatabase;

    public function test_checks_cohort_updated_at_and_normalizes_empty_parent_id_to_null(): void
    {
        config(['services.authentik.core_team_group_id' => null]);

        Bus::fake([UpdateSsoGroup::class]);

        $syncedAt = Carbon::now()->subMinute()->startOfSecond();
        $olderGroupTime = Carbon::now()->subHours(2)->startOfSecond();

        $group = Group::create(['name' => 'Core Team 2026', 'is_managed' => true]);
        Group::withoutTimestamps(function () use ($group, $syncedAt) {
            $group->sso_id = 'sso-ct-2026';
            $group->sso_last_synced_at = $syncedAt;
            $group->saveQuietly();
        });
        $coreTeam = CoreTeam::create(['year' => 2026, 'is_active' => true, 'group_id' => $group->id]);
        Group::whereKey($group->id)->update(['updated_at' => $olderGroupTime]);
        CoreTeam::whereKey($coreTeam->id)->update(['updated_at' => Carbon::now()]);

        (new SyncCoreTeamSsoGroups)->handle();

        Bus::assertDispatched(
            UpdateSsoGroup::class,
            fn (UpdateSsoGroup $job) => $job->group->is($group) && $job->ssoParentIds === null,
        );
    }
}
