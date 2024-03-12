<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TestimonialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $testimonials = Testimonial::where('name', 'like', '%' . $request->input('q') . '%')->get();
        $testimonials = $testimonials->map(function ($testimonial) {
            return [
                'id' => $testimonial->id,
                'user' => $testimonial->load('user'),
                'position' => $testimonial->position,
                'photo' => $testimonial->photo,
                'content' => $testimonial->content
            ];
        });

        return response()->json($testimonials);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'user_id' => 'required|exists:users,id',
            'position' => 'required|regex:/^[a-zA-Z.0-9.\s]+$/|max:255',
            'photo' => 'required|mimes:jpg,png,jpeg|max:2048',
            'content' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response(['error' => $validator->errors()->first()], 422);
        }

        try {
            Testimonial::create([
                'user_id' => $request->user_id,
                'position' => $request->position,
                'photo' => $request->file('photo')->store('images/testimonial', 'public'),
                'content' => $request->content,
            ]);

            return response()->json(['success' => 'Data berhasil disimpan'], 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Data gagal disimpan'], 401);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Testimonial $testimonial)
    {
        $testimonial = [
            'id' => $testimonial->id,
            'user' => $testimonial->load('user'),
            'position' => $testimonial->position,
            'photo' => $testimonial->photo,
            'content' => $testimonial->content
        ];

        return response()->json($testimonial);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Testimonial $testimonial)
    {
        $rules = [
            'user_id' => 'required|exists:users,id',
            'position' => 'required|regex:/^[a-zA-Z.0-9.\s]+$/|max:255',
            'photo' => 'required|mimes:jpg,png,jpeg|max:2048',
            'content' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response(['error' => $validator->errors()->first()], 422);
        }

        try {
            $testimonial->user_id = $request->user_id;
            $testimonial->postion = $request->position;
            $testimonial->content = $request->content;
            if ($request->has('photo')) {
                $testimonial->photo = $request->file('photo')->store('images/testimonial', 'public');
            }
            $testimonial->save();

            return response()->json(['success' => 'Data berhasil diubah'], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Data gagal diubah'], 401);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();

        return response()->json(['success' => 'Data berhasil dihapus'], 200);
    }
}
