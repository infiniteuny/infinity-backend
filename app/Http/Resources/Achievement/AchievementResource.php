<?php

namespace App\Http\Resources\Achievement;

use App\Http\Resources\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AchievementResource extends Resource
{
    protected $resourceName = 'achievement';

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toBaseArray(Request $request): array
    {
        $achievement = $this->resource->toArray();

        if (! Auth::guard('oidc_token')->user()) {
            $classifiedFields = [
                'team_id',
            ];

            foreach ($classifiedFields as $field) {
                if (isset($achievement[$field])) {
                    $achievement[$field] = 'REDACTED';
                }
            }
        }

        return $achievement;
    }
}
