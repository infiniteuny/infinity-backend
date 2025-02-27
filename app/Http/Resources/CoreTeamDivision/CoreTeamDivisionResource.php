<?php

namespace App\Http\Resources\CoreTeamDivision;

use App\Http\Resources\Resource;
use App\Utils\ResponseFormatter;
use Illuminate\Http\Request;

class CoreTeamDivisionResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ResponseFormatter::singleton(
            'core_team_division',
            $this->toBaseArray($request),
        );
    }
}
