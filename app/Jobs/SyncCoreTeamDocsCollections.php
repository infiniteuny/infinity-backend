<?php

namespace App\Jobs;

use App\Models\CoreTeam;
use App\Models\Group;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncCoreTeamDocsCollections implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $coreTeams = CoreTeam::get();
        $readOnlyGroupIds = array_values(array_filter([
            config('services.outline.core_team_group_id'),
            config('services.outline.xcore_team_group_id'),
        ]));

        foreach ($coreTeams as $coreTeam) {
            $group = Group::with('integration')->find($coreTeam->group_id);

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
