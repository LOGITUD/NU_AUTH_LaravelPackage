# NU_AUTH_LaravelPackage

Laravel package that helps authenticate with the NU_AUTH service

This laravel auth package is a private package so we can't just require it using composer, that's why we have to add a vcs repository to tell 
composer from which url the package must be loaded.

    "repositories": [
        {
            "type": "vcs",
            "url":  "git@bitbucket.org:NUMESIA/nu_auth_laravelpackage.git"
        }
    ],
	"require": {
        "NUMESIA/laravel-auth": "0.0.2"
    },
	

Once this has finished, you will need to add the service provider to the providers array in your app.php config as follows:

	Tymon\JWTAuth\Providers\JWTAuthServiceProvider::class,
	Numesia\NUAuth\Providers\NUAuthServiceProvider::class,
	
Next, also in the app.php config file, under the aliases array, you may want to add the JWTAuth and NUAuth facades.

	'JWTAuth' => Tymon\JWTAuth\Facades\JWTAut::class
	'NUAuth' => Numesia\NUAuth\Facades\NUAuth::class

Finally, you will want to change your `JWT_SECRET` key from `.env` file:

    JWT_SECRET=YourAuthSecretKey

## How to use ?

### Middleware

NUAuth comes with an `Authenticate` middleware

This will check the header and query string (as explained above) for the presence of a token, and attempts to decode it.

To use the middlewares you will have to register them in `app/Http/Kernel.php` under the `$routeMiddleware` property:

	protected $routeMiddleware = [
    	...
    	'nuauth' => 'Numesia\NUAuth\Middleware\Authenticate',
	];
	
And then you can use it in your `app/Http/routes.php` file

	Route::group(['middleware' => 'nuauth'], function(){
    	Route::get('/', function () {
	        return "Hello I'm authenticated";
	    });
	});
	
### Alias

NUAuth comes with an `NUAuth` alias which contain some useful methods :

	<?php
	
	// Get auth user Payload instance
	\NUAuth::user()

	// Get user Scopes
	\NUAuth::user()->get('scopes');

	// Get user Roles
	\NUAuth::user()->get('roles');

	// Get user Id
	\NUAuth::user()->get('sub');
	
	// Logout auth user
	\NUAuth::logout();