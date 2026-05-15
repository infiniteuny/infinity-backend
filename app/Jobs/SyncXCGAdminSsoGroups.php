<?php

namespace App\Jobs;

use App\Models\CommunityGroupAdmin;
use App\Models\Group;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncXCGAdminSsoGroups implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $admins = CommunityGroupAdmin::where('is_active', false)->get();
        $ssoParentId = config('services.authentik.xcg_admin_group_id');

        foreach ($admins as $admin) {
            $group = Group::find($admin->group_id);

            if (! $group) {
                continue;
            }

            if (is_null($group->sso_last_synced_at)) {
                CreateSsoGroup::dispatch($group, [$ssoParentId]);
            } elseif ($group->sso_last_synced_at < $group->updated_at) {
                UpdateSsoGroup::dispatch($group, [$ssoParentId]);
            }
        }
    }
}
