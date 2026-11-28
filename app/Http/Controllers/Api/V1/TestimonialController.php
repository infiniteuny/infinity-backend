<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\StorageVisibility;
use App\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Testimonial\StoreTestimonialRequest;
use App\Http\Requests\Testimonial\UpdateTestimonialRequest;
use App\Http\Resources\Testimonial\TestimonialCollection;
use App\Http\Resources\Testimonial\TestimonialResource;
use App\Jobs\DeleteBlob;
use App\Models\Testimonial;
use App\Traits\HasPagination;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;
use Throwable;

/**
 * @group Testimonials
 * Manage testimonials.
 */
class TestimonialController extends Controller
{
    use HasPagination;

    public function __construct()
    {
        $this->middleware('can:create,'.Testimonial::class)->only('store');
        $this->middleware('can:update,testimonial')->only('update');
        $this->middleware('can:delete,testimonial')->only('destroy');
    }

    /**
     * List all testimonials
     *
     * @unauthenticated
     *
     * @apiResourceCollection App\Http\Resources\Testimonial\TestimonialCollection
     *
     * @apiResourceModel App\Models\Testimonial paginate=10,cursor
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `name`, `position`, `photo`, `content`, `created_at`, `updated_at`. No-example
     * @queryParam filters[name] string Filter by partial `name`. No-example
     * @queryParam filters[position] string Filter by partial `position`. No-example
     * @queryParam filters[created_at] string Filter by `created_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam filters[updated_at] string Filter by `updated_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam sorts string Comma-separated list of fields to sort by. Prefix with `-` for descending order. Allowed values: `id`, `name`, `position`, `created_at`, `updated_at`. Default: `-id`. No-example
     * @queryParam per_page integer Number of items to return per page. Defaults to 10. Example: 10
     * @queryParam cursor string Cursor token for pagination. No-example
     */
    public function index(Request $request)
    {
        $testimonials = QueryBuilder::for(Testimonial::class)
            ->allowedFields(
                'id',
                'name',
                'position',
                'photo',
                'content',
                'created_at',
                'updated_at',
            )
            ->allowedFilters(
                'name',
                'position',
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC),
            )
            ->allowedSorts(
                'id',
                'name',
                'position',
                'created_at',
                'updated_at',
            )
            ->defaultSorts('-id');

        $testimonials = $this->cursorPaginateWithTieBreaker($testimonials, $request);

        return new TestimonialCollection($testimonials);
    }

    /**
     * Create a testimonial
     *
     * @apiResource App\Http\Resources\Testimonial\TestimonialResource status=201
     *
     * @apiResourceModel App\Models\Testimonial
     */
    public function store(StoreTestimonialRequest $request)
    {
        $manifest = Storage::store(
            $request->file('photo'),
            'testimonials/photos',
            StorageVisibility::PUBLIC,
        );

        try {
            $testimonial = Testimonial::create(
                array_replace($request->validated(), ['photo' => $manifest])
            );
        } catch (Throwable $e) {
            if (is_string($manifest) && $manifest !== '') {
                DeleteBlob::dispatch($manifest);
            }

            throw $e;
        }

        return new TestimonialResource($testimonial);
    }

    /**
     * Retrieve a testimonial
     *
     * @unauthenticated
     *
     * @apiResource App\Http\Resources\Testimonial\TestimonialResource
     *
     * @apiResourceModel App\Models\Testimonial
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `name`, `position`, `photo`, `content`, `created_at`, `updated_at`. No-example
     */
    public function show(Testimonial $testimonial)
    {
        $testimonial = QueryBuilder::for(Testimonial::where('id', $testimonial->id))
            ->allowedFields(
                'id',
                'name',
                'position',
                'photo',
                'content',
                'created_at',
                'updated_at',
            )
            ->firstOrFail();

        return new TestimonialResource($testimonial);
    }

    /**
     * Update a testimonial
     *
     * @apiResource App\Http\Resources\Testimonial\TestimonialResource
     *
     * @apiResourceModel App\Models\Testimonial
     */
    public function update(UpdateTestimonialRequest $request, Testimonial $testimonial)
    {
        $hasPhoto = $request->has('photo');
        $oldManifest = null;
        $manifest = null;

        if ($hasPhoto) {
            $oldManifest = $testimonial->getRawOriginal('photo');
            $manifest = Storage::store(
                $request->file('photo'),
                'testimonials/photos',
                StorageVisibility::PUBLIC,
            );
        }

        try {
            $testimonial->update(
                $hasPhoto
                    ? array_replace($request->validated(), ['photo' => $manifest])
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

        return new TestimonialResource($testimonial);
    }

    /**
     * Delete a testimonial
     *
     * @apiResource App\Http\Resources\Testimonial\TestimonialResource
     *
     * @apiResourceModel App\Models\Testimonial
     */
    public function destroy(Testimonial $testimonial)
    {
        $manifest = $testimonial->getRawOriginal('photo');

        $testimonial->delete();

        if (is_string($manifest) && $manifest !== '') {
            DeleteBlob::dispatch($manifest);
        }

        return new TestimonialResource($testimonial);
    }
}
