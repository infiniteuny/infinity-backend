<?php

namespace App\Http\Resources\AchievementLeaderboard;

use App\Http\Resources\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AchievementLeaderboardResource extends Resource
{
    protected $resourceName = 'achievement_leaderboard';

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toBaseArray(Request $request): array
    {
        $achievementLeaderboard = $this->resource->toArray();

        if (! Auth::guard(config('auth.defaults.semi_public_guard'))->user()) {
            $classifiedFields = [
                'email_address',
                'phone_number',
                'student_id',
            ];

            foreach ($classifiedFields as $field) {
                if (isset($achievementLeaderboard['user'][$field])) {
                    $achievementLeaderboard['user'][$field] = 'REDACTED';
                }
            }
        }

        return $achievementLeaderboard;
    }
}
