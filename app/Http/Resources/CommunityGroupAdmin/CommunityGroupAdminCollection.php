<?php

namespace App\Http\Resources\CommunityGroupAdmin;

use App\Http\Resources\Collection;
use App\Utils\ResponseFormatter;
use Illuminate\Http\Request;

class CommunityGroupAdminCollection extends Collection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ResponseFormatter::collection(
            'community_group_admins',
            $this->collection->map->toBaseArray($request)->all(),
        );
    }
}
