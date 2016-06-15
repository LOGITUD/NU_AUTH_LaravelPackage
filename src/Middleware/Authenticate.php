<?php

namespace Numesia\NUAuth\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Numesia\NUAuth\NUAuth;
use Exception;

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
    public function handle($request, Closure $next)
    {
        try {
            $user = $this->nuauth->user();
        } catch (TokenExpiredException $e) {
            return $this->respond('tymon.jwt.expired', 'token_expired', $e->getStatusCode(), [$e]);
        } catch (JWTException $e) {
            return $this->respond('tymon.jwt.invalid', 'token_invalid', $e->getStatusCode(), [$e]);
        } catch (Exception $e) {
            return $this->respond('user.unavailable', 'user_unavailable', '401');
        }

        event('tymon.jwt.valid', $user);

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
        return response()->json(['error' => $error], $status);
    }
}
