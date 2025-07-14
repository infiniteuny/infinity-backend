<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncSsoUsers implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $users = User::whereNull('sso_last_synced_at')
            ->orWhereColumn('sso_last_synced_at', '<', 'updated_at')
            ->get();

        foreach ($users as $user) {
            if (is_null($user->sso_last_synced_at)) {
                dispatch(new CreateSsoUser($user));
            } elseif ($user->sso_last_synced_at < $user->updated_at) {
                dispatch(new UpdateSsoUser($user));
            }
        }
    }
}
