<?php

namespace App\Http\Resources\CompetitionTimeRange;

use App\Http\Resources\Resource;
use App\Utils\ResponseFormatter;
use Illuminate\Http\Request;

class CompetitionTimeRangeResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ResponseFormatter::singleton(
            'competition_time_range',
            $this->toBaseArray($request),
        );
    }
}
