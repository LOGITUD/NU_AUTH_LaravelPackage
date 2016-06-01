<?php

namespace Numesia\NUAuth;
use JWTAuth;

class NUAuth
{
    /**
     * User
     * @var Payload
     */
    private $user;

    /**
     * Get authenticate user
     *
     * @return $this
     */
    public function user()
    {
        if ($this->user) {
            return $this->user;
        }

        $this->user = JWTAuth::parseToken()->getPayload();

        return $this->user;
    }

    public function logout()
    {
        $token = JWTAuth::getToken();
        if ($token) {
            JWTAuth::setToken($token)->invalidate();
        }
        $this->user = null;
    }
}
