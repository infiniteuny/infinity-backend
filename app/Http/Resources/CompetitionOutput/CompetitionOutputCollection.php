<?php

namespace App\Http\Resources\CompetitionOutput;

use App\Http\Resources\Collection;
use App\Utils\ResponseFormatter;
use Illuminate\Http\Request;

class CompetitionOutputCollection extends Collection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ResponseFormatter::collection(
            'competition_outputs',
            $this->collection->map->toBaseArray($request)->all(),
        );
    }
}
