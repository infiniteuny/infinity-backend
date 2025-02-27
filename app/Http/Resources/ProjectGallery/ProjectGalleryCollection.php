<?php

namespace App\Http\Resources\ProjectGallery;

use App\Http\Resources\Collection;
use App\Utils\ResponseFormatter;
use Illuminate\Http\Request;

class ProjectGalleryCollection extends Collection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ResponseFormatter::collection(
            'project_galleries',
            $this->collection->map->toBaseArray($request)->all(),
        );
    }
}
