<?php

namespace App\Http\Resources\Achievement;

use App\Http\Resources\Collection;
use App\Utils\ResponseFormatter;
use Illuminate\Http\Request;

class AchievementCollection extends Collection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ResponseFormatter::collection(
            'achievements',
            $this->collection->map->toBaseArray($request)->all(),
        );
    }
}
