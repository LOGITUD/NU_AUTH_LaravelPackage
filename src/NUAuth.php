<?php

namespace Numesia\NUAuth;

use Auth;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

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
     * Guard
     * @var string
     */
    private $guard = null;

    public function setGuard($guard)
    {
        $this->guard = $guard;
    }

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

        $this->auth = JWTAuth::parseToken('bearer', $this->guard . 'authorization', $this->guard . 'token', $this->guard . 'access_token')->getPayload();

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

    public function userHas($conditions = '*:*:*')
    {
        $auth       = $this->auth();
        $userClaims = $auth->get('user');
        $allRoles   = $auth->get('roles');

        @list($departments, $roles, $scopes) = explode(':', $conditions);

        if (!$this->isBelongTo($userClaims['departments'], $departments)) {
            return 'not_in_departments';
        }

        if (!$this->hasSuffisantRole($userClaims['roles'], $roles, $allRoles)) {
            return 'not_in_roles';
        }

        if (!$this->isBelongTo($userClaims['scopes'], $scopes)) {
            return 'not_in_scopes';
        }

        return true;
    }


    /**
     * Check whether a Role is suffisant to pass
     *
     * @param  array    $authRoles
     * @param  string   $requestRoles
     * @param  array    $allRoles
     *
     * @return boolean
     */
    protected function hasSuffisantRole($authRoles, $requestRoles, $allRoles)
    {
        $roles = array_keys($allRoles);
        $reversedRoles = array_reverse($roles);

        foreach ($reversedRoles as $role) {
            $replace = implode(array_slice($reversedRoles, array_search($role, $reversedRoles)), '|');
            $requestRoles = str_replace($role.'+', $replace, $requestRoles);
        }

        foreach ($roles as $role) {
            $replace = implode(array_slice($roles, array_search($role, $roles)), '|');
            $requestRoles = str_replace($role.'-', $replace, $requestRoles);
        }

        return $this->isBelongTo($authRoles, $requestRoles);
    }

    /**
     * Check whether a string elements belongs to a group
     *
     * @param  array   $group
     * @param  string  $elements
     *
     * @return boolean
     */
    protected function isBelongTo(array $group, $elements)
    {
        if (!$elements || $elements == '*') {
            return true;
        }

        $andElements = explode('&', $elements);
        $orElements  = explode('|', $elements);

        $countAndElements = count($andElements);
        $countOrElements  = count($orElements);

        if ($countAndElements == $countOrElements) {
            return in_array($elements, $group);
        } else if ($countAndElements > $countOrElements) {
            return count(array_intersect($andElements, $group)) == $countAndElements;
        } else {
            return count(array_intersect($orElements, $group)) > 0;
        }
    }

    public function login()
    {
        Auth::login($this->user());
    }

    public function logout()
    {
        $token = $this->getToken();

        if ($token) {
            JWTAuth::setToken($token)->invalidate();
        }

        $this->user = $this->auth = null;

        Auth::logout();
    }

    private function getToken()
    {
        try {
            JWTAuth::parseToken('bearer', $this->guard . 'authorization', $this->guard . 'token', $this->guard . 'access_token');
        } catch (JWTException $e) {
            return false;
        }

        return JWTAuth::getToken();
    }
}
