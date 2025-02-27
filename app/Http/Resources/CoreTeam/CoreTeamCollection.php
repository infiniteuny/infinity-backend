<?php

namespace App\Http\Resources\CoreTeam;

use App\Http\Resources\Collection;
use App\Utils\ResponseFormatter;
use Illuminate\Http\Request;

class CoreTeamCollection extends Collection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ResponseFormatter::collection(
            'core_teams',
            $this->collection->map->toBaseArray($request)->all(),
        );
    }
}
