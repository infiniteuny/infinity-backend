<?php

namespace App\Http\Resources\CoreTeamDivision;

use App\Http\Resources\Collection;
use App\Utils\ResponseFormatter;
use Illuminate\Http\Request;

class CoreTeamDivisionCollection extends Collection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ResponseFormatter::collection(
            'core_team_divisions',
            $this->collection->map->toBaseArray($request)->all(),
        );
    }
}
