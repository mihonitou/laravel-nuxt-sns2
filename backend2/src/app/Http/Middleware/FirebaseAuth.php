<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use App\Models\User;


class FirebaseAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {


        $idToken = $request->bearerToken();

        if (!$idToken) {
            return response()->json(['message' => 'No token'], 401);
        }

        try {
            $auth = (new Factory)
                ->withServiceAccount(storage_path('app/firebase/serviceAccountKey.json'))
                ->withProjectId('laravel-nuxt-sns2')
                ->createAuth();

            $verifiedIdToken = $auth->verifyIdToken($idToken);
            $uid = $verifiedIdToken->claims()->get('sub');

            $user = User::where('firebase_uid', $uid)->first();

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            // 🔥 セッション使わずに渡す
            $request->merge(['auth_user' => $user]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        return $next($request);
    }
}
