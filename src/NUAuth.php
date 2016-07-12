<?php

namespace Numesia\NUAuth;

use Auth;
use JWTAuth;

class NUAuth
{
    /**
     * Auth
     * @var Payload
     */
    private $auth;

    /**
     * User
     * @var User
     */
    private $user;

    /**
     * Get auth payload instance
     *
     * @return $this
     */
    public function auth()
    {
        if ($this->auth) {
            return $this->auth;
        }

        $this->auth = JWTAuth::parseToken()->getPayload();

        return $this->auth;
    }

    public function user()
    {
        if ($this->user) {
            return $this->user;
        }

        $authId            = $this->auth()->get('sub');
        $userModel         = env('NAUTH_USER_MODEL', 'App\Models\User');
        return $this->user = $userModel::where(env('NAUTH_KEY', 'auth_id'), $authId)->firstOrFail();
    }

    public function login()
    {
        Auth::login($this->user());
    }

    public function logout()
    {
        $token = JWTAuth::getToken();
        if ($token) {
            JWTAuth::setToken($token)->invalidate();
        }

        $this->user = $this->auth = null;
        Auth::logout();
    }
}
