<?php

namespace App\Http\Resources\CompetitionOutput;

use App\Http\Resources\Resource;
use App\Utils\ResponseFormatter;
use Illuminate\Http\Request;

class CompetitionOutputResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ResponseFormatter::singleton(
            'competition_output',
            $this->toBaseArray($request),
        );
    }
}
