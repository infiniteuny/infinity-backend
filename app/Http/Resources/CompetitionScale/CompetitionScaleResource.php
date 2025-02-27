<?php

namespace App\Http\Resources\CompetitionScale;

use App\Http\Resources\Resource;
use App\Utils\ResponseFormatter;
use Illuminate\Http\Request;

class CompetitionScaleResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ResponseFormatter::singleton(
            'competition_scale',
            $this->toBaseArray($request),
        );
    }
}
