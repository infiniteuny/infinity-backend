<?php

namespace App\Http\Resources\Faculty;

use App\Http\Resources\Collection;
use App\Utils\ResponseFormatter;
use Illuminate\Http\Request;

class FacultyCollection extends Collection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ResponseFormatter::collection(
            'faculties',
            $this->collection->map->toBaseArray($request)->all(),
        );
    }
}
