<?php

namespace App\Http\Resources\CompetitionOrganizerType;

use App\Http\Resources\Resource;
use App\Utils\ResponseFormatter;
use Illuminate\Http\Request;

class CompetitionOrganizerTypeResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ResponseFormatter::singleton(
            'competition_organizer_type',
            $this->toBaseArray($request),
        );
    }
}
