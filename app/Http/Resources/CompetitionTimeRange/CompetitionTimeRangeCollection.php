<?php

namespace App\Http\Resources\CompetitionTimeRange;

use App\Http\Resources\Collection;
use App\Utils\ResponseFormatter;
use Illuminate\Http\Request;

class CompetitionTimeRangeCollection extends Collection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ResponseFormatter::collection(
            'competition_time_ranges',
            $this->collection->map->toBaseArray($request)->all(),
        );
    }
}
