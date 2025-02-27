<?php

namespace App\Http\Resources\FundApplication;

use App\Http\Resources\Resource;
use App\Utils\ResponseFormatter;
use Illuminate\Http\Request;

class FundApplicationResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ResponseFormatter::singleton(
            'fund_application',
            $this->toBaseArray($request),
        );
    }
}
