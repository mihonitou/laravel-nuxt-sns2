<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Comment;

class CommentController extends Controller
{
    // コメント一覧
    public function index($postId)
    {
        return Comment::with('user')
            ->where('post_id', $postId)
            ->latest()
            ->get();
    }

    // コメント投稿
    public function store(Request $request, $postId)
    {
        $request->validate([
            'content' => 'required|string',
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

        $comment = Comment::create([
            'user_id' => $user->id,
            'post_id' => $postId,
            'content' => $request->content,
        ]);

        return response()->json($comment, 201);
    }
}
