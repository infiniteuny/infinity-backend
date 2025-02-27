<?php

namespace App\Http\Resources\TeamMember;

use App\Http\Resources\Collection;
use App\Utils\ResponseFormatter;
use Illuminate\Http\Request;

class TeamMemberCollection extends Collection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ResponseFormatter::collection(
            'team_members',
            $this->collection->map->toBaseArray($request)->all(),
        );
    }
}
