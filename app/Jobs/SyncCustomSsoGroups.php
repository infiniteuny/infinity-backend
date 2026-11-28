<?php

namespace App\Jobs;

use App\Models\Group;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class SyncCustomSsoGroups implements ShouldBeUnique, ShouldQueue
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
            (new WithoutOverlapping('sync-custom-sso-groups'))
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
        Group::query()
            ->where('is_managed', false)
            ->where(function ($query) {
                $query->whereNull('sso_id')
                    ->orWhere('sso_id', '')
                    ->orWhereNull('sso_last_synced_at')
                    ->orWhereColumn('updated_at', '>', 'sso_last_synced_at');
            })
            ->chunkById(100, function ($groups) {
                foreach ($groups as $group) {
                    $ssoId = is_string($group->sso_id) ? trim($group->sso_id) : '';

                    if ($ssoId === '') {
                        CreateSsoGroup::dispatch($group);
                    } else {
                        UpdateSsoGroup::dispatch($group);
                    }
                }
            });
    }
}
