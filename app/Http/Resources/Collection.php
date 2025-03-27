<?php

namespace App\Http\Resources;

use App\Utils\ResponseFormatter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class Collection extends ResourceCollection
{
    protected $collectionName = 'resources';

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ResponseFormatter::collection(
            $this->collectionName,
            $this->collection->map->toBaseArray($request)->all(),
        );
    }

    /**
     * Customize the pagination information for the resource.
     */
    public function paginationInformation(Request $request, array $paginated, array $default): array
    {
        $default = [
            'data' => [
                'meta' => [
                    'per_page' => $paginated['per_page'],
                    'prev_cursor' => $paginated['prev_cursor'],
                    'next_cursor' => $paginated['next_cursor'],
                ],
            ],
        ];

        return $default;
    }
}
