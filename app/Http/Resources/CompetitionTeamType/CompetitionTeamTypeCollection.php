<?php

namespace App\Http\Resources\CompetitionTeamType;

use App\Http\Resources\Collection;
use App\Utils\ResponseFormatter;
use Illuminate\Http\Request;

class CompetitionTeamTypeCollection extends Collection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ResponseFormatter::collection(
            'competition_team_types',
            $this->collection->map->toBaseArray($request)->all(),
        );
    }
}
