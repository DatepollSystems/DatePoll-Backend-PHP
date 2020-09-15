<?php /** @noinspection PhpUndefinedVariableInspection */

use App\Http\Middleware\SettingsPermissionMiddleware;
use App\Http\Middleware\System\JobsPermissionMiddleware;
use App\Http\Middleware\System\LogsPermissionMiddleware;

$router->group(['prefix' => 'settings'], function () use ($router) {
  $router->get('openweathermap/key', ['uses' => 'SettingsController@getOpenWeatherMapKey']);
  $router->get('openweathermap/cinemaCityId', ['uses' => 'SettingsController@getOpenWeatherMapCinemaCityId']);
  $router->get('happyAlert', ['uses' => 'SettingsController@getHappyAlert']);
});

$router->group(['prefix' => 'system'], function () use ($router) {

  $router->group([
    'prefix' => 'settings',
    'middleware' => [SettingsPermissionMiddleware::class]], function () use ($router) {
    $router->post('cinema', ['uses' => 'SettingsController@setCinemaFeatureIsEnabled']);
    $router->post('events', ['uses' => 'SettingsController@setEventsFeatureIsEnabled']);
    $router->post('broadcast', ['uses' => 'SettingsController@setBroadcastFeatureIsEnabled']);
    $router->post('name', ['uses' => 'SettingsController@setCommunityName']);
    $router->post('communityUrl', ['uses' => 'SettingsController@setCommunityUrl']);
    $router->post('description', ['uses' => 'SettingsController@setCommunityDescription']);
    $router->post('imprint', ['uses' => 'SettingsController@setCommunityImprint']);
    $router->post('privacyPolicy', ['uses' => 'SettingsController@setCommunityPrivacyPolicy']);
    $router->post('openweathermap/key', ['uses' => 'SettingsController@setOpenWeatherMapKey']);
    $router->post('openweathermap/cinemaCityId', ['uses' => 'SettingsController@setOpenWeatherMapCinemaCityId']);
    $router->post('url', ['uses' => 'SettingsController@setUrl']);
    $router->post('happyAlert', ['uses' => 'SettingsController@setHappyAlert']);
  });

  /** Log routes */
  $router->group([
    'prefix' => 'logs',
    'middleware' => [LogsPermissionMiddleware::class]], function () use ($router) {

    $router->get('', ['uses' => 'SystemControllers\LoggingController@getAllLogs']);
    $router->delete('all', ['uses' => 'SystemControllers\LoggingController@deleteAllLogs']);
    $router->delete('{id}', ['uses' => 'SystemControllers\LoggingController@deleteLog']);
  });

  $router->group([
    'prefix' => 'jobs',
    'middleware' => [JobsPermissionMiddleware::class]], function () use ($router) {

    $router->get('', ['uses' => 'SystemControllers\JobController@getUndoneJobs']);
  });
});
