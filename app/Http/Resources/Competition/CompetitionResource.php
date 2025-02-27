<?php

namespace App\Http\Resources\Competition;

use App\Http\Resources\Resource;
use App\Utils\ResponseFormatter;
use Illuminate\Http\Request;

class CompetitionResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ResponseFormatter::singleton(
            'competition',
            $this->toBaseArray($request),
        );
    }
}
