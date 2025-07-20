<?php

namespace App\Http\Resources\AchievementLeaderboard;

use App\Http\Resources\Collection;
use App\Utils\ResponseFormatter;
use Illuminate\Http\Request;

class AchievementLeaderboardYearCollection extends Collection
{
    protected $collectionName = 'achievement_leaderboard_years';

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ResponseFormatter::collection(
            $this->collectionName,
            $this->collection->map(fn ($item) => $item->year)->all(),
        );
    }

}
