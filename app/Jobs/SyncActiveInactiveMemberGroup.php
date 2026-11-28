<?php

namespace App\Jobs;

use App\Models\Group;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Carbon;

class SyncActiveInactiveMemberGroup implements ShouldBeUnique, ShouldQueue
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
            (new WithoutOverlapping('sync-active-inactive-member-group'))
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
