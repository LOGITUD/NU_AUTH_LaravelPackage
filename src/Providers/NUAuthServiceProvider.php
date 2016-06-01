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
        //
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
