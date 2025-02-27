<?php

namespace App\Http\Resources\CompetitionTeamType;

use App\Http\Resources\Resource;
use App\Utils\ResponseFormatter;
use Illuminate\Http\Request;

class CompetitionTeamTypeResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ResponseFormatter::singleton(
            'competition_team_type',
            $this->toBaseArray($request),
        );
    }
}
