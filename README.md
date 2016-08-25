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
        "NUMESIA/laravel-auth": "0.0.*"
    },


Once this has finished, you will need to add the service provider to the providers array in your app.php config as follows:

    Tymon\JWTAuth\Providers\JWTAuthServiceProvider::class,
    Numesia\NUAuth\Providers\NUAuthServiceProvider::class,

Next, also in the app.php config file, under the aliases array, you may want to add the JWTAuth and NUAuth facades.

    'JWTAuth' => Tymon\JWTAuth\Facades\JWTAuth::class,
    'NUAuth' => Numesia\NUAuth\Facades\NUAuth::class,

Finally, you will want to change your `JWT_SECRET`, `NAUTH_USER_MODEL`, `NAUTH_KEY` keys from `.env` file:

    JWT_SECRET=YourAuthSecretKey
    NAUTH_USER_MODEL=App\Models\User
    NAUTH_KEY=auth_id
    NAUTH_LOGIN_ROUTE='zxadmin.login'
    NAUTH_ADMIN_ROUTE='zxadmin.dashboard.index'

> /!\ You have to create `auth_id` field in you user model

## How to use ?

### Middleware

NUAuth comes with an `Authenticate` middleware

This will check the header and query string (as explained above) for the presence of a token, and attempts to decode it.

To use the middlewares you will have to register them in `app/Http/Kernel.php` under the `$routeMiddleware` property:

    protected $routeMiddleware = [
        ...
        'nuauth' => \Numesia\NUAuth\Middleware\Authenticate::class,
        'guest'  => \Numesia\NUAuth\Middleware\RedirectIfAuthenticated::class,
        ...
    ];

And then you can use it in your `app/Http/routes.php` file

    Route::group(['middleware' => 'nuauth'], function(){
        Route::get('/', function () {
            return "Hello I'm authenticated";
        });
    });

#### Advance

You can also tell your middleware to filter by departments, roles and also scopes by using the syntax:

```
['middleware' => 'nuauth:departments:roles:scopes']
```

**Example:**

- Get access only if the user belongs to NUMESIA department

```
    nuauth:NUMESIA:*:*
```

- Get access only if roles are ADMIN or OPERATOR

```
    nuauth:*:ADMIN|OPERATOR:*
```

- Get access only if roles are MANAGER or higher

```
    nuauth:*:MANAGER+:*
```

- Get access only if roles are MANAGER or lower

```
    nuauth:*:MANAGER-:*
```

- Get access only if scopes are CREATE and UPDATE

```
    nuauth:*:*:CREATE&UPDATE
```


### Alias

NUAuth comes with an `NUAuth` alias which contain some useful methods :

    <?php

    // Get auth user Payload instance
    \NUAuth::auth()

    // Get user claims
    $userClaims = \NUAuth::auth()->get('user');

    // Get user Scopes
    $userClaims['scopes'];

    // Get user departments
    $userClaims['departments'];

    // Get user Roles
    $userClaims['roles'];

    // Get user Id
    \NUAuth::auth()->get('sub');

    // Get user model
    \NUAuth::user();

    // Or default laravel auth user
    \Auth::user();

    // Logout auth user
    \NUAuth::logout();