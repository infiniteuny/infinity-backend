<?php

namespace App\Http\Resources\CommunityGroupMember;

use App\Http\Resources\Resource;
use App\Utils\ResponseFormatter;
use Illuminate\Http\Request;

class CommunityGroupResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ResponseFormatter::singleton(
            'community_group',
            $this->toBaseArray($request),
        );
    }
}
