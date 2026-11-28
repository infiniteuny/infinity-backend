<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\StorageVisibility;
use App\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectGallery\StoreProjectGalleryRequest;
use App\Http\Requests\ProjectGallery\UpdateProjectGalleryRequest;
use App\Http\Resources\ProjectGallery\ProjectGalleryCollection;
use App\Http\Resources\ProjectGallery\ProjectGalleryResource;
use App\Jobs\DeleteBlob;
use App\Models\ProjectGallery;
use App\Traits\HasPagination;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;
use Throwable;

/**
 * @group Project Galleries
 * Manage project galleries.
 */
class ProjectGalleryController extends Controller
{
    use HasPagination;

    public function __construct()
    {
        $this->middleware('can:create,'.ProjectGallery::class)->only('store');
        $this->middleware('can:update,project_gallery')->only('update');
        $this->middleware('can:delete,project_gallery')->only('destroy');
    }

    /**
     * List all project galleries
     *
     * @unauthenticated
     *
     * @apiResourceCollection App\Http\Resources\ProjectGallery\ProjectGalleryCollection
     *
     * @apiResourceModel App\Models\ProjectGallery paginate=10,cursor
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `title`, `description`, `url`, `image`, `created_at`, `updated_at`. No-example
     * @queryParam filters[title] string Filter by partial `title`. No-example
     * @queryParam filters[description] string Filter by partial `description`. No-example
     * @queryParam filters[url] string Filter by partial `url`. No-example
     * @queryParam filters[created_at] string Filter by `created_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam filters[updated_at] string Filter by `updated_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam sorts string Comma-separated list of fields to sort by. Prefix with `-` for descending order. Allowed values: `id`, `title`, `created_at`, `updated_at`. Default: `-id`. No-example
     * @queryParam per_page integer Number of items to return per page. Defaults to 10. Example: 10
     * @queryParam cursor string Cursor token for pagination. No-example
     */
    public function index(Request $request)
    {
        $projectGalleries = QueryBuilder::for(ProjectGallery::class)
            ->allowedFields(
                'id',
                'title',
                'description',
                'url',
                'image',
                'created_at',
                'updated_at',
            )
            ->allowedFilters(
                'title',
                'description',
                'url',
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC),
            )
            ->allowedSorts(
                'id',
                'title',
                'created_at',
                'updated_at',
            )
            ->defaultSorts('-id');

        $projectGalleries = $this->cursorPaginateWithTieBreaker($projectGalleries, $request);

        return new ProjectGalleryCollection($projectGalleries);
    }

    /**
     * Create a project gallery
     *
     * @apiResource App\Http\Resources\ProjectGallery\ProjectGalleryResource status=201
     *
     * @apiResourceModel App\Models\ProjectGallery
     */
    public function store(StoreProjectGalleryRequest $request)
    {
        $manifest = Storage::store(
            $request->file('image'),
            'project-galleries/images',
            StorageVisibility::PUBLIC,
        );

        try {
            $projectGallery = ProjectGallery::create(
                array_replace($request->validated(), ['image' => $manifest])
            );
        } catch (Throwable $e) {
            if (is_string($manifest) && $manifest !== '') {
                DeleteBlob::dispatch($manifest);
            }

            throw $e;
        }

        return new ProjectGalleryResource($projectGallery);
    }

    /**
     * Retrieve a project gallery
     *
     * @unauthenticated
     *
     * @apiResource App\Http\Resources\ProjectGallery\ProjectGalleryResource
     *
     * @apiResourceModel App\Models\ProjectGallery
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `title`, `description`, `url`, `image`, `created_at`, `updated_at`. No-example
     */
    public function show(ProjectGallery $projectGallery)
    {
        $projectGallery = QueryBuilder::for(ProjectGallery::where('id', $projectGallery->id))
            ->allowedFields(
                'id',
                'title',
                'description',
                'url',
                'image',
                'created_at',
                'updated_at',
            )
            ->firstOrFail();

        return new ProjectGalleryResource($projectGallery);
    }

    /**
     * Update a project gallery
     *
     * @apiResource App\Http\Resources\ProjectGallery\ProjectGalleryResource
     *
     * @apiResourceModel App\Models\ProjectGallery
     */
    public function update(UpdateProjectGalleryRequest $request, ProjectGallery $projectGallery)
    {
        $hasImage = $request->has('image');
        $oldManifest = null;
        $manifest = null;

        if ($hasImage) {
            $oldManifest = $projectGallery->getRawOriginal('image');
            $manifest = Storage::store(
                $request->file('image'),
                'project-galleries/images',
                StorageVisibility::PUBLIC,
            );
        }

        try {
            $projectGallery->update(
                $hasImage
                    ? array_replace($request->validated(), ['image' => $manifest])
                    : $request->validated()
            );
        } catch (Throwable $e) {
            if (is_string($manifest) && $manifest !== '') {
                DeleteBlob::dispatch($manifest);
            }

            throw $e;
        }

        if (is_string($oldManifest) && $oldManifest !== '') {
            DeleteBlob::dispatch($oldManifest);
        }

        return new ProjectGalleryResource($projectGallery);
    }

    /**
     * Delete a project gallery
     *
     * @apiResource App\Http\Resources\ProjectGallery\ProjectGalleryResource
     *
     * @apiResourceModel App\Models\ProjectGallery
     */
    public function destroy(ProjectGallery $projectGallery)
    {
        $manifest = $projectGallery->getRawOriginal('image');

        $projectGallery->delete();

        if (is_string($manifest) && $manifest !== '') {
            DeleteBlob::dispatch($manifest);
        }

        return new ProjectGalleryResource($projectGallery);
    }
}
