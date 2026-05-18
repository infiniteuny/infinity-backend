<?php

namespace App\Jobs;

use App\Models\Group;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;

class SyncActiveInactiveMember implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $activeGroup = Group::where('name', 'Active Member')->first();
        $inactiveGroup = Group::where('name', 'Inactive Member')->first();

        if (! $activeGroup && ! $inactiveGroup) {
            return;
        }

        $now = Carbon::now();

        $activeUserIds = User::where('is_member', true)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->pluck('id')
            ->unique()
            ->values()
            ->all();

        $inactiveUserIds = User::where('is_member', true)
            ->where(fn ($query) => $query
                ->where('start_date', '>', $now)
                ->orWhere('end_date', '<', $now)
            )
            ->pluck('id')
            ->unique()
            ->values()
            ->all();

        if ($activeGroup) {
            $activeGroup->users()->sync($activeUserIds);
        }

        if ($inactiveGroup) {
            $inactiveGroup->users()->sync($inactiveUserIds);
        }
    }
}
