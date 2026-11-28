<?php

namespace Tests\Unit\Jobs;

use App\Jobs\CreateDocsCollection;
use App\Jobs\SyncCoreTeamDocsCollections;
use App\Jobs\UpdateDocsCollection;
use App\Models\CoreTeam;
use App\Models\Group;
use App\Models\GroupIntegration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class SyncCoreTeamDocsCollectionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_branches_on_docs_collection_id_rather_than_null_watermark(): void
    {
        Bus::fake([CreateDocsCollection::class, UpdateDocsCollection::class]);

        $newColGroup = Group::create(['name' => 'Core Team 2026']);
        CoreTeam::create(['year' => 2026, 'is_active' => true, 'group_id' => $newColGroup->id]);
        GroupIntegration::create([
            'group_id' => $newColGroup->id,
            'docs_group_id' => 'docs-group-2026',
            'docs_collection_id' => null,
            'docs_collection_last_synced_at' => null,
        ]);

        $recreatedGroup = Group::create(['name' => 'Core Team 2025']);
        CoreTeam::create(['year' => 2025, 'is_active' => false, 'group_id' => $recreatedGroup->id]);
        GroupIntegration::create([
            'group_id' => $recreatedGroup->id,
            'docs_group_id' => 'recreated-docs-group-2025',
            'docs_collection_id' => 'existing-collection-2025',
            'docs_collection_last_synced_at' => null,
        ]);

        (new SyncCoreTeamDocsCollections)->handle();

        Bus::assertDispatched(
            CreateDocsCollection::class,
            fn (CreateDocsCollection $job) => $job->group->is($newColGroup),
        );
        Bus::assertDispatched(
            UpdateDocsCollection::class,
            fn (UpdateDocsCollection $job) => $job->group->is($recreatedGroup),
        );
    }
}
