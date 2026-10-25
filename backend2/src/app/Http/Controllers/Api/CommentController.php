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

        $user = $request->get('auth_user');

        $comment = Comment::create([
            'user_id' => $user->id,
            'post_id' => $postId,
            'content' => $request->content,
        ]);

        return response()->json($comment, 201);
    }
}
