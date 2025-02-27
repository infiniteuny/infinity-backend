<?php

namespace App\Http\Resources\FundApplication;

use App\Http\Resources\Collection;
use App\Utils\ResponseFormatter;
use Illuminate\Http\Request;

class FundApplicationCollection extends Collection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ResponseFormatter::collection(
            'fund_applications',
            $this->collection->map->toBaseArray($request)->all(),
        );
    }
}
