<?php

namespace Numesia\NUAuth\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use JWTAuth;

class AuthController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     *
     * @return Response
     */
    public function register(Request $request)
    {
        if ($request->input('request.secret_key') != env('JWT_SECRET')) {
            return;
        }

        $model = env('NAUTH_USER_MODEL', 'App\Models\User');
        $auth_key = env('NAUTH_KEY', 'auth_id');
        $user = new $model;

        if (is_array(config('nuauth.data'))) {
            foreach (config('nuauth.data') as $field => $req) {
                $user->{$field} = $request->input($req);
            }
        }

        $user->{$auth_key} = $request->input('user.id');
        $user->save();
    }

    /**
     *
     * @return Response
     */
    public function changePassword(Request $request)
    {
        if ($request->input('request.secret_key') != env('JWT_SECRET')) {
            return;
        }

        event('password.changePassword', ['request' => $request]);
    }

    /**
     *
     * @return Response
     */
    public function sendResetEmail(Request $request)
    {
        if ($request->input('request.secret_key') != env('JWT_SECRET')) {
            return;
        }

        event('password.sendResetEmail', ['request' => $request]);
    }

    /**
     *
     * @return Response
     */
    public function logout(Request $request)
    {
        $token = JWTAuth::getToken();

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
