<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Like;

class LikeController extends Controller
{
    public function store(Request $request)
    {
        $user_id = $request->user_id; // 仮でフロントから送る

        $exists = Like::where('user_id', $user_id)
            ->where('post_id', $request->post_id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Already liked'], 200);
        }

        $like = Like::create([
            'user_id' => $user_id,
            'post_id' => $request->post_id,
        ]);

        return response()->json($like, 201);
    }

    public function destroy(Request $request)
    {
        $deleted = Like::where('user_id', $request->user_id)
            ->where('post_id', $request->post_id)
            ->delete();

        if ($deleted) {
            return response()->json(['message' => 'Unliked'], 200);
        }

        return response()->json(['message' => 'Like not found'], 404);
    }
}
