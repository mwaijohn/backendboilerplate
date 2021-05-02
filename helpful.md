# 1. Installing passport

```composer require laravel/passport```

```php artisan migrate```

```php artisan passport:install```

After running the passport:install command, add the Laravel\Passport\HasApiTokens trait to your App\Models\User model. This trait will provide a few helper methods to your model which allow you to inspect the authenticated user's token and scopes:


```<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
}
```

Next, you should call the Passport::routes method within the boot method of your App\Providers\AuthServiceProvider. This method will register the routes necessary to issue access tokens and revoke access tokens, clients, and personal access tokens:


```<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        if (! $this->app->routesAreCached()) {
            Passport::routes();
        }
    }
}
```

Finally, in your application's config/auth.php configuration file, you should set the driver option of the api authentication guard to passport. This will instruct your application to use Passport's TokenGuard when authenticating incoming API requests:


```'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],

    'api' => [
        'driver' => 'passport',
        'provider' => 'users',
    ],
],
```

##  convert all responses to JSON automatically.

```php artisan make:middleware ForceJsonResponse```

```public function handle($request, Closure $next)
{
    $request->headers->set('Accept', 'application/json');
    return $next($request);
}
```


add the middleware to our app/Http/Kernel.php file in the $routeMiddleware array:

```'json.response' => \App\Http\Middleware\ForceJsonResponse::class,```

 also add it to the $middleware array in the same file:

 ```\App\Http\Middleware\ForceJsonResponse::class,```



 #### To allow the consumers of our Laravel REST API to access it from a different origin, we have to set up CORS. To do that, we’ll create a piece of middleware called Cors.

 ```php artisan make:middleware Cors```

 Then, in app/Http/Middleware/Cors.php, add the following code:



```public function handle($request, Closure $next)
{
    return $next($request)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, X-Token-Auth, Authorization');
}
````

```'cors' => \App\Http\Middleware\Cors::class,```

we’ll have to add it to the $middleware array as we did for the previous middleware:

```\App\Http\Middleware\Cors::class,```


append this route group to routes/api.php:

```
Route::group(['middleware' => ['cors', 'json.response']], function () {
   
});
```








