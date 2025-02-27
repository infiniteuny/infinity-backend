<?php

namespace App\Http\Resources\Team;

use App\Http\Resources\Collection;
use App\Utils\ResponseFormatter;
use Illuminate\Http\Request;

class TeamCollection extends Collection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ResponseFormatter::collection(
            'teams',
            $this->collection->map->toBaseArray($request)->all(),
        );
    }
}
