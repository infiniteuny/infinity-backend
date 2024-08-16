<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectGallery\StoreProjectGalleryRequest;
use App\Http\Requests\ProjectGallery\UpdateProjectGalleryRequest;
use App\Jobs\DeleteBlob;
use App\Models\ProjectGallery;
use App\Repositories\StorageFacade;
use App\Utils\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class ProjectGalleryController extends Controller
{
    public function __construct(
        protected StorageFacade $storageFacade,
    ) {
        // $this->authorizeResource(ProjectGallery::class, 'project_gallery');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $projectGalleries = QueryBuilder::for(ProjectGallery::class)
            ->allowedFilters([
                'title',
                'description',
                'url',
                'image',
            ])
            ->defaultSorts([
                '-created_at',
                'id',
            ])
            ->allowedSorts([
                'id',
                'title',
                'description',
                'url',
                'image',
                'created_at',
                'updated_at',
            ])
            ->paginate($request->query('per_page', 10));

        return ResponseFormatter::paginatedCollection('project_galleries', $projectGalleries);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectGalleryRequest $request): JsonResponse
    {
        $manifest = $this->storageFacade->store($request->file('image'), 'images/project-galleries');

        $projectGallery = ProjectGallery::create(
            array_replace($request->validated(), ['image' => $manifest])
        );

        return ResponseFormatter::singleton('project_gallery', $projectGallery, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProjectGallery $projectGallery): JsonResponse
    {
        return ResponseFormatter::singleton('project_gallery', $projectGallery);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectGalleryRequest $request, ProjectGallery $projectGallery): JsonResponse
    {
        $hasImage = $request->has('image');

        if ($hasImage) {
            $encodedManifest = $projectGallery->getRawOriginal('image');

            dispatch(new DeleteBlob($encodedManifest));

            $manifest = $this->storageFacade->store($request->file('image'), 'images/project-galleries');
        }

        $projectGallery->update(
            $hasImage
                ? array_replace($request->validated(), ['image' => $manifest])
                : $request->validated()
        );

        return ResponseFormatter::singleton('project_gallery', $projectGallery);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProjectGallery $projectGallery): JsonResponse
    {
        $encodedManifest = $projectGallery->getRawOriginal('image');

        dispatch(new DeleteBlob($encodedManifest));

        $projectGallery->delete();

        return ResponseFormatter::singleton('project_gallery', $projectGallery);
    }
}
