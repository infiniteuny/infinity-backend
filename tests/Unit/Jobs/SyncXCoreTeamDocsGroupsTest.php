<?php

namespace Tests\Unit\Jobs;

use App\Jobs\SyncXCoreTeamDocsGroups;
use App\Jobs\UpdateDocsGroup;
use App\Models\CoreTeam;
use App\Models\Group;
use App\Models\GroupIntegration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class SyncXCoreTeamDocsGroupsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispatches_update_docs_group_for_inactive_cohorts_with_ex_parent_mapping(): void
    {
        config([
            'services.outline.core_team_group_id' => 'ct-docs-id',
            'services.outline.xcore_team_group_id' => 'xct-docs-id',
        ]);

        Bus::fake([UpdateDocsGroup::class]);

        $inactiveGroup = Group::create(['name' => 'Core Team 2024']);
        CoreTeam::create(['year' => 2024, 'is_active' => false, 'group_id' => $inactiveGroup->id]);
        GroupIntegration::create([
            'group_id' => $inactiveGroup->id,
            'docs_group_id' => 'docs-group-2024',
            'docs_group_last_synced_at' => now()->subHour(),
        ]);

        (new SyncXCoreTeamDocsGroups)->handle();

        Bus::assertDispatched(
            UpdateDocsGroup::class,
            fn (UpdateDocsGroup $job) => $job->group->is($inactiveGroup)
                && $job->docsParentIds === ['xct-docs-id']
                && $job->docsRemoveParentIds === ['ct-docs-id']
                && $job->syncCollectionAfter === true,
        );
    }
}
