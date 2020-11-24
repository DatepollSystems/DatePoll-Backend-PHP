<?php

use App\Console\Kernel as AppKernel;
use App\Exceptions\Handler;
use App\Http\Middleware\JwtMiddleware;
use App\Providers\AppServiceProvider;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Dotenv\Exception\InvalidPathException;
use Fruitcake\Cors\CorsServiceProvider;
use Fruitcake\Cors\HandleCors;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Mail\Mailer as ConMailer;
use Illuminate\Contracts\Mail\MailQueue;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\MailServiceProvider;
use Illuminate\Redis\RedisServiceProvider;
use Laravel\Lumen\Application;
use Laravel\Lumen\Bootstrap\LoadEnvironmentVariables;
use Rap2hpoutre\LaravelLogViewer\LaravelLogViewerServiceProvider;

require_once __DIR__ . '/../vendor/autoload.php';

try {
  (new LoadEnvironmentVariables(
    dirname(__DIR__)
  ))->bootstrap();
} catch (InvalidPathException $e) {
}

$app = new Application(
  dirname(__DIR__)
);

$app->withFacades();
$app->withEloquent();

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
*/
$app->singleton(ExceptionHandler::class, Handler::class);

$app->singleton(Kernel::class, AppKernel::class);

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
$app->register(CorsServiceProvider::class);
$app->configure('cors');
$app->middleware([HandleCors::class]);

/** IDE Helper */
$app->register(IdeHelperServiceProvider::class);

/** Redis and Horizon */
$app->register(RedisServiceProvider::class);

/** Log reader */
$app->register(LaravelLogViewerServiceProvider::class);

/** Mail configuration */
$app->register(MailServiceProvider::class);
$app->configure('mail');
$app->alias('mailer', Mailer::class);
$app->alias('mailer', ConMailer::class);
$app->alias('mailer', MailQueue::class);

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
$app->routeMiddleware(['jwt.auth' => JwtMiddleware::class]);

$app->router->group(['namespace' => 'App\Http\Controllers'], function ($router) {
  require __DIR__ . '/../routes/web.php';
});

return $app;
