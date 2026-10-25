<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\FirebaseLoginController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\LikeController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/test', function () {
    return response()->json([
        'message' => 'API OK'
    ]);
});

Route::post('/firebase-login', [FirebaseLoginController::class, 'login']);

Route::get('/posts', [PostController::class, 'index']); // ←追加

Route::post('/me', [FirebaseLoginController::class, 'me']); // ←外す

Route::post('/posts', [PostController::class, 'store'])->middleware(\App\Http\Middleware\FirebaseAuth::class);

Route::delete('/posts/{id}', [PostController::class, 'destroy'])->middleware(\App\Http\Middleware\FirebaseAuth::class);

// ★ 先にcomments
Route::get('/posts/{postId}/comments', [CommentController::class, 'index']);
Route::post('/posts/{postId}/comments', [CommentController::class, 'store'])->middleware(\App\Http\Middleware\FirebaseAuth::class);

// ★ 後に詳細
Route::get('/posts/{id}', [PostController::class, 'show']);

Route::post('/likes', [LikeController::class, 'store'])->middleware(\App\Http\Middleware\FirebaseAuth::class);
Route::delete('/likes', [LikeController::class, 'destroy'])->middleware(\App\Http\Middleware\FirebaseAuth::class);