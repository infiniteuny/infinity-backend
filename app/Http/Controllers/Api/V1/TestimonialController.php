<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Testimonial\StoreTestimonialRequest;
use App\Http\Requests\Testimonial\UpdateTestimonialRequest;
use App\Jobs\DeleteBlob;
use App\Models\Testimonial;
use App\Repositories\StorageFacade;
use App\Utils\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class TestimonialController extends Controller
{
    public function __construct(
        protected StorageFacade $storageFacade,
    ) {
        // $this->authorizeResource(Testimonial::class, 'testimonial');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $testimonials = QueryBuilder::for(Testimonial::class)
            ->allowedFilters([
                'code',
                'name',
                'degree_id',
                'faculty_id',
            ])
            ->defaultSorts([
                '-created_at',
                'id',
            ])
            ->allowedSorts([
                'id',
                'code',
                'name',
                'degree_id',
                'faculty_id',
                'created_at',
                'updated_at',
            ])
            ->paginate($request->query('per_page', 10));

        return ResponseFormatter::collection('testimonials', $testimonials);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTestimonialRequest $request): JsonResponse
    {
        $manifest = $this->storageFacade->store($request->file('photo'), 'images/testimonials');

        $testimonial = Testimonial::create(
            array_replace($request->validated(), ['image' => $manifest])
        );

        return ResponseFormatter::singleton('testimonial', $testimonial, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Testimonial $testimonial): JsonResponse
    {
        return ResponseFormatter::singleton('testimonial', $testimonial);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTestimonialRequest $request, Testimonial $testimonial): JsonResponse
    {
        $hasPhoto = $request->has('photo');

        if ($hasPhoto) {
            $encodedManifest = $testimonial->getRawOriginal('photo');

            dispatch(new DeleteBlob($encodedManifest));

            $manifest = $this->storageFacade->store($request->file('photo'), 'images/testimonials');
        }

        $testimonial->update(
            $hasPhoto
                ? array_replace($request->validated(), ['photo' => $manifest])
                : $request->validated()
        );

        return ResponseFormatter::singleton('testimonial', $testimonial);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Testimonial $testimonial)
    {
        $encodedManifest = $testimonial->getRawOriginal('photo');

        dispatch(new DeleteBlob($encodedManifest));

        $testimonial->delete();

        return ResponseFormatter::singleton('testimonial', $testimonial);
    }
}
