<?php

namespace App\Jobs;

use App\Models\CommunityGroupAdmin;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class SyncCGAdminSsoGroups implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $uniqueFor = 1800;

    public int $tries = 3;

    public int $backoff = 10;

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('sync-cg-admin-sso-groups'))
                ->shared()
                ->releaseAfter(10)
                ->expireAfter(240),
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $admins = CommunityGroupAdmin::with('group')->where('is_active', true)->get();
        $ssoParentId = config('services.authentik.cg_admin_group_id');
        $ssoParentIds = is_string($ssoParentId) && trim($ssoParentId) !== '' ? [trim($ssoParentId)] : null;

        foreach ($admins as $admin) {
            $group = $admin->group;

            if (! $group) {
                continue;
            }

            if (is_null($group->sso_last_synced_at)) {
                CreateSsoGroup::dispatch($group, $ssoParentIds);
            } elseif ($group->sso_last_synced_at->lt($group->updated_at) || $group->sso_last_synced_at->lt($admin->updated_at)) {
                UpdateSsoGroup::dispatch($group, $ssoParentIds);
            }
        }
    }
}
