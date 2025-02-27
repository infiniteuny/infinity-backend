<?php

namespace App\Http\Resources\CommunityGroup;

use App\Http\Resources\Collection;
use App\Utils\ResponseFormatter;
use Illuminate\Http\Request;

class CommunityGroupCollection extends Collection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ResponseFormatter::collection(
            'community_groups',
            $this->collection->map->toBaseArray($request)->all(),
        );
    }
}
