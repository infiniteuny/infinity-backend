<?php

namespace Tests\Unit\Jobs;

use App\Jobs\CreateDocsGroup;
use App\Jobs\SyncCoreTeamDocsGroups;
use App\Jobs\UpdateDocsGroup;
use App\Models\CoreTeam;
use App\Models\Group;
use App\Models\GroupIntegration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class SyncCoreTeamDocsGroupsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispatches_create_and_update_docs_group_for_active_cohorts_with_collection_followup(): void
    {
        config([
            'services.outline.core_team_group_id' => 'ct-docs-id',
            'services.outline.xcore_team_group_id' => 'xct-docs-id',
        ]);

        Bus::fake([CreateDocsGroup::class, UpdateDocsGroup::class]);

        $newGroup = Group::create(['name' => 'Core Team 2026']);
        CoreTeam::create(['year' => 2026, 'is_active' => true, 'group_id' => $newGroup->id]);

        $staleGroup = Group::create(['name' => 'Core Team 2025']);
        CoreTeam::create(['year' => 2025, 'is_active' => true, 'group_id' => $staleGroup->id]);
        GroupIntegration::create([
            'group_id' => $staleGroup->id,
            'docs_group_id' => 'existing-docs-group',
            'docs_group_last_synced_at' => now()->subHour(),
        ]);

        (new SyncCoreTeamDocsGroups)->handle();

        Bus::assertDispatched(
            CreateDocsGroup::class,
            fn (CreateDocsGroup $job) => $job->group->is($newGroup)
                && $job->docsParentIds === ['ct-docs-id']
                && $job->docsRemoveParentIds === ['xct-docs-id']
                && $job->syncCollectionAfter === true,
        );
        Bus::assertDispatched(
            UpdateDocsGroup::class,
            fn (UpdateDocsGroup $job) => $job->group->is($staleGroup)
                && $job->syncCollectionAfter === true,
        );
    }
}
