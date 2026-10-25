<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Post;

use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('user')
            ->withCount('likes')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($posts);
    }

    public function show($id)
    {
        $post = Post::with('user')
            ->withCount('likes')
            ->findOrFail($id);

        return response()->json($post);
    }

    public function store(Request $request)
    {

        $request->validate([
            'content' => 'required|string|max:120',
        ]);

        $user = $request->get('auth_user');

        $post = Post::create([
            'user_id' => $user->id,
            'content' => $request->content,
        ]);

        return response()->json($post);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->get('auth_user');

        $post = Post::findOrFail($id);

        if ($post->user_id !== $user->id) {
            return response()->json([
                'message' => 'Forbidden'
            ], 403);
        }

        $post->delete();

        return response()->json([
            'message' => 'Deleted'
        ]);
    }
}
