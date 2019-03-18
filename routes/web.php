<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return 'Running DatePoll-Backend! ( ͡° ͜ʖ ͡°) ';
});

$router->group(['prefix' => 'api', 'middleware' => 'cors'], function () use ($router) {

  $router->get('/', function () use ($router) {
    return response()->json(['version' => '0.0.1', 'version_number' => 1], 200);
  });

  /** Setting routes */
  require_once (__DIR__ . '/SettingsRoutes.php');

  /** Auth routes */
  require_once (__DIR__ . '/AuthRoutes.php');

  $router->group(['prefix' => 'v1', 'middleware' => 'jwt.auth'], function () use ($router) {

    /** User routes */
    require_once (__DIR__ . '/UserRoutes.php');

    /** Management routes */
    require_once (__DIR__ . '/ManagementRoutes.php');

    /** Cinema routes */
    require_once (__DIR__ . '/CinemaRoutes.php');

  });
});
