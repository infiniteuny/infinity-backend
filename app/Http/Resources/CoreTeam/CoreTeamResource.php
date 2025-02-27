<?php

namespace App\Http\Resources\CoreTeam;

use App\Http\Resources\Resource;
use App\Utils\ResponseFormatter;
use Illuminate\Http\Request;

class CoreTeamResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ResponseFormatter::singleton(
            'core_team',
            $this->toBaseArray($request),
        );
    }
}
