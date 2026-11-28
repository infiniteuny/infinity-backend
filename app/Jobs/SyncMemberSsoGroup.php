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
        $ssoId = is_string($ssoId) ? trim($ssoId) : '';
        $ssoParentId = config('services.authentik.internal_group_id');
        $ssoParentIds = is_string($ssoParentId) && trim($ssoParentId) !== '' ? [trim($ssoParentId)] : null;

        if (! $group) {
            return;
        }

        if ($ssoId !== '' && $group->sso_id !== $ssoId) {
            $group->sso_id = $ssoId;
            $group->sso_last_synced_at = null;
            Group::withoutTimestamps(fn () => $group->saveQuietly());
        }

        if (is_null($group->sso_last_synced_at)) {
            CreateSsoGroup::dispatch($group, $ssoParentIds);
        } elseif ($group->sso_last_synced_at->lt($group->updated_at)) {
            UpdateSsoGroup::dispatch($group, $ssoParentIds);
        }
    }
}
