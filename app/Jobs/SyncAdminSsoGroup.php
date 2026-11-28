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
        $ssoId = is_string($ssoId) ? trim($ssoId) : '';
        $group = Group::where('name', 'Administrator')->first();

        if (! $group) {
            return;
        }

        if ($ssoId !== '' && $group->sso_id !== $ssoId) {
            $group->sso_id = $ssoId;
            $group->sso_last_synced_at = null;
            Group::withoutTimestamps(fn () => $group->saveQuietly());
        }

        if (is_null($group->sso_last_synced_at)) {
            CreateSsoGroup::dispatch($group, isSuperuser: true);
        } elseif ($group->sso_last_synced_at->lt($group->updated_at)) {
            UpdateSsoGroup::dispatch($group, isSuperuser: true);
        }
    }
}
