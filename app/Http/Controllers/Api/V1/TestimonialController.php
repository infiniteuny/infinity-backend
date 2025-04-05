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
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Testimonial
 * Manage testimonials.
 */
class TestimonialController extends Controller
{
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
     */
    public function index(Request $request)
    {
        $testimonials = QueryBuilder::for(Testimonial::class)
            ->allowedFields([
                'id',
                'name',
                'position',
                'photo',
                'content',
                'created_at',
                'updated_at',
            ])
            ->allowedFilters([
                'name',
                'position',
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC),
            ])
            ->allowedSorts([
                'id',
                'name',
                'position',
                'created_at',
                'updated_at',
            ])
            ->defaultSorts([
                '-id',
            ])
            ->cursorPaginate($request->query('per_page', 10));

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

        $testimonial = Testimonial::create(
            array_replace($request->validated(), ['photo' => $manifest])
        );

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
     */
    public function show(Testimonial $testimonial)
    {
        $testimonial = QueryBuilder::for(Testimonial::where('id', $testimonial->id))
            ->allowedFields([
                'id',
                'name',
                'position',
                'photo',
                'content',
                'created_at',
                'updated_at',
            ])
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

        if ($hasPhoto) {
            $oldManifest = $testimonial->getRawOriginal('photo');

            dispatch(new DeleteBlob($oldManifest));

            $manifest = Storage::store(
                $request->file('photo'),
                'testimonials/photos',
                StorageVisibility::PUBLIC,
            );
        }

        $testimonial->update(
            $hasPhoto
                ? array_replace($request->validated(), ['photo' => $manifest])
                : $request->validated()
        );

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

        dispatch(new DeleteBlob($manifest));

        $testimonial->delete();

        return new TestimonialResource($testimonial);
    }
}
