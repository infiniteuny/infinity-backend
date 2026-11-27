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
        $coreTeams = CoreTeam::with(['group', 'members'])->get();
        $activeGroup = Group::where('name', 'Core Team')->first();
        $inactiveGroup = Group::where('name', 'Ex Core Team')->first();

        $activeUserIds = [];
        $inactiveUserIds = [];

        foreach ($coreTeams as $coreTeam) {
            $userIds = $coreTeam->members->pluck('id')->unique()->values()->all();

            if ($coreTeam->group) {
                $coreTeam->group->users()->sync($userIds);
            }

            if ($coreTeam->is_active) {
                foreach ($userIds as $userId) {
                    $activeUserIds[$userId] = true;
                }
            } else {
                foreach ($userIds as $userId) {
                    $inactiveUserIds[$userId] = true;
                }
            }
        }

        $activeIds = array_keys($activeUserIds);
        $inactiveOnlyIds = array_values(array_diff(array_keys($inactiveUserIds), $activeIds));

        if ($activeGroup) {
            $activeGroup->users()->sync($activeIds);
        }

        if ($inactiveGroup) {
            $inactiveGroup->users()->sync($inactiveOnlyIds);
        }
    }
}
