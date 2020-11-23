<?php /** @noinspection PhpUndefinedVariableInspection */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
*/

use App\Http\Middleware\Broadcasts\BroadcastsFeatureMiddleware;
use App\Http\Middleware\System\LogsPermissionMiddleware;

$router->get('/', function () use ($router) {
  return 'Running DatePoll-Backend! ( ͡° ͜ʖ ͡°)';
});

// Calendar routes
$router->get('calendar', ['uses' => 'CalendarController@getCompleteCalendar']);
$router->get('calendar/{token}', ['uses' => 'CalendarController@getCalendarOf']);

// Broadcast attachment download route
$router->get(
  'attachment/{token}',
  ['uses' => 'BroadcastControllers\BroadcastController@attachmentDownload',
    'middleware' => [BroadcastsFeatureMiddleware::class], ]
);

// Log viewer
$router->group(
  ['namespace' => '\Rap2hpoutre\LaravelLogViewer',
    'middleware' => ['jwt.auth', LogsPermissionMiddleware::class], ],
  function () use ($router) {
    $router->get('logs', 'LogViewerController@index');
  }
);

$router->group(['prefix' => 'api'], function () use ($router) {

  // Server info route
  $router->get('/', ['uses' => 'SystemControllers\DatePollServerController@getServerInfo']);

  require(__DIR__ . '/AuthRoutes.php');

  $router->group(['prefix' => 'v1', 'middleware' => 'jwt.auth'], function () use ($router) {
    require(__DIR__ . '/SystemRoutes.php');
    require(__DIR__ . '/UserRoutes.php');
    require(__DIR__ . '/ManagementRoutes.php');
    require(__DIR__ . '/CinemaRoutes.php');
    require(__DIR__ . '/EventRoutes.php');
    require(__DIR__ . '/BroadcastRoutes.php');
    require(__DIR__ . '/SeatReservationRoutes.php');
  });

  /**
   * Calendar route (deprecated)
   * To remove date: 27.7.2025
   */
  $router->get('user/calendar/{token}', ['uses' => 'CalendarController@getCalendarOf']);
});
