<?php

namespace App\Jobs;

use App\Models\CompetitionTeamType;
use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\DB;

class SyncIndividualTeams implements ShouldBeUnique, ShouldQueue
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
            (new WithoutOverlapping('sync-individual-teams'))
                ->shared()
                ->dontRelease()
                ->expireAfter(240),
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $individualTeamTypeId = config('app.individual_team_type_id');

        if (! $individualTeamTypeId || ! CompetitionTeamType::whereKey($individualTeamTypeId)->exists()) {
            return;
        }

        User::query()
            ->where('is_member', true)
            ->select(['id', 'name', 'is_member'])
            ->chunkById(200, function ($users) use ($individualTeamTypeId) {
                $userIds = $users->pluck('id')->all();

                $existingTeamsByLeader = Team::with('members:id')
                    ->where('team_type_id', $individualTeamTypeId)
                    ->whereIn('leader_id', $userIds)
                    ->get()
                    ->groupBy('leader_id');

                foreach ($users as $user) {
                    $teamsForUser = $existingTeamsByLeader->get($user->id);

                    if ($teamsForUser !== null && $teamsForUser->isNotEmpty()) {
                        $team = $teamsForUser->first();
                        $memberIds = $team->members->pluck('id')->all();

                        if (
                            $team->name === $user->name
                            && $team->leader_id === $user->id
                            && $memberIds === [$user->id]
                        ) {
                            continue;
                        }
                    }

                    $this->syncUserIndividualTeam($user->id, $individualTeamTypeId);
                }
            });
    }

    /**
     * Ensure the given member has an individual team matching their details and membership.
     */
    protected function syncUserIndividualTeam(string $userId, string $individualTeamTypeId): void
    {
        DB::transaction(function () use ($userId, $individualTeamTypeId) {
            $user = User::whereKey($userId)->lockForUpdate()->first();

            if (! $user || ! $user->is_member) {
                return;
            }

            $team = Team::where('team_type_id', $individualTeamTypeId)
                ->where('leader_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (! $team) {
                $team = Team::create([
                    'leader_id' => $user->id,
                    'team_type_id' => $individualTeamTypeId,
                    'name' => $user->name,
                ]);

                $team->members()->sync([$user->id]);

                return;
            }

            $attributes = [];

            if ($team->name !== $user->name) {
                $attributes['name'] = $user->name;
            }

            if ($team->leader_id !== $user->id) {
                $attributes['leader_id'] = $user->id;
            }

            if ($attributes !== []) {
                $team->update($attributes);
            }

            $memberIds = $team->members()->pluck('users.id')->all();

            if ($memberIds !== [$user->id]) {
                $team->members()->sync([$user->id]);
            }
        });
    }
}
