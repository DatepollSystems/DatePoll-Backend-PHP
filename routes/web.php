<?php /** @noinspection PhpUndefinedVariableInspection */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
*/

use App\Versions;

$router->get('/', function () use ($router) {
  return 'Running DatePoll-Backend! ( ͡° ͜ʖ ͡°)';
});

$router->group(['prefix' => 'api'], function () use ($router) {

  $router->get('/', ['uses' => 'DatePollServerController@getServerInfo']);

  /** Auth routes */
  require_once(__DIR__ . '/AuthRoutes.php');

  $router->group(['prefix' => 'v1', 'middleware' => 'jwt.auth'], function () use ($router) {

    /** System */
    require_once (__DIR__ . '/SystemRoutes.php');

    /** User routes */
    require_once(__DIR__ . '/UserRoutes.php');

    /** Management routes */
    require_once(__DIR__ . '/ManagementRoutes.php');

    /** Cinema routes */
    require_once(__DIR__ . '/CinemaRoutes.php');

    /** Events */
    require_once (__DIR__ . '/EventRoutes.php');

    /** Broadcasts */
    require_once (__DIR__ . '/BroadcastRoutes.php');

  });

  /** Calendar route */
  $router->get('user/calendar/{token}', ['uses' => 'CalendarController@getCalendarOf']);
  // $router->get('calendar/complete', ['uses' => 'CalendarController@getCompleteCalendar']);
});
