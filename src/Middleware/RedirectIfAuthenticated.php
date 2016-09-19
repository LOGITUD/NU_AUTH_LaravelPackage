<?php

namespace Numesia\NUAuth\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated extends Authenticate
{
    public $redirect = null;
    public $guard = null;

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
            $ability = $this->nuauth->userHas($conditions);
            if ($ability !== true) {
                return $next($request);
            }
            $this->nuauth->login();
        } catch (\Exception $e) {
            return $next($request);
        }

        $route = $this->redirect ?: env('NAUTH_ADMIN_ROUTE');
        return redirect()->route($route);
    }
}
