<?php

namespace App\Http\Resources\CommunityGroupAdminMember;

use App\Http\Resources\Resource;
use App\Utils\ResponseFormatter;
use Illuminate\Http\Request;

class CommunityGroupAdminMemberResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ResponseFormatter::singleton(
            'community_group_admin_member',
            $this->toBaseArray($request),
        );
    }
}
