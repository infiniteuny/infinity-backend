<?php

namespace App\Http\Resources\Degree;

use App\Http\Resources\Collection;
use App\Utils\ResponseFormatter;
use Illuminate\Http\Request;

class DegreeCollection extends Collection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ResponseFormatter::collection(
            'degrees',
            $this->collection->map->toBaseArray($request)->all(),
        );
    }
}
