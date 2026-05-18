<?php

namespace App\Jobs;

use App\Models\Group;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncAdminSsoGroup implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $ssoId = config('services.authentik.admin_group_id');
        $group = Group::where('name', 'Administrator')->first();

        if (! $group) {
            return;
        }

        $group->sso_id = $ssoId;
        $group->save();

        if (is_null($group->sso_last_synced_at)) {
            CreateSsoGroup::dispatch($group);
        } elseif ($group->sso_last_synced_at < $group->updated_at) {
            UpdateSsoGroup::dispatch($group);
        }
    }
}
