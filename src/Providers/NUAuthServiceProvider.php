<?php

namespace Numesia\NUAuth\Providers;

use Illuminate\Support\ServiceProvider;

class NUAuthServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Boot the service provider.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('nuauth.php'),
        ], 'config');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // register providers

        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'nuauth');
    }
}
