<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Post::class, 'post');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $posts = Post::where('name', 'like', '%'.$request->input('q').'%')->get();
        $posts = $posts->map(function ($post) {
            return [
                'id' => $post->id,
                'title' => $post->title,
                'content' => $post->content,
                'time' => $post->time,
                'cover_image' => $post->cover_image,
            ];
        });

        return response()->json($posts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request)
    {
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
            'cover_image' => $post->cover_image,
        ];

        return response()->json($post);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
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
