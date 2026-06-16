<?php

namespace App\Jobs;

use App\Models\CommunityGroupAdmin;
use App\Models\Group;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncCGAdminMembersGroup implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $cgAdmins = CommunityGroupAdmin::with('members')->get();
        $activeGroup = Group::where('name', 'CG Admin')->first();
        $inactiveGroup = Group::where('name', 'Ex CG Admin')->first();

        foreach ($cgAdmins as $cgAdmin) {
            $userIds = $cgAdmin->members->pluck('id')->unique()->values()->all();
            $cgAdmin->group->users()->sync($userIds);

            if ($cgAdmin->is_active) {
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
