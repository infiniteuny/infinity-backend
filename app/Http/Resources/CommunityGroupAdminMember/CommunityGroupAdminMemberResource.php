<?php

namespace App\Http\Resources\CommunityGroupAdminMember;

use App\Http\Resources\Resource;
use Illuminate\Http\Request;

class CommunityGroupAdminMemberResource extends Resource
{
    protected $resourceName = 'community_group_admin_member';

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toBaseArray(Request $request): array
    {
        $user = $this->resource->toArray();

        if (! $request->user()) {
            $classifiedFields = [
                'email_address',
                'phone_number',
                'student_id',
            ];

            foreach ($classifiedFields as $field) {
                if (isset($user[$field])) {
                    $user[$field] = 'REDACTED';
                }
            }
        }

        return $user;
    }
}
