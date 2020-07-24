<?php /** @noinspection PhpUndefinedVariableInspection */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
*/

$router->get('/', function () use ($router) {
  return 'Running DatePoll-Backend! ( ͡° ͜ʖ ͡°)';
});

/** Calendar route */
$router->get('calendar', ['uses' => 'CalendarController@getCompleteCalendar']);  /** Calendar route */
$router->get('calendar/{token}', ['uses' => 'CalendarController@getCalendarOf']);

$router->group(['prefix' => 'api'], function () use ($router) {

  /** Server info route */
  $router->get('/', ['uses' => 'DatePollServerController@getServerInfo']);

  /** Auth routes */
  require_once(__DIR__ . '/AuthRoutes.php');

  $router->group([
    'prefix' => 'v1',
    'middleware' => 'jwt.auth'], function () use ($router) {

    /** System */
    require_once(__DIR__ . '/SystemRoutes.php');

    /** User routes */
    require_once(__DIR__ . '/UserRoutes.php');

    /** Management routes */
    require_once(__DIR__ . '/ManagementRoutes.php');

    /** Cinema routes */
    require_once(__DIR__ . '/CinemaRoutes.php');

    /** Events */
    require_once(__DIR__ . '/EventRoutes.php');

    /** Broadcasts */
    require_once(__DIR__ . '/BroadcastRoutes.php');
  });

  /** Calendar route (deprecated) */
  $router->get('user/calendar/{token}', ['uses' => 'CalendarController@getCalendarOf']);
});
