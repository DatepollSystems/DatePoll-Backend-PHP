<?php /** @noinspection PhpUndefinedVariableInspection */

use App\Http\Middleware\JwtMiddleware;
use App\Http\Middleware\SettingsPermissionMiddleware;

$router->group(['prefix' => 'settings'], function () use ($router) {
  // Secure this routes so not everyone can get the keys an the tokens
  $router->group(['prefix' => '', 'middleware' => [JwtMiddleware::class]], function () use ($router) {
    $router->get('openweathermap/key', ['uses' => 'SettingsController@getOpenWeatherMapKey']);
    $router->get('openweathermap/cinemaCityId', ['uses' => 'SettingsController@getOpenWeatherMapCinemaCityId']);
  });
});

$router->group(['prefix' => 'settings/administration', 'middleware' => [JwtMiddleware::class, SettingsPermissionMiddleware::class]], function () use ($router) {
  $router->post('cinema', ['uses' => 'SettingsController@setCinemaFeatureIsEnabled']);
  $router->post('events', ['uses' => 'SettingsController@setEventsFeatureIsEnabled']);
  $router->post('name', ['uses' => 'SettingsController@setCommunityName']);
  $router->post('communityUrl', ['uses' => 'SettingsController@setCommunityUrl']);
  $router->post('description', ['uses' => 'SettingsController@setCommunityDescription']);
  $router->post('imprint', ['uses' => 'SettingsController@setCommunityImprint']);
  $router->post('privacyPolicy', ['uses' => 'SettingsController@setCommunityPrivacyPolicy']);
  $router->post('openweathermap/key', ['uses' => 'SettingsController@setOpenWeatherMapKey']);
  $router->post('openweathermap/cinemaCityId', ['uses' => 'SettingsController@setOpenWeatherMapCinemaCityId']);
  $router->post('url', ['uses' => 'SettingsController@setUrl']);
});