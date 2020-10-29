<?php

use App\Providers\AppServiceProvider;
use Rap2hpoutre\LaravelLogViewer\LaravelLogViewerServiceProvider;

require_once __DIR__ . '/../vendor/autoload.php';

try {
  (new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
  ))->bootstrap();
} catch (Dotenv\Exception\InvalidPathException $e) {
}

$app = new Laravel\Lumen\Application(
  dirname(__DIR__)
);

$app->withFacades();
$app->withEloquent();

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
*/
$app->singleton(Illuminate\Contracts\Debug\ExceptionHandler::class, App\Exceptions\Handler::class);

$app->singleton(Illuminate\Contracts\Console\Kernel::class, App\Console\Kernel::class);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
*/
/**
 * Register DatePoll Service providers to implement the Repository Pattern and change default database
 * string limit to 191 chars
 */
$app->register(AppServiceProvider::class);

/** Cors fix */
$app->register(Fruitcake\Cors\CorsServiceProvider::class);
$app->configure('cors');
$app->middleware([Fruitcake\Cors\HandleCors::class]);

/** IDE Helper */
$app->register(Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);

/** Redis and Horizon */
$app->register(Illuminate\Redis\RedisServiceProvider::class);

/** Log reader */
$app->register(LaravelLogViewerServiceProvider::class);

/** Mail configuration */
$app->register(Illuminate\Mail\MailServiceProvider::class);
$app->configure('mail');
$app->alias('mailer', Illuminate\Mail\Mailer::class);
$app->alias('mailer', Illuminate\Contracts\Mail\Mailer::class);
$app->alias('mailer', Illuminate\Contracts\Mail\MailQueue::class);

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

/** Register JWT-Auth middleware */
$app->routeMiddleware(['jwt.auth' => App\Http\Middleware\JwtMiddleware::class]);

$app->router->group(['namespace' => 'App\Http\Controllers'], function ($router) {
  require __DIR__ . '/../routes/web.php';
});

return $app;
