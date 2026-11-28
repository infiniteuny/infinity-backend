<?php

namespace App\Jobs;

use App\Models\CoreTeam;
use App\Models\Group;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncCoreTeamDocsGroups implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $coreTeams = CoreTeam::where('is_active', true)->get();
        $docsParentIds = array_values(array_filter([config('services.outline.core_team_group_id')]));
        $docsRemoveParentIds = array_values(array_filter([config('services.outline.xcore_team_group_id')]));

        foreach ($coreTeams as $coreTeam) {
            $group = Group::with('integration')->find($coreTeam->group_id);

            if (! $group) {
                continue;
            }

            $lastSyncedAt = $group->integration?->docs_group_last_synced_at;

            if (is_null($group->integration?->docs_group_id) || is_null($lastSyncedAt)) {
                CreateDocsGroup::dispatch($group, $docsParentIds, $docsRemoveParentIds, syncCollectionAfter: true);
            } elseif ($lastSyncedAt->lt($group->updated_at) || $lastSyncedAt->lt($coreTeam->updated_at)) {
                UpdateDocsGroup::dispatch($group, $docsParentIds, $docsRemoveParentIds, syncCollectionAfter: true);
            }
        }
    }
}
