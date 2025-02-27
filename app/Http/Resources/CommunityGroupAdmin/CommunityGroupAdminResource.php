<?php

namespace App\Http\Resources\CommunityGroupAdmin;

use App\Http\Resources\Resource;
use App\Utils\ResponseFormatter;
use Illuminate\Http\Request;

class CommunityGroupAdminResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ResponseFormatter::singleton(
            'community_group_admin',
            $this->toBaseArray($request),
        );
    }
}
