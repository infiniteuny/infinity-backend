<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectGalleryRequest;
use App\Http\Requests\UpdateProjectGalleryRequest;
use App\Models\ProjectGallery;
use Illuminate\Http\Request;

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
    public function store(StoreProjectGalleryRequest $request)
    {
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
    public function update(UpdateProjectGalleryRequest $request, ProjectGallery $projectGallery)
    {
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
