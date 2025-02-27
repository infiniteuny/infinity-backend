<?php

namespace App\Http\Resources\CoreTeamMember;

use App\Http\Resources\Collection;
use App\Utils\ResponseFormatter;
use Illuminate\Http\Request;

class CoreTeamMemberCollection extends Collection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ResponseFormatter::collection(
            'core_team_members',
            $this->collection->map->toBaseArray($request)->all(),
        );
    }
}
