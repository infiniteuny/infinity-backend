<?php

namespace App\Jobs;

use App\Models\Group;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncActiveInactiveMemberSsoGroup implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $ssoParentId = config('services.authentik.member_group_id');

        $groups = [
            Group::where('name', 'Active Member')->first(),
            Group::where('name', 'Inactive Member')->first(),
        ];

        foreach ($groups as $group) {
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
