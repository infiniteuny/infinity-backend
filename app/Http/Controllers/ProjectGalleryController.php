<?php

namespace App\Http\Controllers;

use App\Models\ProjectGallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectGalleryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $projectGalleries = ProjectGallery::where('name', 'like', '%' . $request->input('q') . '%')->get();
        $projectGalleries = $projectGalleries->map(function ($projectGallery) {
            return [
                'id' => $projectGallery->id,
                'title' => $projectGallery->title,
                'description' => $projectGallery->description,
                'url' => $projectGallery->url,
                'image' => $projectGallery->image
            ];
        });

        return response()->json($projectGalleries);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'title' => 'required|regex:/^[a-zA-Z.0-9.\s]+$/|max:255',
            'description' => 'required|string',
            'url' => 'required|url',
            'image' => 'required|mimes:jpg,png,jpeg|max:2048',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response(['error' => $validator->errors()->first()], 422);
        }

        try {
            ProjectGallery::create([
                'title' => $request->title,
                'description' => $request->description,
                'url' => $request->url,
                'image' => $request->file('image')->store('images/project-gallery', 'public'),
            ]);

            return response()->json(['success' => 'Data berhasil disimpan'], 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Data gagal disimpan'], 401);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ProjectGallery $projectGallery)
    {
        $projectGallery =  [
            'id' => $projectGallery->id,
            'title' => $projectGallery->title,
            'description' => $projectGallery->description,
            'url' => $projectGallery->url,
            'image' => $projectGallery->image
        ];

        return response()->json($projectGallery);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProjectGallery $projectGallery)
    {
        $rules = [
            'title' => 'required|regex:/^[a-zA-Z.0-9.\s]+$/|max:255',
            'description' => 'required|string',
            'url' => 'required|url',
            'image' => 'mimes:jpg,png,jpeg|max:2048',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response(['error' => $validator->errors()->first()], 422);
        }

        try {
            $projectGallery->title = $request->title;
            $projectGallery->description = $request->description;
            $projectGallery->url = $request->url;
            if ($request->has('image')) {
                $projectGallery->image = $request->file('image')->store('images/project-gallery', 'public');
            }
            $projectGallery->save();

            return response()->json(['success' => 'Data berhasil diubah'], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Data gagal diubah'], 401);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProjectGallery $projectGallery)
    {
        $projectGallery->delete();

        return response()->json(['success' => 'Data berhasil dihapus'], 200);
    }
}
