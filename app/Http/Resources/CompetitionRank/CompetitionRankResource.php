<?php

namespace App\Http\Resources\CompetitionRank;

use App\Http\Resources\Resource;
use App\Utils\ResponseFormatter;
use Illuminate\Http\Request;

class CompetitionRankResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ResponseFormatter::singleton(
            'competition_rank',
            $this->toBaseArray($request),
        );
    }
}
