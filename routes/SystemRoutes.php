<?php /** @noinspection PhpUndefinedVariableInspection */

use App\Http\Middleware\System\LogsPermissionMiddleware;

$router->group(['prefix' => 'system'], function () use ($router) {

  /** Log routes */
  $router->group([
    'prefix' => 'logs',
    'middleware' => [LogsPermissionMiddleware::class]], function () use ($router) {

    $router->get('', ['uses' => 'SystemControllers\LoggingController@getAllLogs']);
    $router->delete('all', ['uses' => 'SystemControllers\LoggingController@deleteAllLogs']);
    $router->delete('{id}', ['uses' => 'SystemControllers\LoggingController@deleteLog']);
  });
});
