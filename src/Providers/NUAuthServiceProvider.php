<?php

namespace Numesia\NUAuth\Providers;

use Numesia\NUAuth\NUAuth;
use Illuminate\Support\ServiceProvider;

class NUAuthServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('nuauth.php'),
        ], 'config');

        $routeConfig = [
            'namespace'  => 'Numesia\NUAuth\Controllers',
            'prefix'     => 'nuauth',
        ];

        if (!$this->app->routesAreCached()) {
            $this->app['router']->group($routeConfig, function ($router) {
                $router->post('register', 'AuthController@register');
                $router->post('password/changePassword', 'AuthController@changePassword');
                $router->post('password/sendResetEmail', 'AuthController@sendResetEmail');
                $router->post('logout', 'AuthController@logout');
                $router->put('account', 'AuthController@update');
            });
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('NUAuth', function () {
            return new NUAuth;
        });
    }
}
