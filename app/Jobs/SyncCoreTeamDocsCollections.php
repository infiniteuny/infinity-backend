<?php

namespace App\Jobs;

use App\Models\CoreTeam;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class SyncCoreTeamDocsCollections implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $uniqueFor = 1800;

    public int $tries = 3;

    public int $backoff = 10;

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('sync-core-team-docs-collections'))
                ->shared()
                ->releaseAfter(10)
                ->expireAfter(240),
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $coreTeams = CoreTeam::with('group.integration')->get();
        $readOnlyGroupIds = array_values(array_filter(array_map(
            fn ($id) => is_string($id) ? trim($id) : '',
            [
                config('services.outline.core_team_group_id'),
                config('services.outline.xcore_team_group_id'),
            ],
        )));

        foreach ($coreTeams as $coreTeam) {
            $group = $coreTeam->group;

            if (! $group || is_null($group->integration?->docs_group_id)) {
                continue;
            }

            $integration = $group->integration;
            $collectionName = 'Core Team '.$coreTeam->year;
            $lastSyncedAt = $integration->docs_collection_last_synced_at;

            if (is_null($integration->docs_collection_id)) {
                CreateDocsCollection::dispatch($group, $collectionName, $readOnlyGroupIds);
            } elseif (
                is_null($lastSyncedAt)
                || $lastSyncedAt->lt($coreTeam->updated_at)
                || $lastSyncedAt->lt($group->updated_at)
            ) {
                UpdateDocsCollection::dispatch($group, $collectionName, $readOnlyGroupIds);
            }
        }
    }
}
