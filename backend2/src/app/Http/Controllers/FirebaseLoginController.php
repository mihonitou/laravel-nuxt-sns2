<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use App\Models\User;
use Illuminate\Support\Str;


class FirebaseLoginController extends Controller
{
    public function login(Request $request)
    {
        $idToken = $request->input('idToken');

        $factory = (new Factory)
            ->withServiceAccount('/var/www/storage/app/firebase/serviceAccountKey.json')
            ->withProjectId('laravel-nuxt-sns2');

        $auth = $factory->createAuth();

        $verifiedIdToken = $auth->verifyIdToken($idToken);

        $uid = $verifiedIdToken->claims()->get('sub');

        // 🔥 追加：Firebaseの情報取得
        $firebaseUser = $auth->getUser($uid);

        $name = $firebaseUser->displayName ?? 'anonymous';
        $email = $firebaseUser->email ?? ($uid . '@dummy.com');

        $user = User::where('firebase_uid', $uid)->first();

        if (!$user) {
            $user = User::create([
                'firebase_uid' => $uid,
                'name' => $name,
                'email' => $email,
                'password' => bcrypt(Str::random(16)),
            ]);
        }

        return response()->json([
            'uid' => $uid,
            'user_id' => $user->id
        ]);
    }

    public function me(Request $request)
    {
        $idToken = $request->bearerToken();

        if (!$idToken) {
            return response()->json(['message' => 'No token'], 401);
        }

        try {
            $factory = (new \Kreait\Firebase\Factory)
                ->withServiceAccount('/var/www/storage/app/firebase/serviceAccountKey.json')
                ->withProjectId('laravel-nuxt-sns2');

            $auth = $factory->createAuth();

            $verifiedIdToken = $auth->verifyIdToken($idToken);

            $uid = $verifiedIdToken->claims()->get('sub');

            $user = \App\Models\User::where('firebase_uid', $uid)->first();

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            return response()->json([
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }
    }

}
