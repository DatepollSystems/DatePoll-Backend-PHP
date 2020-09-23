<?php /** @noinspection PhpUndefinedVariableInspection */

use App\Http\Middleware\SettingsPermissionMiddleware;
use App\Http\Middleware\System\JobsPermissionMiddleware;
use App\Http\Middleware\System\LogsPermissionMiddleware;

$router->group(['prefix' => 'settings'], function () use ($router) {
  $router->get('openweathermap/key', ['uses' => 'SystemControllers\SettingsController@getOpenWeatherMapKey']);
  $router->get('openweathermap/cinemaCityId', ['uses' => 'SystemControllers\SettingsController@getOpenWeatherMapCinemaCityId']);
  $router->get('happyAlert', ['uses' => 'SystemControllers\SettingsController@getHappyAlert']);
});

$router->group(['prefix' => 'system'], function () use ($router) {

  $router->group([
    'prefix' => 'settings',
    'middleware' => [SettingsPermissionMiddleware::class]], function () use ($router) {
    $router->post('cinema', ['uses' => 'SystemControllers\SettingsController@setCinemaFeatureIsEnabled']);
    $router->post('events', ['uses' => 'SystemControllers\SettingsController@setEventsFeatureIsEnabled']);
    $router->post('broadcast', ['uses' => 'SystemControllers\SettingsController@setBroadcastFeatureIsEnabled']);
    $router->post('name', ['uses' => 'SystemControllers\SettingsController@setCommunityName']);
    $router->post('communityUrl', ['uses' => 'SystemControllers\SettingsController@setCommunityUrl']);
    $router->post('description', ['uses' => 'SystemControllers\SettingsController@setCommunityDescription']);
    $router->post('imprint', ['uses' => 'SystemControllers\SettingsController@setCommunityImprint']);
    $router->post('privacyPolicy', ['uses' => 'SystemControllers\SettingsController@setCommunityPrivacyPolicy']);
    $router->post('openweathermap/key', ['uses' => 'SystemControllers\SettingsController@setOpenWeatherMapKey']);
    $router->post('openweathermap/cinemaCityId', ['uses' => 'SystemControllers\SettingsController@setOpenWeatherMapCinemaCityId']);
    $router->post('url', ['uses' => 'SystemControllers\SettingsController@setUrl']);
    $router->post('happyAlert', ['uses' => 'SystemControllers\SettingsController@setHappyAlert']);
  });

  /** Log routes */
  $router->group([
    'prefix' => 'logs',
    'middleware' => [LogsPermissionMiddleware::class]], function () use ($router) {

    $router->get('', ['uses' => 'SystemControllers\LoggingController@getAllLogs']);
    $router->delete('all', ['uses' => 'SystemControllers\LoggingController@deleteAllLogs']);
    $router->delete('{id}', ['uses' => 'SystemControllers\LoggingController@deleteLog']);
  });
  $router->post('logs', ['uses' => 'SystemControllers\LoggingController@saveLog']);

  /** Job routes */
  $router->group([
    'prefix' => 'jobs',
    'middleware' => [JobsPermissionMiddleware::class]], function () use ($router) {

    $router->get('', ['uses' => 'SystemControllers\JobController@getUndoneJobs']);
  });
});
