<?php

namespace App\Jobs;

use App\Models\Group;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;

class SyncActiveInactiveMemberGroup implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $activeGroup = Group::where('name', 'Active Member')->first();
        $inactiveGroup = Group::where('name', 'Inactive Member')->first();

        if (! $activeGroup && ! $inactiveGroup) {
            return;
        }

        $today = Carbon::today()->toDateString();

        $activeUserIds = User::where('is_member', true)
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->pluck('id')
            ->unique()
            ->values()
            ->all();

        $inactiveUserIds = User::where('is_member', true)
            ->where(fn ($query) => $query
                ->whereNull('start_date')
                ->orWhereNull('end_date')
                ->orWhereDate('start_date', '>', $today)
                ->orWhereDate('end_date', '<', $today)
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
