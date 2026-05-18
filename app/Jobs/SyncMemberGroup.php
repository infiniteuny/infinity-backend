<?php

namespace App\Jobs;

use App\Models\Group;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncMemberGroup implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $group = Group::where('name', 'Member')->first();

        if (! $group) {
            return;
        }

        $userIds = User::where('is_member', true)->pluck('id')->unique()->values()->all();
        $group->users()->sync($userIds);
    }
}
