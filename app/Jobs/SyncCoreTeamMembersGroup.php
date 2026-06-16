<?php

namespace App\Jobs;

use App\Models\CoreTeam;
use App\Models\Group;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncCoreTeamMembersGroup implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $coreTeams = CoreTeam::with('members')->get();
        $activeGroup = Group::where('name', 'Core Team')->first();
        $inactiveGroup = Group::where('name', 'Ex Core Team')->first();

        foreach ($coreTeams as $coreTeam) {
            $userIds = $coreTeam->members->pluck('id')->unique()->values()->all();
            $coreTeam->group->users()->sync($userIds);

            if ($coreTeam->is_active) {
                if ($activeGroup) {
                    $activeGroup->assignToModels($userIds);
                }

                if ($inactiveGroup) {
                    $inactiveGroup->removeFromModels($userIds);
                }
            } else {
                if ($activeGroup) {
                    $activeGroup->removeFromModels($userIds);
                }

                if ($inactiveGroup) {
                    $inactiveGroup->assignToModels($userIds);
                }
            }
        }
    }
}
