<?php

namespace App\Http\Resources\ProjectGallery;

use App\Http\Resources\Resource;
use App\Utils\ResponseFormatter;
use Illuminate\Http\Request;

class ProjectGalleryResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ResponseFormatter::singleton(
            'project_gallery',
            $this->toBaseArray($request),
        );
    }
}
