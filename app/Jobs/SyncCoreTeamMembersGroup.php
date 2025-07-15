<?php

namespace App\Jobs;

use App\Models\CoreTeam;
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

        foreach ($coreTeams as $coreTeam) {
            $userIds = $coreTeam->members->pluck('id')->unique()->values()->all();
            $coreTeam->group->users()->sync($userIds);
        }
    }
}
