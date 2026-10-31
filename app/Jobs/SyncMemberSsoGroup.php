<?php

namespace App\Jobs;

use App\Models\Group;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncMemberSsoGroup implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $group = Group::where('name', 'Member')->first();
        $ssoId = config('services.authentik.member_group_id');
        $ssoParentId = config('services.authentik.internal_group_id');

        if (! $group) {
            return;
        }

        $group->sso_id = $ssoId;
        $group->save();

        if (is_null($group->sso_last_synced_at)) {
            CreateSsoGroup::dispatch($group, [$ssoParentId]);
        } elseif ($group->sso_last_synced_at < $group->updated_at) {
            UpdateSsoGroup::dispatch($group, [$ssoParentId]);
        }
    }
}
