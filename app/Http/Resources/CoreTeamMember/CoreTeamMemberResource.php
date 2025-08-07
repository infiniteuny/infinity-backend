<?php

namespace App\Http\Resources\CoreTeamMember;

use App\Http\Resources\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CoreTeamMemberResource extends Resource
{
    protected $resourceName = 'core_team_member';

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toBaseArray(Request $request): array
    {
        $user = $this->resource->toArray();

        if (! Auth::guard('api_token')->user()) {
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
