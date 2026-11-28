<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class SyncSsoUsers implements ShouldBeUnique, ShouldQueue
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
            (new WithoutOverlapping('sync-sso-users'))
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
        User::query()
            ->where(function ($query) {
                $query->whereNull('sso_id')
                    ->orWhere('sso_id', '')
                    ->orWhereNull('sso_last_synced_at')
                    ->orWhereColumn('sso_last_synced_at', '<', 'updated_at');
            })
            ->chunkById(200, function ($users) {
                foreach ($users as $user) {
                    $ssoId = is_scalar($user->sso_id) ? trim((string) $user->sso_id) : '';

                    if ($ssoId === '' || is_null($user->sso_last_synced_at)) {
                        CreateSsoUser::dispatch($user);
                    } elseif ($user->sso_last_synced_at->lt($user->updated_at)) {
                        UpdateSsoUser::dispatch($user);
                    }
                }
            });
    }
}
