<?php

namespace Numesia\NUAuth;
use JWTAuth;

class NUAuth
{
    private $user;

    /**
     * Get connected user
     *
     * @return $this
     */
    public function user()
    {
        if ($this->user) {
            return $this;
        }

        $this->user = JWTAuth::parseToken();

        return $this;
    }

    /**
     * Get user permissions
     *
     * @return array
     */
    public function getPermissions()
    {

    }

    /**
     * Get user roles
     *
     * @return array
     */
    public function getRoles()
    {

    }
}
