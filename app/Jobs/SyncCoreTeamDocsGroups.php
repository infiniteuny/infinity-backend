<?php

namespace App\Jobs;

use App\Models\CoreTeam;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class SyncCoreTeamDocsGroups implements ShouldBeUnique, ShouldQueue
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
            (new WithoutOverlapping('sync-core-team-docs-groups'))
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
        $coreTeams = CoreTeam::with('group.integration')->where('is_active', true)->get();
        $coreTeamParentId = config('services.outline.core_team_group_id');
        $xcoreTeamParentId = config('services.outline.xcore_team_group_id');
        $docsParentIds = is_string($coreTeamParentId) && trim($coreTeamParentId) !== '' ? [trim($coreTeamParentId)] : [];
        $docsRemoveParentIds = is_string($xcoreTeamParentId) && trim($xcoreTeamParentId) !== '' ? [trim($xcoreTeamParentId)] : [];

        foreach ($coreTeams as $coreTeam) {
            $group = $coreTeam->group;

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
