<?php

namespace App\Http\Controllers\Api\V1;

use App\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectGallery\StoreProjectGalleryRequest;
use App\Http\Requests\ProjectGallery\UpdateProjectGalleryRequest;
use App\Http\Resources\ProjectGallery\ProjectGalleryCollection;
use App\Http\Resources\ProjectGallery\ProjectGalleryResource;
use App\Jobs\DeleteBlob;
use App\Models\ProjectGallery;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Project Galleries
 * Manage project galleries.
 */
class ProjectGalleryController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ProjectGallery::class, 'project_gallery');
    }

    /**
     * List all project galleries.
     *
     * @apiResourceCollection App\Http\Resources\ProjectGallery\ProjectGalleryCollection
     *
     * @apiResourceModel App\Models\ProjectGallery
     */
    public function index(Request $request)
    {
        $projectGalleries = QueryBuilder::for(ProjectGallery::class)
            ->allowedFields([
                'id',
                'title',
                'description',
                'url',
                'image',
                'created_at',
                'updated_at',
            ])
            ->allowedFilters([
                'title',
                'description',
                'url',
            ])
            ->allowedSorts([
                'id',
                'title',
                'created_at',
                'updated_at',
            ])
            ->defaultSorts([
                '-id',
            ])
            ->cursorPaginate($request->query('per_page', 10));

        return new ProjectGalleryCollection($projectGalleries);
    }

    /**
     * Create a project gallery.
     *
     * @apiResource App\Http\Resources\ProjectGallery\ProjectGalleryResource
     *
     * @apiResourceModel App\Models\ProjectGallery
     */
    public function store(StoreProjectGalleryRequest $request)
    {
        $manifest = Storage::store($request->file('image'), 'images/project-galleries');

        $projectGallery = ProjectGallery::create(
            array_replace($request->validated(), ['image' => $manifest])
        );

        return new ProjectGalleryResource($projectGallery);
    }

    /**
     * Retrieve a project gallery.
     *
     * @apiResource App\Http\Resources\ProjectGallery\ProjectGalleryResource
     *
     * @apiResourceModel App\Models\ProjectGallery
     */
    public function show(ProjectGallery $projectGallery)
    {
        $projectGallery = QueryBuilder::for(ProjectGallery::where('id', $projectGallery->id))
            ->allowedFields([
                'id',
                'title',
                'description',
                'url',
                'image',
                'created_at',
                'updated_at',
            ])
            ->firstOrFail();

        return new ProjectGalleryResource($projectGallery);
    }

    /**
     * Update a project gallery.
     *
     * @apiResource App\Http\Resources\ProjectGallery\ProjectGalleryResource
     *
     * @apiResourceModel App\Models\ProjectGallery
     */
    public function update(UpdateProjectGalleryRequest $request, ProjectGallery $projectGallery)
    {
        $hasImage = $request->has('image');

        if ($hasImage) {
            $encodedManifest = $projectGallery->getRawOriginal('image');

            dispatch(new DeleteBlob($encodedManifest));

            $manifest = Storage::store($request->file('image'), 'images/project-galleries');
        }

        $projectGallery->update(
            $hasImage
                ? array_replace($request->validated(), ['image' => $manifest])
                : $request->validated()
        );

        return new ProjectGalleryResource($projectGallery);
    }

    /**
     * Delete a project gallery.
     *
     * @apiResource App\Http\Resources\ProjectGallery\ProjectGalleryResource
     *
     * @apiResourceModel App\Models\ProjectGallery
     */
    public function destroy(ProjectGallery $projectGallery)
    {
        $encodedManifest = $projectGallery->getRawOriginal('image');

        dispatch(new DeleteBlob($encodedManifest));

        $projectGallery->delete();

        return new ProjectGalleryResource($projectGallery);
    }
}
