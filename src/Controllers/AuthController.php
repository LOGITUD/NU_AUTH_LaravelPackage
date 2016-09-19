<?php

namespace Numesia\NUAuth\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AuthController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Show the homepage.
     *
     * @return Response
     */
    public function register(Request $request)
    {
        $model = env('USER_MODEL', 'App\Models\User');
        $auth_key = env('NAUTH_KEY', 'auth_id');

        $user = new $model;
        $user->{$auth_key} = $request->auth_id;
        $user->save();
    }

    /**
     * Show the homepage.
     *
     * @return Response
     */
    public function logout(Request $request)
    {
        $token = $request->token;

        if (!$token) {
            return response()->json(['error' => 'token_not_provided'], 500);
        }

        try {
            JWTAuth::setToken($token)->invalidate();
        }catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['error' => 'token_invalid'], 500);
        }
    }
}
