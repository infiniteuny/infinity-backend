<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Testimonial\StoreTestimonialRequest;
use App\Http\Requests\Testimonial\UpdateTestimonialRequest;
use App\Models\Testimonial;
use App\Utils\ResponseFormatter;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class TestimonialController extends Controller
{
    public function __construct()
    {
        // $this->authorizeResource(Testimonial::class, 'testimonial');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
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
    public function store(StoreTestimonialRequest $request)
    {
        $testimonial = Testimonial::create($request->validated());

        return ResponseFormatter::singleton('testimonial', $testimonial, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Testimonial $testimonial)
    {
        return ResponseFormatter::singleton('testimonial', $testimonial);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTestimonialRequest $request, Testimonial $testimonial)
    {
        $testimonial->update($request->validated());

        return ResponseFormatter::singleton('testimonial', $testimonial);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();

        return ResponseFormatter::singleton('testimonial', $testimonial);
    }
}
