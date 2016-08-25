<?php

namespace Numesia\NUAuth\Middleware;

use Closure;
use Exception;
use Illuminate\Support\Facades\Auth;
use Numesia\NUAuth\NUAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Request;

class Authenticate
{
    /**
     * Create a new instance.
     *
     * @param \Numesia\NUAuth\NUAuth  $auth
     */
    public function __construct(NUAuth $nuauth)
    {
        $this->nuauth = $nuauth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $conditions = '*:*:*')
    {
        try {
            $this->nuauth->login();
        } catch (TokenExpiredException $e) {
            return $this->respond('tymon.jwt.expired', 'token_expired', $e->getStatusCode(), [$e]);
        } catch (JWTException $e) {
            return $this->respond('tymon.jwt.invalid', 'token_invalid', $e->getStatusCode(), [$e]);
        } catch (Exception $e) {
            return $this->respond('nauth.user_unavailable', 'user_unavailable', '401');
        }

        $auth       = $this->nuauth->auth();
        $userClaims = $auth->get('user');
        $allRoles   = $auth->get('roles');

        @list($departments, $roles, $scopes) = explode(':', $conditions);

        if (!$this->isBelongTo($userClaims['departments'], $departments)) {
            return $this->respond('nauth.not_in_departments', 'not_in_departments', '401');
        }

        if (!$this->hasSuffisantRole($userClaims['roles'], $roles, $allRoles)) {
            return $this->respond('nauth.not_in_roles', 'not_in_roles', '401');
        }

        if (!$this->isBelongTo($userClaims['scopes'], $scopes)) {
            return $this->respond('nauth.not_in_scopes', 'not_in_scopes', '401');
        }

        return $next($request);
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
    /**
     * Fire event and return the response.
     *
     * @param  string   $event
     * @param  string   $error
     * @param  int  $status
     * @param  array    $payload
     * @return mixed
     */
    protected function respond($event, $error, $status, $payload = [])
    {
        event($event, $payload);

        if (Request::ajax() || Request::wantsJson()) {
            return response(['error' => $error], $status);
        } else {
            return redirect()->guest(route(env('NAUTH_LOGIN_ROUTE')));
        }
    }
}
