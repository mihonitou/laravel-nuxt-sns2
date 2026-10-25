<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Like;
use App\Models\User;

class LikeController extends Controller
{
    public function store(Request $request)
    {
        $request->validate(['post_id' => 'required|integer|exists:posts,id',]);

        $user = $request->get('auth_user');

        $exists = Like::where('user_id', $user->id)->where('post_id', $request->post_id)->exists();
        if ($exists) {
            return response()->json(['message' => 'Already liked'], 200);
        }
        $like = Like::create(['user_id' => $user->id, 'post_id' => $request->post_id,]);
        return response()->json($like, 201);
    }
    public function destroy(Request $request)
    {
        $request->validate(['post_id' => 'required|integer|exists:posts,id',]);

        $user = $request->get('auth_user');

        $deleted = Like::where('user_id', $user->id)->where('post_id', $request->post_id)->delete();
        if ($deleted) {
            return response()->json(['message' => 'Unliked'], 200);
        }
        return response()->json(['message' => 'Like not found'], 404);
    }
}