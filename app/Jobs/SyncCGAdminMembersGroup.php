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
        $cgAdmins = CommunityGroupAdmin::with(['group', 'members'])->get();
        $activeGroup = Group::where('name', 'CG Admin')->first();
        $inactiveGroup = Group::where('name', 'Ex CG Admin')->first();

        $activeUserIds = [];
        $inactiveUserIds = [];

        foreach ($cgAdmins as $cgAdmin) {
            $userIds = $cgAdmin->members->pluck('id')->unique()->values()->all();

            if ($cgAdmin->group) {
                $cgAdmin->group->users()->sync($userIds);
            }

            if ($cgAdmin->is_active) {
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
