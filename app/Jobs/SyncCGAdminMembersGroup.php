<?php

namespace App\Jobs;

use App\Models\CommunityGroupAdmin;
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

        foreach ($cgAdmins as $cgAdmin) {
            $userIds = $cgAdmin->members->pluck('id')->unique()->values()->all();
            $cgAdmin->group->users()->sync($userIds);
        }
    }
}
