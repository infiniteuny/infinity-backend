<?php

namespace App\Http\Resources\Achievement;

use App\Http\Resources\Resource;
use Illuminate\Http\Request;

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

        if (! $request->user()) {
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
