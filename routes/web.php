<?php /** @noinspection PhpUndefinedVariableInspection */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
*/

use App\Http\Middleware\System\LogsPermissionMiddleware;

$router->get('/', function () use ($router) {
  return 'Running DatePoll-Backend! ( ͡° ͜ʖ ͡°)';
});

/** Calendar route */
$router->get('calendar.ics', ['uses' => 'CalendarController@getCompleteCalendar']);
$router->get('calendar/{token}.ics', ['uses' => 'CalendarController@getCalendarOf']);

/** Log viewer */
$router->group(['namespace' => '\Rap2hpoutre\LaravelLogViewer', 'middleware' => ['jwt.auth', LogsPermissionMiddleware::class]], function() use ($router) {
  $router->get('logs', 'LogViewerController@index');
});

$router->group(['prefix' => 'api'], function () use ($router) {

  /** Server info route */
  $router->get('/', ['uses' => 'DatePollServerController@getServerInfo']);

  /** Auth routes */
  require(__DIR__ . '/AuthRoutes.php');

  $router->group([
    'prefix' => 'v1',
    'middleware' => 'jwt.auth'], function () use ($router) {

    /** System */
    require(__DIR__ . '/SystemRoutes.php');

    /** User routes */
    require(__DIR__ . '/UserRoutes.php');

    /** Management routes */
    require(__DIR__ . '/ManagementRoutes.php');

    /** Cinema routes */
    require(__DIR__ . '/CinemaRoutes.php');

    /** Events */
    require(__DIR__ . '/EventRoutes.php');

    /** Broadcasts */
    require(__DIR__ . '/BroadcastRoutes.php');
  });

  /** Calendar route (deprecated)
   * To remove date: 27.7.2025
   */
  $router->get('user/calendar/{token}', ['uses' => 'CalendarController@getCalendarOf']);
});
