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

        $idToken = $request->bearerToken();

        if (!$idToken) {
            return response()->json(['message' => 'No token'], 401);
        }

        $factory = (new \Kreait\Firebase\Factory)
            ->withServiceAccount('/var/www/storage/app/firebase/serviceAccountKey.json')
            ->withProjectId('laravel-nuxt-sns2');

        $auth = $factory->createAuth();

        $verifiedIdToken = $auth->verifyIdToken($idToken);

        $uid = $verifiedIdToken->claims()->get('sub');

        $user = User::where('firebase_uid', $uid)->first();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $post = Post::create([
            'user_id' => $user->id,
            'content' => $request->content,
        ]);

        return response()->json($post);
    }

    public function destroy(Request $request, $id)
    {
        $idToken = $request->bearerToken();

        if (!$idToken) {
            return response()->json(['message' => 'No token'], 401);
        }

        $factory = (new \Kreait\Firebase\Factory)
            ->withServiceAccount('/var/www/storage/app/firebase/serviceAccountKey.json')
            ->withProjectId('laravel-nuxt-sns2');

        $auth = $factory->createAuth();

        $verifiedIdToken = $auth->verifyIdToken($idToken);

        $uid = $verifiedIdToken->claims()->get('sub');

        $user = User::where('firebase_uid', $uid)->first();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

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
