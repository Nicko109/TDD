<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreRequest;
use App\Http\Requests\Api\UpdateRequest;
use App\Http\Resources\Post\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::all();
        return PostResource::collection($posts)->resolve();
    }

    public function store(StoreRequest $request)
    {
        $data = $request->validated();
        if (!empty($data['image'])) {
        $path = Storage::disk('local')->put('/images', $data['image']);
        $data['image_url'] = $path;
        }
        unset($data['image']);
        $post = Post::create($data);

        return PostResource::make($post)->resolve();
    }
    public function show(Post $post)
    {

        return PostResource::make($post)->resolve();
    }

    public function update(Post $post, UpdateRequest $request)
    {
        $data = $request->validated();
        if (!empty($data['image'])) {
            $path = Storage::disk('local')->put('/images', $data['image']);
            $data['image_url'] = $path;
        }
        unset($data['image']);
        $post->update($data);

        $post = $post->fresh();
        return PostResource::make($post)->resolve();
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return response()->json([
            'message' => 'deleted'
        ]);
    }
}
