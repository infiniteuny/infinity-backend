<?php

namespace App\Jobs;

use App\Models\CoreTeam;
use App\Models\Group;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncCoreTeamSsoGroups implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $coreTeams = CoreTeam::where('is_active', true)->get();
        $ssoParentId = config('services.authentik.core_team_group_id');
        $ssoParentIds = is_string($ssoParentId) && trim($ssoParentId) !== '' ? [trim($ssoParentId)] : null;

        foreach ($coreTeams as $coreTeam) {
            $group = Group::find($coreTeam->group_id);

            if (! $group) {
                continue;
            }

            if (is_null($group->sso_last_synced_at)) {
                CreateSsoGroup::dispatch($group, $ssoParentIds);
            } elseif ($group->sso_last_synced_at->lt($group->updated_at) || $group->sso_last_synced_at->lt($coreTeam->updated_at)) {
                UpdateSsoGroup::dispatch($group, $ssoParentIds);
            }
        }
    }
}
