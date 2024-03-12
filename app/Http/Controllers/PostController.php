<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $posts = Post::where('name', 'like', '%' . $request->input('q') . '%')->get();
        $posts = $posts->map(function ($post) {
            return [
                'id' => $post->id,
                'title' => $post->title,
                'content' => $post->content,
                'time' => $post->time,
                'cover_image' => $post->cover_image
            ];
        });

        return response()->json($posts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'title' => 'required|regex:/^[a-zA-Z.0-9.\s]+$/|max:255',
            'content' => 'required|string',
            'time' => 'required|date_format:H:i:s',
            'cover_image' => 'required|mimes:jpg,png,jpeg|max:2048',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response(['error' => $validator->errors()->first()], 422);
        }

        try {
            Post::create([
                'title' => $request->title,
                'content' => $request->content,
                'time' => $request->time,
                'cover_image' => $request->file('cover_image')->store('images/post', 'public'),
            ]);

            return response()->json(['success' => 'Data berhasil disimpan'], 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Data gagal disimpan'], 401);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        $post = [
            'id' => $post->id,
            'title' => $post->title,
            'content' => $post->content,
            'time' => $post->time,
            'cover_image' => $post->cover_image
        ];

        return response()->json($post);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        $rules = [
            'title' => 'required|regex:/^[a-zA-Z.0-9.\s]+$/|max:255',
            'content' => 'required|string',
            'time' => 'required|date_format:H:i:s',
            'cover_image' => 'image|mimes:jpg,png,jpeg|max:2048',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response(['error' => $validator->errors()->first()], 422);
        }

        try {
            $post->title = $request->title;
            $post->content = $request->content;
            $post->time = $request->time;
            if ($request->has('cover_image')) {
                $post->cover_image = $request->file('cover_image')->store('images/post', 'public');
            }
            $post->save();

            return response()->json(['success' => 'Data berhasil diubah'], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Data gagal diubah'], 401);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        $post->delete();

        return response()->json(['success' => 'Data berhasil dihapus'], 200);
    }
}
