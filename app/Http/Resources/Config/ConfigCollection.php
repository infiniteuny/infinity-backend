<?php

namespace App\Http\Resources\Config;

use App\Http\Resources\Collection;
use App\Utils\ResponseFormatter;
use Illuminate\Http\Request;

class ConfigCollection extends Collection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ResponseFormatter::collection(
            'configs',
            $this->collection->map->toBaseArray($request)->all(),
        );
    }
}
