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

    public $redirect = null;
    public $guard = null;

    /**
     * Create a new instance.
     *
     * @param \Numesia\NUAuth\NUAuth  $auth
     */
    public function __construct(NUAuth $nuauth)
    {
        $this->nuauth = $nuauth;
        $this->nuauth->setGuard($this->guard);
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
        $ability = $this->nuauth->userHas($conditions);

        if ($ability !== true) {
            return $this->respond('nauth.' . $ability, $ability, '401');
        }

        return $next($request);
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
            $route = $this->redirect ?: env('NAUTH_LOGIN_ROUTE');
            return redirect()->guest(route($route));
        }
    }
}
